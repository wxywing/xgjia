#!/usr/bin/env python3
"""
统一增量爬虫 — 公棚 + 赛事 + 展厅 + 铭鸽
对比 DB 已有数据，只抓增量

用法:
  python3 crawl_incremental_all.py --type races      # 仅赛事
  python3 crawl_incremental_all.py --type lofts      # 仅公棚（低频）
  python3 crawl_incremental_all.py --type shops      # 仅展厅
  python3 crawl_incremental_all.py --type pigeons    # 仅铭鸽
  python3 crawl_incremental_all.py --type all        # 全部
  python3 crawl_incremental_all.py --type all --dry-run

特点:
  - 所有类型均 INSERT IGNORE 防重复
  - 断点续跑（每类型独立 checkpoint）
  - --dry-run 只报告不写入
  - 铭鸽抓取支持 --max-shops 限制每次处理展厅数
"""
import json, re, time, os, sys, ssl, urllib.request, subprocess, argparse
from datetime import datetime
from collections import OrderedDict

ssl._create_default_https_context = ssl._create_unverified_context

# ============================================================
# 配置
# ============================================================
SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
MYSQL = "/Applications/phpstudy/Extensions/MySQL5.7.28/bin/mysql"
MYSQL_DB = "xgjia"
MYSQL_USER = "root"
MYSQL_PASS = "123456"

TIMEOUT = 20
DELAY_LIST = 0.5    # 列表页间隔
DELAY_DETAIL = 0.3  # 详情页间隔
DELAY_SHOP = 1.0    # 展厅主页间隔

HEADERS = {
    "User-Agent": "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36",
    "Accept-Language": "zh-CN,zh;q=0.9",
}

# ============================================================
# MySQL 操作（通过 CLI）
# ============================================================

def mysql_query(sql):
    cmd = [MYSQL, "-u", MYSQL_USER, f"-p{MYSQL_PASS}", MYSQL_DB, "-N", "-e", sql]
    try:
        out = subprocess.check_output(cmd, stderr=subprocess.DEVNULL, timeout=30)
        lines = out.decode("utf-8").strip().split("\n")
        return [line.split("\t") for line in lines if line.strip()]
    except subprocess.CalledProcessError as e:
        print(f"  MySQL QUERY ERROR: {e.output.decode() if e.output else e}", flush=True)
        return []

def mysql_exec(sql):
    cmd = [MYSQL, "-u", MYSQL_USER, f"-p{MYSQL_PASS}", MYSQL_DB, "-e", sql]
    try:
        subprocess.check_output(cmd, stderr=subprocess.DEVNULL, timeout=30)
        return True
    except subprocess.CalledProcessError as e:
        print(f"  MySQL EXEC ERROR: {e.output.decode() if e.output else e}", flush=True)
        return False

def mysql_escape(s):
    if s is None: return "NULL"
    return "'" + str(s).replace("\\", "\\\\").replace("'", "\\'") + "'"

# ============================================================
# HTTP 请求
# ============================================================

def fetch(url, timeout=TIMEOUT, min_bytes=50):
    req = urllib.request.Request(url, headers=HEADERS)
    try:
        data = urllib.request.urlopen(req, timeout=timeout).read()
        if len(data) < min_bytes: return None
        return data.decode("gbk", errors="replace")
    except Exception as e:
        print(f"    FETCH ERROR: {e}", flush=True)
        return None

# ============================================================
# HTML 工具
# ============================================================

def clean(text):
    return re.sub(r'\s+', ' ', re.sub(r'&nbsp;', ' ', re.sub(r'<[^>]+>', ' ', text))).strip()

# ============================================================
# 1. 公棚 (Lofts) 增量
# 数据源: https://gdgp.chinaxinge.com/gplist.asp
# 对比字段: gp_id → lofts.gp_id
# 新公棚: INSERT + 抓详情 default2.asp?gp_id=X
# ============================================================

LOFT_BASE = "https://gdgp.chinaxinge.com"
LOFT_LIST_URL = f"{LOFT_BASE}/gplist.asp"

def extract_loft_ids_from_list(html):
    """从公棚列表页提取 gp_id + 名称 + 省份 + 电话"""
    ids = []
    seen = set()
    # 按行解析：每行第一个 td 是省份，第二个 td 含名称链接
    rows = re.findall(r'<tr[^>]*>(.*?)</tr>', html, re.DOTALL)
    for row in rows:
        m = re.search(r'<a[^>]+href="[^"]*gp_id=(\d+)[^"]*"[^>]*>(.*?)</a>', row, re.DOTALL)
        if not m:
            continue
        gp_id = m.group(1)
        name = re.sub(r'<[^>]+>', '', m.group(2)).strip()
        if gp_id in seen or not name or len(name) <= 1:
            continue
        seen.add(gp_id)
        # 提取行内所有 td
        cells = re.findall(r'<td[^>]*>(.*?)</td>', row, re.DOTALL)
        clean = [re.sub(r'<[^>]+>', '', c).strip() for c in cells]
        province = re.sub(r'\s+', '', clean[0]) if clean else ''
        # 从行内提取电话（列表页可能有）
        phone = ''
        for c in clean:
            p = re.findall(r'[\d\-]{7,15}', c)
            if p:
                phone = p[0]
                break
        ids.append({"gp_id": gp_id, "name": name, "province": province, "phone": phone})
    return ids

def parse_loft_pagination(html):
    pages = re.findall(r'page=(\d+)', html)
    if pages: return max(int(p) for p in pages)
    m = re.search(r'共\s*(\d+)\s*页', html)
    if m: return int(m.group(1))
    return 1

def try_match_re(patterns, text, flags=0):
    """多模式匹配，返回第一个捕获组"""
    for p in patterns:
        m = re.search(p, text, flags)
        if m:
            try:
                return m.group(1).strip()
            except IndexError:
                return m.group(0).strip()
    return ""

