#!/usr/bin/env python3
"""公棚详情爬虫 v4 - 整合 default.asp + about.asp 双页面抓取
修复：横向表格解析、简介提取、多模板支持
"""
import json, re, urllib.request, time, sys, os, signal

BASE = 'https://gdgp.chinaxinge.com'
SCRIPTS_DIR = '/Users/scentlibrart-007/.qclaw/workspace-agent-f8126f99/xgjia-website/scripts'
LOFTS_LIST = f'{SCRIPTS_DIR}/lofts_list.json'
OUT = f'{SCRIPTS_DIR}/lofts_detail_v4.json'

with open(LOFTS_LIST, encoding='utf-8') as f:
    lofts = json.load(f)
lofts_sorted = sorted(lofts, key=lambda l: (0 if l.get('is_gold') else 1, int(l['gp_id'])))

def fetch(url, timeout=20):
    req = urllib.request.Request(url, headers={
        'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
        'Accept-Language': 'zh-CN,zh;q=0.9',
    })
    try:
        data = urllib.request.urlopen(req, timeout=timeout).read()
        if len(data) < 500:
            return None
        return data.decode('gbk', errors='replace')
    except Exception:
        return None

def try_match(patterns, text, flags=0):
    for p in patterns:
        m = re.search(p, text, flags)
        if m:
            try:
                return m.group(1).strip()
            except IndexError:
                return m.group(0).strip()
    return ''

def clean(text):
    text = re.sub(r'<[^>]+>', ' ', text)
    text = re.sub(r'&nbsp;', ' ', text)
    text = re.sub(r'\s+', ' ', text)
    return text.strip()

def extract_from_main(html):
    """从 default.asp 主页提取：电话、地址、联系人、网站、logo"""
    text = re.sub(r'<script[^>]*>.*?</script>', '', html, flags=re.DOTALL)
    text = re.sub(r'<style[^>]*>.*?</style>', '', text, flags=re.DOTALL)

    result = {}
    
    # 电话（直接文本格式）
    result['contact_phone'] = try_match([
        r'电话[：:]\s*([0-9\-]{7,20})',
        r'Tel[：:]\s*([0-9\-]{7,20})',
        r'联系电话[：:]\s*([0-9\-]{7,20})',
        r'手机[：:]\s*([0-9\-]{11})',
    ], text)

    # 地址
    result['address'] = try_match([
        r'地址[：:]\s*([\u4e00-\u9fa5][^\n<]{5,80})',
        r'地\s*址[：:]\s*([\u4e00-\u9fa5][^\n<]{5,80})',
    ], text)

    # 联系人/负责人
    result['contact_name'] = try_match([
        r'负责人[：:]\s*([^\s<,，]{2,10})',
        r'联系人[：:]\s*([^\s<,，]{2,10})',
        r'联 系 人[：:]\s*([^\s<,，]{2,10})',
    ], text)

    # 网站
    m = re.search(r'互联网网址[：:\s]*<a[^>]+href=["\']?(https?://[^"\'\s>]+)', html)
    if m:
        result['website'] = m.group(1)
    else:
        m2 = re.search(r'href=["\']?(https?://\w+\.chinaxinge\.com)', html)
        if m2:
            result['website'] = m2.group(1)

    # Logo - 只匹配明确的logo，不取通用图标
    m = re.search(r'<img[^>]+src=["\']([^"\']+)["\'][^>]*(?:logo|Logo|LOGO)', html)
    if m:
        logo_url = m.group(1)
        if not logo_url.startswith('http'):
            logo_url = BASE + '/' + logo_url.lstrip('/')
        result['logo'] = logo_url
    # 不再 fallback 到第一个图片（太泛）

    # 描述（主页面中的描述文字）
    desc = try_match([
        r'本公棚[^\n<]{10,500}',
    ], text)
    if desc:
        result['description'] = clean(desc)[:2000]

    return result