def decode_html_entities(text):
    """解码 HTML 实体：&#8203;（零宽空格）、&#160;/&nbsp;（不间断空格）等"""
    text = text.replace('&nbsp;', ' ').replace('&#160;', ' ')
    text = text.replace('&#8203;', '').replace('&#8204;', '').replace('&#8205;', '')
    text = text.replace('&amp;', '&').replace('&lt;', '<').replace('&gt;', '>')
    text = text.replace('&quot;', '"').replace('&apos;', "'")
    # 通用数字实体
    text = re.sub(r'&#(\d+);', lambda m: chr(int(m.group(1))) if 0 < int(m.group(1)) < 0x110000 else '', text)
    text = re.sub(r'&#x([0-9a-fA-F]+);', lambda m: chr(int(m.group(1), 16)), text)
    return re.sub(r'\s+', ' ', text).strip()

def _desc_mentions_other_loft(desc, my_name):
    """检测 description 是否描述了另一个公棚（而非 my_name）"""
    if not desc or len(desc) < 10:
        return False
    my_stem = re.sub(r'[（(].*?[）)]', '', my_name).strip()
    my_prefix = my_stem[:3] if len(my_stem) >= 3 else my_stem
    # 提取描述中所有的公棚名
    name_re = re.compile(
        r'([\u4e00-\u9fa5]{2,8}'
        r'(?:公棚|赛鸽中心|赛鸽公棚|赛鸽俱乐部|信鸽中心|赛鸽训养棚|'
        r'寄养棚|竞技俱乐部|赛鸽秋棚|赛鸽春棚))'
    )
    other_names = [m.group(1) for m in name_re.finditer(desc)]
    if not other_names:
        return False  # 没有提到任何公棚名，可能是通用描述
    # 如果有 name_re 匹配且 my_prefix 不在 desc 中，且 other_names 都不是 my_name 的子串→ 描述的是别人
    mentions_self = my_prefix in desc if my_prefix else False
    for oname in other_names:
        if oname in my_name or my_stem in oname:
            mentions_self = True
            break
    if other_names and not mentions_self:
        return True
    return False

NAV_KEYWORDS = ['鸽舍族谱', '展厅新闻', '历年赛绩', '信鸽拍卖', '在线购买',
                '推荐配对', '鸽圈动态', '展厅相册', '鸽友留言', '联系我们']

def _strip_chinaxinge_nav(desc):
    """从描述中删除中信网展厅导航栏前缀
    模式: 鸽舍族谱 ... 联系我们[whitespace]
    返回清理后的文本"""
    if not desc or len(desc) < 10:
        return desc
    # 检测是否以导航关键词开头
    has_nav_start = any(desc.strip().startswith(kw) for kw in NAV_KEYWORDS[:1])
    if not has_nav_start:
        return desc
    # 检测是否包含足够的导航关键词（至少4个）
    hit_count = sum(1 for kw in NAV_KEYWORDS if kw in desc[:300])
    if hit_count < 4:
        return desc
    # 正则删除导航前缀：从开头到"联系我们"
    cleaned = re.sub(r'^鸽舍族谱\s*.*?联系我们[\s\r\n]*', '', desc, flags=re.DOTALL).strip()
    if len(cleaned) < 10:
        return ''
    return cleaned

def extract_loft_from_main(html):
    """从 default.asp 主页提取：电话、地址、联系人"""
    text = re.sub(r'<script[^>]*>.*?</script>', '', html, flags=re.DOTALL)
    text = re.sub(r'<style[^>]*>.*?</style>', '', text, flags=re.DOTALL)

    result = {}
    result['contact_phone'] = try_match_re([
        r'电话[：:]\s*([0-9\-]{7,20})',
        r'Tel[：:]\s*([0-9\-]{7,20})',
        r'联系电话[：:]\s*([0-9\-]{7,20})',
        r'手机[：:]\s*([0-9\-]{11})',
    ], text)

    result['address'] = try_match_re([
        r'地址[：:]\s*([\u4e00-\u9fa5][^\n<]{5,80})',
        r'地\s*址[：:]\s*([\u4e00-\u9fa5][^\n<]{5,80})',
    ], text)

    result['contact_name'] = try_match_re([
        r'负责人[：:]\s*([^\s<,，]{2,10})',
        r'联系人[：:]\s*([^\s<,，]{2,10})',
        r'联 系 人[：:]\s*([^\s<,，]{2,10})',
    ], text)

    # 网址
    m = re.search(r'互联网网址[：:\s]*<a[^>]+href=["\']?(https?://[^"\'\s>]+)', html)
    if m: result['website'] = m.group(1)

    # 省份从地址或 title 提取
    prov_m = re.search(r'([\u4e00-\u9fa5]{2,3}省|北京|上海|天津|重庆|内蒙古|广西|西藏|宁夏|新疆|香港|澳门)',
                       result.get('address', '') + text)
    if prov_m: result['province'] = prov_m.group(1)

    # 比赛类型
    if '春棚' in text: result['race_type'] = '春棚'
    elif '秋棚' in text: result['race_type'] = '秋棚'

    return result