def extract_from_about(html):
    """从 about.asp 提取：容量、届数、比赛类型、面积、简介"""
    result = {}
    
    # 横向表格解析
    for anchor in ['举办比赛性质', '可容量羽数', '容量', '举办届数']:
        pos = html.find(anchor)
        if pos < 0:
            continue
        table_start = html.rfind('<table', 0, pos)
        if table_start < 0:
            continue
        table_end = html.find('</table>', pos)
        if table_end < 0:
            continue
        table_html = html[table_start:table_end+8]
        
        rows = re.findall(r'<tr[^>]*>(.*?)</tr>', table_html, re.DOTALL)
        row_cells = []
        for row in rows:
            cells = re.findall(r'<td[^>]*>(.*?)</td>', row, re.DOTALL)
            texts = []
            for c in cells:
                t = re.sub(r'<[^>]+>', '', c).strip()
                t = re.sub(r'&nbsp;', ' ', t)
                t = re.sub(r'\s+', ' ', t).strip()
                texts.append(t)
            row_cells.append(texts)
        
        if len(row_cells) >= 2:
            labels = row_cells[0]
            values = row_cells[1]
            table_data = {}
            for i in range(min(len(labels), len(values))):
                label = labels[i].strip()
                value = values[i].strip()
                if value and value not in ('查看', '查看监控', '查看地图', ''):
                    table_data[label] = value
            
            # 映射
            field_map = {
                '举办比赛性质': 'race_type',
                '可容量羽数': 'capacity',
                '限收羽数': 'limit_count',
                '举办届数': 'edition',
                '公棚面积(平方米)': 'area',
                '公棚面积': 'area',
            }
            for label, field in field_map.items():
                if label in table_data:
                    result[field] = re.sub(r'\s+', '', table_data[label])[:30]
            break
    
    # 简介（about 页面中可能更完整）
    if 'description' not in result:
        # 模式1: "本公棚成立..."
        m = re.search(r'本公棚[^\n<]{10,500}', html, re.DOTALL)
        if m:
            text = clean(m.group())
            if len(text) > 20:
                result['description'] = text[:2000]
        
        # 模式2: td 中长文本
        if 'description' not in result:
            tds = re.findall(r'<td[^>]*>(.*?)</td>', html, re.DOTALL)
            for td in tds:
                text = clean(td)
                if len(text) > 50 and ('公棚' in text or '赛鸽' in text):
                    if 'javascript' not in text.lower() and 'function' not in text.lower():
                        result['description'] = text[:2000]
                        break
    
    return result

def find_template(gp_id):
    """确定公棚使用的模板编号"""
    # 先试 default2.asp
    url = f'{BASE}/default2.asp?gp_id={gp_id}'
    html = fetch(url, timeout=10)
    if html and len(html) > 500 and ('公棚' in html or '赛鸽' in html):
        return 'default2', html
    
    # 遍历 mo1-mo38
    for mo in range(1, 39):
        url = f'{BASE}/style/mo{mo}/default.asp?gp_id={gp_id}'
        html = fetch(url, timeout=10)
        if html and len(html) > 500 and ('公棚' in html or '赛鸽' in html):
            return f'mo{mo}', html
        time.sleep(0.1)
    
    return None, None

# 断点续爬
results = []
done_ids = set()
if os.path.exists(OUT):
    with open(OUT, encoding='utf-8') as f:
        results = json.load(f)
    done_ids = {r['gp_id'] for r in results}
    print(f'Resuming from {len(done_ids)} done', file=sys.stderr)

def save():
    with open(OUT, 'w', encoding='utf-8') as f:
        json.dump(results, f, ensure_ascii=False, indent=2)

signal.signal(signal.SIGTERM, lambda s,f: (save(), sys.exit(1)))
signal.signal(signal.SIGINT, lambda s,f: (save(), sys.exit(1)))

count = 0
for loft in lofts_sorted:
    gp_id = loft['gp_id']
    name = loft.get('name', '')
    
    if gp_id in done_ids:
        continue
    
    print(f'[{len(results)+1}/{len(lofts_sorted)}] {name[:20]} (gp_id={gp_id})', file=sys.stderr)
    
    # 1. 找模板 + 获取主页
    template, main_html = find_template(gp_id)
    if not main_html:
        print(f'  No content found', file=sys.stderr)
        results.append({**loft, 'detail_status': 'failed', 'template': ''})
        time.sleep(0.5)
        count += 1
        if count % 20 == 0: save()
        continue
    
    # 2. 从主页提取
    main_data = extract_from_main(main_html)
    
    # 3. 获取 about 页面
    about_data = {}
    if template and template != 'default2':
        about_url = f'{BASE}/style/{template}/about.asp?gp_id={gp_id}'
    else:
        # 尝试 mo1 的 about
        about_url = f'{BASE}/style/mo1/about.asp?gp_id={gp_id}'
    
    about_html = fetch(about_url, timeout=10)
    if about_html:
        about_data = extract_from_about(about_html)
    
    # 4. 合并（about 数据补充 main 没有的字段）
    merged = {**loft, **main_data, **about_data, 'template': template, 'detail_status': 'ok'}
    # about 的 description 补充 main 没有的情况
    if not main_data.get('description') and about_data.get('description'):
        merged['description'] = about_data['description']
    
    results.append(merged)
    
    # 打印提取结果
    parts = []
    if merged.get('contact_phone'): parts.append(f'T:{merged["contact_phone"]}')
    if merged.get('contact_name'): parts.append(f'N:{merged["contact_name"]}')
    if merged.get('capacity'): parts.append(f'C:{merged["capacity"]}')
    if merged.get('race_type'): parts.append(f'R:{merged["race_type"]}')
    if merged.get('edition'): parts.append(f'E:{merged["edition"]}')
    if merged.get('area'): parts.append(f'A:{merged["area"]}')
    if merged.get('description'): parts.append('D')
    if merged.get('logo'): parts.append('L')
    print(f'  [{template}] OK: {" ".join(parts) if parts else "minimal"}', file=sys.stderr)
    
    time.sleep(0.8)
    count += 1
    if count % 20 == 0:
        save()

save()
ok = len([r for r in results if r.get('detail_status') == 'ok'])
print(f'\nDone! ok={ok}/{len(results)}', file=sys.stderr)