def extract_loft_from_about(html):
    """从 about.asp 提取：容量、届数、比赛类型、面积、简介"""
    result = {}

    # 横向表格解析：表头行 + 数据行（同一张表含多列）
    # 先找包含关键字段的表
    for anchor in ['举办比赛性质', '可容量羽数', '容量', '举办届数']:
        pos = html.find(anchor)
        if pos < 0: continue
        table_start = html.rfind('<table', 0, pos)
        if table_start < 0: continue
        table_end = html.find('</table>', pos)
        if table_end < 0: continue
        table_html = html[table_start:table_end + 8]

        # 提取所有行
        rows = re.findall(r'<tr[^>]*>(.*?)</tr>', table_html, re.DOTALL)
        if len(rows) < 2: continue
        
        # 第一行 = 表头标签，第二行 = 数据值
        labels = [re.sub(r'<[^>]+>', '', td).strip()
                  for td in re.findall(r'<td[^>]*>(.*?)</td>', rows[0], re.DOTALL)]
        values = [re.sub(r'<[^>]+>', '', td).strip()
                  for td in re.findall(r'<td[^>]*>(.*?)</td>', rows[1], re.DOTALL)]

        field_map = {
            '举办比赛性质': 'race_type', '可容量羽数': 'capacity',
            '限收羽数': 'limit_count', '举办届数': 'edition',
            '公棚面积(平方米)': 'area', '公棚面积': 'area',
        }
        for j in range(min(len(labels), len(values))):
            label = labels[j]
            val = values[j].strip()
            if not val or val in ('查看', '查看监控', '查看地图', ''):
                continue
            if label in field_map:
                result[field_map[label]] = re.sub(r'\s+', '', val)[:30]
        break  # 找到一个表格就够了

    # 简介 fallback：about 页可能有更长的介绍文本
    # 只挑明确的“公棚简介”或“本公棚”开头的文本，不要泛匹配
    for pattern in [
        r'公棚简介[：:]*[\s]*([^<]{20,500})',
        r'本公棚[^\n<]{30,600}',
        r'\u516c棚概况[：:]*[\s]*([^<]{20,500})',
        r'\u4e2d心简介[：:]*[\s]*([^<]{20,500})',
    ]:
        m = re.search(pattern, html, re.DOTALL)
        if m:
            raw_desc = m.group(1) if m.lastindex else m.group(0)
            desc = re.sub(r'<[^>]+>', '', raw_desc).strip()
            desc = decode_html_entities(desc)
            if len(desc) > 20:
                result['description'] = desc[:2000]
                break

    # 如果没有明确標题，尝试在表格之外找最长的纯文本段（跳过 JS/HTML太多的）
    if 'description' not in result:
        blocks = re.split(r'<br[^>]*>|</?p[^>]*>|</?div[^>]*>', html)
        candidates = []
        for b in blocks:
            t = re.sub(r'<[^>]+>', '', b).strip()
            t = decode_html_entities(t)
            # 跳过导航、JS、CSS、城市旅游介绍
            skip_words = ['javascript', 'function', 'menu', '首页', '导航', '返回', '上一页',
                         '历史文化名城', '帝尧', '海岱明珠']
            if any(w in t for w in skip_words):
                continue
            if len(t) > 50 and ('公棚' in t or '赛鸽' in t):
                candidates.append(t)
        if candidates:
            result['description'] = max(candidates, key=len)[:2000]

    return result

def find_loft_template(gp_id):
    """确定公棚使用的模板，返回 (模板名, html, about_html)"""
    # 先试 default2.asp
    url = f'{LOFT_BASE}/default2.asp?gp_id={gp_id}'
    html = fetch(url, timeout=10)
    if html and ('公棚' in html or '赛鸽' in html):
        about_html = fetch(f'{LOFT_BASE}/style/mo1/about.asp?gp_id={gp_id}', timeout=10)
        return 'default2', html, about_html

    # 遍历 mo1-mo38
    for mo in range(1, 39):
        url = f'{LOFT_BASE}/style/mo{mo}/default.asp?gp_id={gp_id}'
        html = fetch(url, timeout=10)
        if html and ('公棚' in html or '赛鸽' in html):
            about_html = fetch(f'{LOFT_BASE}/style/mo{mo}/about.asp?gp_id={gp_id}', timeout=10)
            return f'mo{mo}', html, about_html
        time.sleep(0.1)

    return None, None, None

def crawl_lofts(dry_run=False):
    """增量抓取公棚 — 基于 v4 双页面抓取方案"""
    print("\n" + "=" * 60)
    print("📦 公棚增量 (Lofts) — 模板发现 + 主页 + about 三页抓取")

    # 加载已有 ID
    rows = mysql_query("SELECT gp_id FROM lofts WHERE gp_id IS NOT NULL")
    existing = {r[0] for r in rows if r and r[0]}
    print(f"   DB 现有: {len(existing)} 个公棚")

    # 抓列表（分页）
    html = fetch(LOFT_LIST_URL)
    if not html:
        print("   ❌ 列表页获取失败")
        return {"type": "lofts", "new": 0, "errors": ["list_fetch_fail"]}

    all_entries = extract_loft_ids_from_list(html)
    total_pages = parse_loft_pagination(html)
    print(f"   首页: {len(all_entries)} 个, 总页数: {total_pages}")

    for page in range(2, total_pages + 1):
        time.sleep(DELAY_LIST)
        html_pg = fetch(f"{LOFT_LIST_URL}?sy=&area=&cid=&kwd=&page={page}")
        if html_pg:
            entries = extract_loft_ids_from_list(html_pg)
            all_entries.extend(entries)

    # 去重
    seen = set()
    unique = []
    for e in all_entries:
        if e["gp_id"] not in seen:
            seen.add(e["gp_id"])
            unique.append(e)
    print(f"   去重后: {len(unique)} 个公棚")

    # 找新增
    new_entries = [e for e in unique if e["gp_id"] not in existing]
    print(f"   🆕 新增: {len(new_entries)} 个")

    if not new_entries:
        print("   ✅ 无新公棚")
        return {"type": "lofts", "new": 0, "errors": []}

    report = {"type": "lofts", "new": 0, "details": [], "errors": []}
    for i, entry in enumerate(new_entries):
        gp_id, name = entry["gp_id"], entry["name"]
        print(f"   [{i+1}/{len(new_entries)}] gp_id={gp_id} {name}")

        # 模板发现 + 双页抓取
        template, main_html, about_html = find_loft_template(gp_id)
        if not main_html:
            print(f"      ⚠️ 无详情页内容 → 仅入库名称")
            if not dry_run:
                sql = f"INSERT INTO lofts (user_id, gp_id, name, status) VALUES (0, {mysql_escape(gp_id)}, {mysql_escape(name[:100])}, 1)"
                if mysql_exec(sql): report["new"] += 1
                else: report["errors"].append(f"insert_fail: gp_id={gp_id}")
            else:
                report["new"] += 1
            continue

        # 主页提取
        main_data = extract_loft_from_main(main_html)
        # about 页提取
        about_data = extract_loft_from_about(about_html) if about_html else {}
        # 合并（about 补充 main 没有的字段；列表页补省份/电话 fallback）
        merged = {**main_data, **about_data}
        # 列表页省份 fallback（详情页可能没有省份）
        if not merged.get('province') and entry.get('province'):
            merged['province'] = entry['province']
        # 列表页电话 fallback
        if not merged.get('contact_phone') and entry.get('phone'):
            merged['contact_phone'] = entry['phone']

        # 验证描述：如果是其他公棚的描述，丢弃
        desc = merged.get('description', '')
        if desc and _desc_mentions_other_loft(desc, name):
            merged['description'] = ''
            print(f"      ⚠️ 丢弃脏描述（描述的是其他公棚）")

        if not dry_run:
            sql = (
                f"INSERT INTO lofts (user_id, gp_id, name, province, address, "
                f"contact_name, contact_phone, race_type, capacity, description, website, status) VALUES ("
                f"0, {mysql_escape(gp_id)}, {mysql_escape(name[:100])}, "
                f"{mysql_escape(merged.get('province', '')[:30])}, "
                f"{mysql_escape(merged.get('address', '')[:255])}, "
                f"{mysql_escape(merged.get('contact_name', '')[:50])}, "
                f"{mysql_escape(merged.get('contact_phone', '')[:20])}, "
                f"{mysql_escape(merged.get('race_type', '')[:50])}, "
                f"{merged.get('capacity', 0)}, "
                f"{mysql_escape(merged.get('description', '')[:2000])}, "
                f"{mysql_escape(merged.get('website', '')[:255])}, 1)"
            )
            if mysql_exec(sql):
                report["new"] += 1
                report["details"].append({
                    "gp_id": gp_id, "name": name,
                    "province": merged.get("province", ""),
                    "phone": merged.get("contact_phone", ""),
                    "template": template,
                })
                parts = []
                if merged.get('contact_phone'): parts.append(f"📞{merged['contact_phone']}")
                if merged.get('province'): parts.append(f"📍{merged['province']}")
                if merged.get('race_type'): parts.append(f"🏷{merged['race_type']}")
                if merged.get('capacity'): parts.append(f"📦{merged['capacity']}")
                print(f"      ✅ [{template}] {' '.join(parts)}")
            else:
                report["errors"].append(f"insert_fail: gp_id={gp_id}")
        else:
            report["new"] += 1

        time.sleep(DELAY_DETAIL)

    print(f"   ✅ 完成: 新增 {report['new']} 个, 错误 {len(report['errors'])}")
    return report

# ============================================================
# 2. 赛事 (Races) 增量
# 数据源: gdgp.chinaxinge.com/style/mo30/race_rclist.asp?gp_id=X
# 对比字段: source_id → races.source_id
# 新赛事: INSERT → 全量抓成绩明细
# ============================================================

RACE_LIST_URL = "https://gdgp.chinaxinge.com/style/mo30/race_rclist.asp"
RACE_RESULT_URL = "https://gdgp.chinaxinge.com/gdgp_rcshow.asp"

def parse_race_list(html):
    """从赛事列表页解析赛事"""
    races = []
    blocks = re.findall(
        r"onclick=\"window\.open\('/gdgp_rcshow\.asp\?id=(\d+)'\)\".*?"
        r"<div class=\"ss_title\">(.*?)</div>.*?"
        r"开笼时间[：:]\s*</span>(.*?)</div>.*?"
        r"<div class=\"ss_gcsz\">\s*(\d+)\s*</div>.*?"
        r"上笼羽数[：:]\s*</span>\s*(\*|\d+)",
        html, re.DOTALL
    )
    for source_id, name, release_time, returned, entry in blocks:
        races.append({
            "source_id": source_id,
            "name": clean(name),
            "release_time": clean(release_time),
            "returned_count": int(returned) if returned.isdigit() else 0,
            "entry_count": 0 if entry == "*" else (int(entry) if entry.isdigit() else 0),
        })
    return races

def classify_race(name):
    if "决赛" in name: return "final"
    if "预赛" in name or ("关" in name and "决赛" not in name): return "pre"
    if "训放" in name or "家飞" in name or "扫描" in name: return "train"
    if "收费站" in name or "收费" in name: return "toll"
    return "other"

def extract_race_season(name, release_time):
    t = release_time or name or ""
    m = re.search(r"(\d{4})", t)
    year = int(m.group(1)) if m else None
    m = re.search(r"(\d{4})-(\d{2})", t)
    season = "spring" if (m and 1 <= int(m.group(2)) <= 6) else ("autumn" if m else "other")
    return year, season

def parse_results_pagination(html):
    m = re.search(r'页码[：:]\s*\d+/(\d+).*?共\s*(\d+)\s*条', html)
    if m: return int(m.group(1)), int(m.group(2))
    return 1, 0

def parse_results_page(html):
    results = []
    rows = re.findall(r'<tr[^>]*>(.*?)</tr>', html, re.DOTALL | re.IGNORECASE)
    for row in rows:
        cells = re.findall(r'<td[^>]*>(.*?)</td>', row, re.DOTALL | re.IGNORECASE)
        c = [re.sub(r'<[^>]+>', '', cell).strip() for cell in cells]
        if len(c) == 7 and c[0].isdigit():
            try:
                speed_val = c[6].replace(',', '')
                results.append({
                    "rank": int(c[0]), "owner_name": c[1] or None,
                    "region": c[2] or None, "ring_number": c[3] or None,
                    "color": c[4] or None, "arrival_time": c[5] or None,
                    "speed": float(speed_val) if speed_val.replace('.', '').replace('-', '').isdigit() else 0.0,
                })
            except (ValueError, IndexError):
                pass
    return results

def is_2026_or_later(release_time):
    """检查 release_time 是否 >= 2026-01-01"""
    if not release_time:
        return False
    m = re.search(r'(\d{4})', str(release_time))
    if m:
        return int(m.group(1)) >= 2026
    return False

def crawl_races(dry_run=False):
    """增量抓取赛事（仅 2026-01-01 之后的数据）"""
    print("\n" + "=" * 60)
    print("🏁 赛事增量 (Races) — 仅 2026-01-01 之后")

    # 加载所有已有 source_id
    rows = mysql_query("SELECT source_id FROM races WHERE source_id IS NOT NULL")
    existing_ids = {r[0] for r in rows if r and r[0]}
    print(f"   DB 现有: {len(existing_ids)} 场赛事")

    # 加载所有公棚
    rows = mysql_query("SELECT id, name, gp_id FROM lofts WHERE status = 1 ORDER BY id")
    lofts = [{"db_id": int(r[0]), "name": r[1], "gp_id": r[2]} for r in rows if len(r) >= 3 and r[2]]
    print(f"   公棚: {len(lofts)} 个")

    report = {"type": "races", "new_races": 0, "new_results": 0, "errors": [], "details": []}

    for i, loft in enumerate(lofts):
        gp_id, name, db_id = loft["gp_id"], loft["name"], loft["db_id"]

        html = fetch(f"{RACE_LIST_URL}?gp_id={gp_id}")
        if not html:
            report["errors"].append(f"list_fail: gp_id={gp_id}")
            print(f"   [{i+1}/{len(lofts)}] {name} - ❌ 列表失败")
            time.sleep(DELAY_LIST)
            continue

        races_on_page = parse_race_list(html)
        new_races = [r for r in races_on_page if r["source_id"] not in existing_ids]
        total_new = len(new_races)
        new_races = [r for r in new_races if is_2026_or_later(r.get("release_time", ""))]
        skipped_pre2026 = total_new - len(new_races)

        if not new_races:
            if (i + 1) % 50 == 0:
                print(f"   [{i+1}/{len(lofts)}] {name} - ✅ {len(races_on_page)} 场 (无新)")
            time.sleep(DELAY_LIST)
            continue

        print(f"   [{i+1}/{len(lofts)}] {name} - 🆕 {len(new_races)} 场新赛事")

        for race in new_races:
            sid = race["source_id"]
            year, season = extract_race_season(race.get("name", ""), race.get("release_time", ""))
            race["season_year"] = year
            race["season_type"] = season

            # Insert race
            ir_sql = (
                f"INSERT INTO races (loft_id, source_id, name, release_time, "
                f"returned_count, entry_count, race_category, season_year, season_type) VALUES ("
                f"{db_id}, {mysql_escape(sid)}, {mysql_escape(race['name'][:200])}, "
                f"{mysql_escape(race['release_time'])}, {race['returned_count']}, "
                f"{race['entry_count']}, {mysql_escape(classify_race(race['name']))}, "
                f"{year or 'NULL'}, {mysql_escape(season)})"
            )
            if dry_run or not mysql_exec(ir_sql):
                if dry_run: report["new_races"] += 1
                else: report["errors"].append(f"insert_race_fail: src={sid}")
                continue

            # Get DB race_id
            rows = mysql_query(f"SELECT id FROM races WHERE source_id = '{sid}'")
            if not rows: continue
            db_race_id = int(rows[0][0])

            # Fetch results
            hr = fetch(f"{RACE_RESULT_URL}?id={sid}&page=1&o=0")
            if not hr: continue
            total_pages, total_records = parse_results_pagination(hr)
            if total_records == 0: continue

            all_results = parse_results_page(hr)
            for pg in range(2, total_pages + 1):
                time.sleep(DELAY_DETAIL)
                hpg = fetch(f"{RACE_RESULT_URL}?id={sid}&page={pg}&o=0")
                if hpg: all_results.extend(parse_results_page(hpg))

            # Batch INSERT IGNORE
            batch = []
            for row in all_results:
                batch.append(
                    f"({db_race_id}, {row['rank']}, {mysql_escape(row['owner_name'])}, "
                    f"{mysql_escape(row['region'])}, {mysql_escape(row['ring_number'])}, "
                    f"{mysql_escape(row['color'])}, {mysql_escape(row['arrival_time'])}, "
                    f"{row['speed']})"
                )
                if len(batch) >= 500:
                    sql = "INSERT IGNORE INTO race_results (race_id, `rank`, owner_name, region, ring_number, color, arrival_time, speed) VALUES " + ",\n".join(batch)
                    if not mysql_exec(sql): report["errors"].append(f"insert_results_fail: race_id={db_race_id}")
                    batch = []
            if batch:
                sql = "INSERT IGNORE INTO race_results (race_id, `rank`, owner_name, region, ring_number, color, arrival_time, speed) VALUES " + ",\n".join(batch)
                if not mysql_exec(sql): report["errors"].append(f"insert_results_fail: race_id={db_race_id}")

            mysql_exec(f"UPDATE races SET result_count = {len(all_results)} WHERE id = {db_race_id}")
            existing_ids.add(sid)
            report["new_races"] += 1
            report["new_results"] += len(all_results)
            report["details"].append({"source_id": sid, "name": race["name"], "results": len(all_results), "loft": name})
            time.sleep(DELAY_LIST)

        time.sleep(DELAY_LIST)

    print(f"   ✅ 赛事完成: 新增 {report['new_races']} 场, 成绩 {report['new_results']} 条")
    return report

# ============================================================
# 3. 展厅 (Shops) 增量
# 数据源: https://www.chinaxinge.com/xinge/product/netshop.asp?page=N
# 对比字段: shop_id → shops.source_id
# 新展厅: INSERT + 抓详情（联系人/省份/展品ID列表）
# ============================================================

SHOP_LIST_URL = "https://www.chinaxinge.com/xinge/product/netshop.asp"
SHOP_DETAIL_URL = "https://www.chinaxinge.com/xinge/shop/default.asp"

def extract_shop_ids_from_list(html):
    """从 netshop.asp 提取 shop_id + 名称 + 头像"""
    shops = []
    seen = set()
    pattern = re.compile(
        r"<a\s+href=['\"][^'\"]*shop_id=(\d+)[^'\"]*['\"][^>]*>\s*"
        r"<span\s+style=['\"][^'\"]*font-size:18px[^'\"]*['\"][^>]*>"
        r"([\u4e00-\u9fa5A-Za-z0-9·\-\s]+?)"
        r"</span>\s*</a>",
        re.DOTALL
    )
    avatar_pat = re.compile(
        r"<a\s+href=['\"][^'\"]*shop_id=(\d+)[^'\"]*['\"][^>]*>\s*"
        r"<img\s+src=['\"]?(//pic\d+\.chinaxinge\.com/[^'\"\s>]+\.(?:jpg|jpeg|png|gif))['\"]?\s",
        re.DOTALL
    )
    avatars = {}
    for m in avatar_pat.finditer(html):
        url = m.group(2)
        avatars[m.group(1)] = "https:" + url if url.startswith("//") else url

    for m in pattern.finditer(html):
        sid, name = m.group(1), m.group(2).strip()
        if sid not in seen and name:
            seen.add(sid)
            shops.append({"shop_id": sid, "name": name, "avatar": avatars.get(sid, "")})
    return shops

def get_shop_pages(html):
    pages = re.findall(r'page=(\d+)', html)
    return max(int(p) for p in pages) if pages else 1

def extract_shop_detail(html, shop_id):
    """从展厅首页提取联系信息 + 展品分类 + 展品ID列表"""
    result = {
        "shop_id": shop_id, "contact_name": "", "contact_phone": "",
        "email": "", "address": "", "province": "", "city": "",
        "website": "", "description": "", "categories": [], "product_ids": [],
    }
    # 联系人
    m = re.search(r'联系人[：:]\s*([\u4e00-\u9fa5A-Za-z·\s]+?)(?:\s|&|<|$)', html)
    if m: result["contact_name"] = m.group(1).strip()[:50]
    # 电话
    m = re.search(r'电话[：:]\s*([\d\-\+\(\)\s]{7,30})', html)
    if m: result["contact_phone"] = m.group(1).strip()[:20]
    if not result["contact_phone"] or len(result["contact_phone"]) < 7:
        m = re.search(r'(1[3-9]\d{9})', html)
        if m: result["contact_phone"] = m.group(1)
    # Email
    m = re.search(r'Email[：:]\s*<a[^>]*>([^<]+)</a>', html, re.IGNORECASE)
    if m: result["email"] = m.group(1).strip()[:100]
    # 地址
    m = re.search(r'地址[：:]\s*(.*?)(?:\s*邮政编码|<br|</p|$)', html)
    if m:
        raw = re.sub(r'<[^>]+>', '', m.group(1)).strip()
        result["address"] = raw[:255]
        prov_m = re.match(r'([\u4e00-\u9fa5]{2,3}省|北京|上海|天津|重庆|内蒙古|广西|西藏|宁夏|新疆|香港|澳门)', raw)
        if prov_m: result["province"] = prov_m.group(1)
    # 独立域名
    m = re.search(r'<p>(https?://[a-z0-9\-]+(?:\.chinaxinge\.com|\.ag188\.com)[^<]*)</p>', html)
    if m: result["website"] = m.group(1)[:255]
    # 分类
    cat_pat = re.compile(r'shop_gride=(\d+)[^>]*>\s*【?([^<【】]+?)】?\s*\((\d+)\)\s*</a>')
    for m in cat_pat.finditer(html):
        result["categories"].append({"source_id": m.group(1), "name": m.group(2).strip()[:100], "pigeon_count": int(m.group(3))})
    # 展品ID
    prod_pat = re.compile(r'showit\.asp\?shop_id=\d+&id=(\d+)')
    seen = set()
    for m in prod_pat.finditer(html):
        pid = m.group(1)
        if pid not in seen:
            seen.add(pid)
            result["product_ids"].append(pid)
    return result

def crawl_shops(dry_run=False):
    """增量抓取展厅"""
    print("\n" + "=" * 60)
    print("🏪 展厅增量 (Shops)")

    rows = mysql_query("SELECT source_id FROM shops WHERE source_id IS NOT NULL")
    existing = {r[0] for r in rows if r and r[0]}
    print(f"   DB 现有: {len(existing)} 个展厅")

    # 抓列表
    html = fetch(f"{SHOP_LIST_URL}?page=1")
    if not html:
        print("   ❌ 列表页获取失败")
        return {"type": "shops", "new": 0, "errors": ["list_fetch_fail"]}

    total_pages = get_shop_pages(html)
    all_shops = extract_shop_ids_from_list(html)
    print(f"   首页: {len(all_shops)} 个, 总页数: {total_pages}")

    for page in range(2, total_pages + 1):
        time.sleep(DELAY_LIST)
        html_pg = fetch(f"{SHOP_LIST_URL}?page={page}")
        if html_pg: all_shops.extend(extract_shop_ids_from_list(html_pg))

    seen_ids = set()
    unique = []
    for s in all_shops:
        if s["shop_id"] not in seen_ids:
            seen_ids.add(s["shop_id"])
            unique.append(s)
    print(f"   去重: {len(unique)} 个展厅")

    new_shops = [s for s in unique if s["shop_id"] not in existing]
    print(f"   🆕 新增: {len(new_shops)} 个")

    if not new_shops:
        print("   ✅ 无新展厅")
        return {"type": "shops", "new": 0, "errors": []}

    report = {"type": "shops", "new": 0, "details": [], "errors": []}
    for i, shop in enumerate(new_shops):
        sid, name, avatar = shop["shop_id"], shop["name"], shop["avatar"]
        print(f"   [{i+1}/{len(new_shops)}] shop_id={sid} {name}")

        html_d = fetch(f"{SHOP_DETAIL_URL}?method=home&shop_id={sid}")
        detail = extract_shop_detail(html_d, sid) if html_d else {"shop_id": sid, "product_ids": []}
        product_count = len(detail.get("product_ids", []))

        if not dry_run:
            sql = (
                f"INSERT INTO shops (user_id, source_id, name, avatar, province, city, "
                f"address, contact_name, contact_phone, website, pigeon_count, status) VALUES ("
                f"0, {mysql_escape(sid)}, {mysql_escape(name[:100])}, "
                f"{mysql_escape(avatar)}, {mysql_escape(detail.get('province', ''))}, "
                f"{mysql_escape(detail.get('city', ''))}, "
                f"{mysql_escape(detail.get('address', ''))}, "
                f"{mysql_escape(detail.get('contact_name', ''))}, "
                f"{mysql_escape(detail.get('contact_phone', ''))}, "
                f"{mysql_escape(detail.get('website', ''))}, "
                f"{product_count}, 1)"
            )
            if mysql_exec(sql):
                report["new"] += 1
                report["details"].append({"shop_id": sid, "name": name, "province": detail.get("province", ""), "products": product_count})
                print(f"      ✅ INSERT (🐦{product_count})")
            else:
                report["errors"].append(f"insert_fail: shop_id={sid}")
        else:
            report["new"] += 1

        time.sleep(DELAY_SHOP)

    print(f"   ✅ 展厅完成: 新增 {report['new']} 个, 错误 {len(report['errors'])}")
    return report

# ============================================================
# 4. 铭鸽 (Pigeons) 增量
# 数据源: 逐展厅抓 product ID 列表，对比 DB
# 对比字段: 展品 product_id → pigeons.source_id
# 新铭鸽: INSERT + 抓详情
# ============================================================

PRODUCT_DETAIL_BASE = "https://www.chinaxinge.com/xinge/shop"

def fetch_shop_products(shop_source_id):
    """抓取展厅首页，提取展品ID列表"""
    html = fetch(f"{SHOP_DETAIL_URL}?method=home&shop_id={shop_source_id}")
    if not html: return []
    prod_pat = re.compile(r'showit\.asp\?shop_id=\d+&id=(\d+)')
    seen = set()
    ids = []
    for m in prod_pat.finditer(html):
        pid = m.group(1)
        if pid not in seen:
            seen.add(pid)
            ids.append(pid)
    return ids

def extract_product_detail(html, product_id, shop_id):
    """从展品详情页提取结构化数据"""
    result = {
        "product_id": product_id, "shop_id": shop_id,
        "name": "", "ring_number": "", "bloodline": "",
        "color": "", "eye_color": "", "gender": "",
        "category": "", "price": "", "images": [], "description": "",
    }
    # Title
    m = re.search(r'<title>(.*?)</title>', html)
    if m: result["name"] = m.group(1).split('-')[0].strip()[:100]
    # 去 script/style
    text = re.sub(r'<script[^>]*>.*?</script>', '', html, flags=re.DOTALL)
    text = re.sub(r'<style[^>]*>.*?</style>', '', text, flags=re.DOTALL)
    # 字段对
    rows = re.findall(r'align="right"[^>]*class=p1[^>]*>(.*?)</td>\s*<td[^>]*class=p1[^>]*>(.*?)</td>', text, re.DOTALL)
    for k, v in rows:
        k = re.sub(r'<[^>]+>', '', k).strip()
        v = re.sub(r'<[^>]+>', '', v).strip()
        if '鸽名' in k or '名\u3000' in k: result["name"] = v[:100]
        elif '环号' in k or '环\u3000' in k:
            result["ring_number"] = re.sub(r'\s+', ' ', v)[:50]
            m = re.search(r'(雄|雌|母|公)', v)
            if m: result["gender"] = '1' if m.group(1) in ('雄', '公') else '2'
        elif '血统' in k: result["bloodline"] = v[:100]
        elif '羽色' in k: result["color"] = v[:30]
        elif '眼砂' in k: result["eye_color"] = v[:30]
        elif '性别' in k: result["gender"] = '1' if ('雄' in v or '公' in v) else ('2' if ('雌' in v or '母' in v) else '')
        elif '目录' in k: result["category"] = re.sub(r'[\s【】]', '', v)[:100]
    # 价格
    m = re.search(r'[¥￥]\s*([\d,.]+)', html)
    if m: result["price"] = m.group(1).replace(',', '')
    # 图片
    big_imgs = re.findall(r'src="(http://pic\.chinaxinge\.com/[^"]*big[^"]*\.(?:jpg|jpeg|png))"', html)
    small_imgs = re.findall(r'src="(http://pic\.chinaxinge\.com/[^"]*small[^"]*\.(?:jpg|jpeg|png))"', html)
    result["images"] = list(dict.fromkeys(big_imgs + small_imgs))[:10]
    # 简介
    m = re.search(r'(?:简介|介绍|详细)[：:]*\s*(.*?)(?:<br\s*/?>\s*<br|</div>|</td></tr>|</table>)', text, re.DOTALL)
    if m:
        desc = re.sub(r'<[^>]+>', '', m.group(1)).strip().replace('&nbsp;', ' ')[:2000]
        if len(desc) > 10:
            desc = _strip_chinaxinge_nav(desc)
            if desc:
                result["description"] = desc
    return result

def crawl_pigeons(dry_run=False, max_shops=0):
    """增量抓取铭鸽 - 逐展厅检查新品"""
    print("\n" + "=" * 60)
    print("🐦 铭鸽增量 (Pigeons)")

    # 加载所有展厅
    rows = mysql_query("SELECT id, source_id, name, pigeon_count FROM shops WHERE source_id IS NOT NULL AND status = 1 ORDER BY id")
    shops = [{"db_id": int(r[0]), "source_id": r[1], "name": r[2], "pigeon_count": int(r[3]) if r[3] else 0} for r in rows if len(r) >= 4]
    print(f"   展厅: {len(shops)} 个")

    # 加载所有已有产品 source_id
    rows_ex = mysql_query("SELECT source_id FROM pigeons WHERE source_id IS NOT NULL")
    existing_products = {r[0] for r in rows_ex if r and r[0]}
    print(f"   DB 现有铭鸽: {len(existing_products)} 条")

    # 限制处理数量（按轮转策略）
    if max_shops > 0:
        import hashlib
        today = datetime.now().strftime("%Y%m%d")
        start_idx = int(hashlib.md5(today.encode()).hexdigest(), 16) % len(shops)
        shops = shops[start_idx:] + shops[:start_idx]
        shops = shops[:max_shops]
        print(f"   本轮检查: {len(shops)} 个展厅 (max_shops={max_shops})")

    report = {"type": "pigeons", "new": 0, "shops_checked": 0, "details": [], "errors": []}

    for i, shop in enumerate(shops):
        sid = shop["source_id"]
        shop_name = shop["name"]
        db_shop_id = shop["db_id"]

        # 快速跳过：如果展厅的 pigeon_count 没变，可能没新品
        # 但为了准确性，仍然检查产品列表（网络开销不大）

        print(f"   [{i+1}/{len(shops)}] {shop_name} (shop_id={sid})")
        product_ids = fetch_shop_products(sid)
        report["shops_checked"] += 1

        if not product_ids:
            print(f"      ⚪ 无展品数据")
            time.sleep(DELAY_SHOP)
            continue

        new_pids = [pid for pid in product_ids if pid not in existing_products]

        if not new_pids:
            print(f"      ✅ {len(product_ids)} 展品 (无新)")
            time.sleep(DELAY_SHOP)
            continue

        print(f"      🆕 {len(new_pids)}/{len(product_ids)} 新品")

        for pid in new_pids:
            # 尝试 model12 优先
            html_p = fetch(f"{PRODUCT_DETAIL_BASE}/model12/showit.asp?shop_id={sid}&id={pid}")
            if not html_p or len(html_p) < 500:
                # 尝试其他 model
                for m in range(1, 20):
                    html_p = fetch(f"{PRODUCT_DETAIL_BASE}/model{m}/showit.asp?shop_id={sid}&id={pid}")
                    if html_p and len(html_p) >= 500 and ('鸽' in html_p or '环' in html_p):
                        break
                    time.sleep(0.3)

            if not html_p or len(html_p) < 500 or ('鸽' not in html_p and '环' not in html_p):
                print(f"         ⚠️  product={pid} 无实质内容")
                continue

            detail = extract_product_detail(html_p, pid, sid)
            images_json = json.dumps(detail.get("images", []), ensure_ascii=False) if detail.get("images") else "[]"

            if not dry_run:
                sql = (
                    f"INSERT IGNORE INTO pigeons (user_id, shop_id, source_id, name, ring_number, "
                    f"bloodline, color, eye_color, gender, description, images, status) VALUES ("
                    f"0, {db_shop_id}, {mysql_escape(pid)}, "
                    f"{mysql_escape(detail.get('name', '')[:100])}, "
                    f"{mysql_escape(detail.get('ring_number', ''))}, "
                    f"{mysql_escape(detail.get('bloodline', ''))}, "
                    f"{mysql_escape(detail.get('color', ''))}, "
                    f"{mysql_escape(detail.get('eye_color', ''))}, "
                    f"{mysql_escape(detail.get('gender', ''))}, "
                    f"{mysql_escape(detail.get('description', ''))}, "
                    f"{mysql_escape(images_json)}, 1)"
                )
                if mysql_exec(sql):
                    report["new"] += 1
                    report["details"].append({"shop_id": sid, "product_id": pid, "ring": detail.get("ring_number", ""), "name": detail.get("name", "")})
                else:
                    report["errors"].append(f"insert_fail: pid={pid}")
            else:
                report["new"] += 1

            existing_products.add(pid)
            time.sleep(DELAY_DETAIL)

        # 更新展厅 pigeon_count
        if not dry_run:
            mysql_exec(f"UPDATE shops SET pigeon_count = {len(product_ids)} WHERE source_id = '{sid}'")

        time.sleep(DELAY_SHOP)

    print(f"   ✅ 铭鸽完成: 检查 {report['shops_checked']} 展厅, 新增 {report['new']} 条")
    return report

# ============================================================
# 主入口
# ============================================================

def main():
    parser = argparse.ArgumentParser(description="统一增量爬虫")
    parser.add_argument("--type", required=True, choices=["races", "lofts", "shops", "pigeons", "all"],
                        help="爬取类型")
    parser.add_argument("--dry-run", action="store_true", help="只对比不写入")
    parser.add_argument("--max-shops", type=int, default=0,
                        help="铭鸽: 每次最多检查几个展厅 (0=全部)")
    args = parser.parse_args()

    print("=" * 60)
    print(f"统一增量爬虫 — {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    print(f"模式: {'DRY-RUN' if args.dry_run else '写入'}")
    print(f"类型: {args.type}")
    print("=" * 60)

    all_reports = []
    types = [args.type] if args.type != "all" else ["lofts", "races", "shops", "pigeons"]

    for t in types:
        if t == "lofts":
            all_reports.append(crawl_lofts(args.dry_run))
        elif t == "races":
            all_reports.append(crawl_races(args.dry_run))
        elif t == "shops":
            all_reports.append(crawl_shops(args.dry_run))
        elif t == "pigeons":
            all_reports.append(crawl_pigeons(args.dry_run, args.max_shops))

    # 汇总
    print("\n" + "=" * 60)
    print("📊 汇总报告")
    total_new = sum(r.get("new", 0) + r.get("new_races", 0) for r in all_reports)
    total_results = sum(r.get("new_results", 0) for r in all_reports)
    for r in all_reports:
        t = r.get("type", "?")
        n = r.get("new", 0) + r.get("new_races", 0)
        nr = r.get("new_results", 0)
        errs = len(r.get("errors", []))
        print(f"   {t:10s}: +{n:>4} ({nr:>6} 成绩)" if nr else f"   {t:10s}: +{n:>4}")
    print(f"   {'合计':10s}: +{total_new:>4} ({total_results:>6} 成绩)")

    # 输出报告 JSON
    report_path = os.path.join(SCRIPT_DIR, "crawl_incremental_all_report.json")
    with open(report_path, "w", encoding="utf-8") as f:
        json.dump({
            "timestamp": datetime.now().isoformat(),
            "mode": "dry-run" if args.dry_run else "write",
            "types": types,
            "total_new": total_new,
            "total_results": total_results,
            "reports": all_reports,
        }, f, ensure_ascii=False, indent=2)
    print(f"\n💾 报告: {report_path}")

if __name__ == "__main__":
    main()
