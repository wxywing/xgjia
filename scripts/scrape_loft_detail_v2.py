#!/usr/bin/env python3
"""爬取公棚详情页，补充联系信息/地址/奖金等字段（改进版 v2）"""
import json, re, urllib.request, time, sys, os
import signal

BASE = 'https://gdgp.chinaxinge.com'
OUT = '/Users/scentlibrart-007/.qclaw/workspace-agent-f8126f99/xgjia-website/scripts/lofts_detail_v2.json'

with open('/Users/scentlibrart-007/.qclaw/workspace-agent-f8126f99/xgjia-website/scripts/lofts_list.json', encoding='utf-8') as f:
    lofts = json.load(f)

# 优先金牌，然后按 gp_id 排序
lofts_sorted = sorted(lofts, key=lambda l: (0 if l['is_gold'] else 1, int(l['gp_id'])))

def fetch(url):
    """获取页面，返回 HTML（GBK 编码）"""
    req = urllib.request.Request(url, headers={
        'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
        'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language': 'zh-CN,zh;q=0.9',
    })
    try:
        data = urllib.request.urlopen(req, timeout=20).read()
        return data.decode('gbk', errors='replace')
    except Exception as e:
        print(f'  FAIL: {e}', file=sys.stderr)
        return None

def try_match(patterns, text, flags=0):
    """尝试多个正则模式，返回第一个匹配结果"""
    for pattern in patterns:
        m = re.search(pattern, text, flags)
        if m:
            return m.group(1).strip()
    return ''

def clean_text(text):
    """清理文本：去除 HTML 标签、标准化空白"""
    text = re.sub(r'<[^>]+>', ' ', text)
    text = re.sub(r'&nbsp;', ' ', text)
    text = re.sub(r'\s+', ' ', text)
    return text.strip()

def extract_detail(html, gp_id, list_name):
    """从详情页提取结构化数据（改进版 - 支持多模板）"""
    
    # 去掉 script/style 标签
    text = re.sub(r'<script[^>]*>.*?</script>', '', html, flags=re.DOTALL)
    text = re.sub(r'<style[^>]*>.*?</style>', '', text, flags=re.DOTALL)
    
    # 提取 title
    title_m = re.search(r'<title>(.*?)</title>', html, re.DOTALL)
    title = title_m.group(1).strip() if title_m else ''
    
    # 初始化结果
    result = {
        'gp_id': gp_id,
        'name': list_name or title.split('-')[0].split('_')[0].strip(),
        'province': '',
        'city': '',
        'address': '',
        'contact_name': '',
        'contact_phone': '',
        'logo': '',
        'description': '',
        'prize_pool': '',
        'entry_fee': '',
        'management_fee': '',
        'capacity': '',
        'race_distance': '',
        'race_type': '',
        'collect_start': '',
        'collect_end': '',
        'race_date': '',
        'website': '',
        'wechat': '',
    }
    
    # 1. 提取省份和城市（从地址中）
    addr_patterns = [
        r'地址[：:\s]*([^<\n]{5,255})',
        r'地\s*址[：:\s]*([^<\n]{5,255})',
        r'通讯\s*地址[：:\s]*([^<\n]{5,255})',
        r'所在\s*地址[：:\s]*([^<\n]{5,255})',
    ]
    addr_raw = try_match(addr_patterns, html)
    if addr_raw:
        addr_clean = clean_text(addr_raw)
        result['address'] = addr_clean[:255]
        
        # 从地址中提取省份
        prov_m = re.match(r'([\u4e00-\u9fa5]{2,3}省|北京|上海|天津|重庆|内蒙古|广西|西藏|宁夏|新疆|香港|澳门)', addr_clean)
        if prov_m:
            result['province'] = prov_m.group(1)
            rest = addr_clean[len(result['province']):].lstrip('省市')
            city_m = re.match(r'([\u4e00-\u9fa5]{2,4}市|[\u4e00-\u9fa5]{2,4}州|[\u4e00-\u9fa5]{2,3}区)', rest)
            if city_m:
                result['city'] = city_m.group(1)
    
    # 2. 提取联系人
    contact_patterns = [
        r'(?:负责人|联系人|场长)[：:\s]*([^<\n]{2,50})',
        r'负责\s*人[：:\s]*([^<\n]{2,50})',
        r'联系\s*人[：:\s]*([^<\n]{2,50})',
        r'场\s*长[：:\s]*([^<\n]{2,50})',
    ]
    contact_raw = try_match(contact_patterns, html, re.IGNORECASE)
    if contact_raw:
        result['contact_name'] = clean_text(contact_raw)[:50]
    
    # 3. 提取电话
    phone_patterns = [
        r'(?:电话|联系电话)[：:\s]*([\d\-\s]{7,20})',
        r'电\s*话[：:\s]*([\d\-\s]{7,20})',
        r'Tel[：:\s]*([\d\-\s]{7,20})',
    ]
    phone_raw = try_match(phone_patterns, html)
    if phone_raw:
        result['contact_phone'] = re.sub(r'\s+', '-', phone_raw.strip())[:20]
    
    # 4. 提取手机
    mobile_m = re.search(r'(?:手机|移动电话|联系手机)[：:\s]*(1[3-9]\d{9})', html, re.IGNORECASE)
    if mobile_m:
        result['contact_phone'] = mobile_m.group(1) or result['contact_phone']
    
    # 5. 提取奖金
    prize_patterns = [
        r'(?:总奖金|奖金总额|总奖金额)[：:\s]*([\d,.万万]+)',
        r'总\s*奖金[：:\s]*([\d,.万万]+)',
        r'奖金\s*总额[：:\s]*([\d,.万万]+)',
    ]
    prize_raw = try_match(prize_patterns, html)
    if prize_raw:
        result['prize_pool'] = prize_raw.replace(',', '').replace('万万', '亿')
    
    # 6. 提取参赛费
    fee_patterns = [
        r'参赛费[：:/\s每羽]*\s*([\d,.]+)',
        r'参赛\s*费[：:/\s每羽]*\s*([\d,.]+)',
        r'每\s*羽[：:\s]*([\d,.]+)',
    ]
    fee_raw = try_match(fee_patterns, html)
    if fee_raw:
        result['entry_fee'] = fee_raw.replace(',', '')
    
    # 7. 提取管理费
    mgmt_patterns = [
        r'(?:饲养|管理)费[：:/\s每羽]*\s*([\d,.]+)',
        r'饲养\s*费[：:/\s每羽]*\s*([\d,.]+)',
        r'管理\s*费[：:/\s每羽]*\s*([\d,.]+)',
        r'饲养管理\s*费[：:/\s每羽]*\s*([\d,.]+)',
    ]
    mgmt_raw = try_match(mgmt_patterns, html)
    if mgmt_raw:
        result['management_fee'] = mgmt_raw.replace(',', '')
    
    # 8. 提取收鸽容量
    cap_patterns = [
        r'(?:容量|收鸽|可收)[：:\s]*([\d,]+)\s*羽',
        r'容量[：:\s]*([\d,]+)\s*羽',
        r'收鸽\s*容量[：:\s]*([\d,]+)\s*羽',
        r'可\s*收[：:\s]*([\d,]+)\s*羽',
    ]
    cap_raw = try_match(cap_patterns, html)
    if cap_raw:
        result['capacity'] = cap_raw.replace(',', '')
    
    # 9. 提取比赛距离
    dist_patterns = [
        r'(?:决赛距离|比赛距离|赛程)[：:\s]*([\d]+)\s*公里',
        r'决赛\s*距离[：:\s]*([\d]+)\s*公里',
        r'比赛\s*距离[：:\s]*([\d]+)\s*公里',
        r'赛\s*程[：:\s]*([\d]+)\s*公里',
    ]
    dist_raw = try_match(dist_patterns, html)
    if dist_raw:
        result['race_distance'] = dist_raw
    
    # 10. 提取公棚类型（春棚/秋棚/春秋棚）
    if re.search(r'春棚', html):
        result['race_type'] = '春棚'
    if re.search(r'秋棚', html):
        result['race_type'] = '秋棚' if not result['race_type'] else '春秋棚'
    if re.search(r'春秋棚|春、秋棚', html):
        result['race_type'] = '春秋棚'
    
    # 11. 提取收鸽时间
    collect_patterns = [
        r'收鸽\s*时间[：:\s]*([\d]{1,2}月[\d]{1,2}日\s*[-~]\s*[\d]{1,2}月[\d]{1,2}日)',
        r'收鸽\s*日期[：:\s]*([\d]{1,2}月[\d]{1,2}日\s*[-~]\s*[\d]{1,2}月[\d]{1,2}日)',
    ]
    collect_raw = try_match(collect_patterns, html)
    if collect_raw:
        # 尝试解析开始和结束时间
        dates = re.findall(r'([\d]{1,2})月([\d]{1,2})日', collect_raw)
        if len(dates) >= 2:
            result['collect_start'] = f'{dates[0][0]}-{dates[0][1]}'
            result['collect_end'] = f'{dates[1][0]}-{dates[1][1]}'
    
    # 12. 提取比赛日期
    race_date_patterns = [
        r'比赛\s*日期[：:\s]*([\d]{1,2}月[\d]{1,2}日)',
        r'决赛\s*日期[：:\s]*([\d]{1,2}月[\d]{1,2}日)',
    ]
    race_date_raw = try_match(race_date_patterns, html)
    if race_date_raw:
        result['race_date'] = race_date_raw
    
    # 13. 提取简介
    desc_patterns = [
        r'(?:公棚简介|简介|介绍)[：:\s]*\s*(.*?)(?:<br\s*/?>\s*<br|</div>|参赛规程)',
        r'公棚\s*简介[：:\s]*\s*(.*?)(?:<br\s*/?>\s*<br|</div>|参赛规程)',
        r'关于\s*我们[：:\s]*\s*(.*?)(?:<br\s*/?>\s*<br|</div>|参赛规程)',
    ]
    for pattern in desc_patterns:
        desc_m = re.search(pattern, html, re.DOTALL | re.IGNORECASE)
        if desc_m:
            result['description'] = clean_text(desc_m.group(1))[:2000]
            break
    
    # 14. 提取 logo
    logo_patterns = [
        r'(?:logo|Logo)\s*[：:\s]*<img[^>]+src=["\']?(https?://[^"\'\s>]+\.(?:jpg|png|gif|jpeg))',
        r'<img[^>]+src=["\']?(https?://img\d*\.chinaxinge\.com/[^"\'\s>]*logo[^"\'\s>]*\.(?:jpg|png|gif|jpeg))',
        r'<img[^>]+id=["\']?logo["\']?[^\>]*src=["\']?(https?://[^"\'\s>]+)',
    ]
    for pattern in logo_patterns:
        logo_m = re.search(pattern, html, re.IGNORECASE)
        if logo_m:
            result['logo'] = logo_m.group(1)
            break
    
    # 15. 提取官网
    website_patterns = [
        r'(?:官网|网址|网站)[：:\s]*https?://([^<\s]+)',
        r'官方网站[：:\s]*https?://([^<\s]+)',
    ]
    website_raw = try_match(website_patterns, html)
    if website_raw:
        result['website'] = website_raw[:255]
    
    return result

results = []
errors = 0
# 断点续爬：加载已有结果
if os.path.exists(OUT):
    with open(OUT, encoding='utf-8') as f:
        results = json.load(f)
    done_ids = {r['gp_id'] for r in results}
    print(f'🔄 从断点续爬，已跳过 {len(done_ids)} 条', file=sys.stderr)
else:
    done_ids = set()

# 信号处理：SIGTERM/SIGINT 时保存
def save_and_exit(signum, frame):
    with open(OUT, 'w', encoding='utf-8') as f:
        json.dump(results, f, ensure_ascii=False, indent=2)
    print(f'\n⚠️ 信号{signum}，已保存{len(results)}条到{OUT}', file=sys.stderr)
    sys.exit(1)

signal.signal(signal.SIGTERM, save_and_exit)
signal.signal(signal.SIGINT, save_and_exit)

for i, loft in enumerate(lofts_sorted):
    gp_id = loft['gp_id']
    name = loft['name']
    if gp_id in done_ids:
        continue
    print(f'[{i+1}/{len(lofts_sorted)}] {name} (gp_id={gp_id})', file=sys.stderr)
    
    # 先尝试 default2.asp（会 302 到实际模板页）
    url = f'{BASE}/default2.asp?gp_id={gp_id}'
    html = fetch(url)
    
    if not html or len(html) < 300:
        # 直接尝试 style/mo1~mo38
        for mo in range(1, 39):
            url = f'{BASE}/style/mo{mo}/default.asp?gp_id={gp_id}'
            html = fetch(url)
            if html and len(html) > 500 and ('公棚' in html or '赛鸽' in html):
                break
            time.sleep(0.2)
            html = None
    
    if not html or len(html) < 300:
        print(f'  ❌ 无法获取详情', file=sys.stderr)
        errors += 1
        results.append({**loft, 'detail_status': 'failed'})
        time.sleep(0.8)
        continue
    
    detail = extract_detail(html, gp_id, name)
    result = {**loft, **detail, 'detail_status': 'ok'}
    results.append(result)
    
    ok_parts = []
    if detail['province']: ok_parts.append(f"📍{detail['province']}")
    if detail['contact_phone']: ok_parts.append(f"📞{detail['contact_phone']}")
    if detail['contact_name']: ok_parts.append(f"👤{detail['contact_name']}")
    if detail['prize_pool']: ok_parts.append(f"💰{detail['prize_pool']}")
    if detail['logo']: ok_parts.append(f"🖼️有Logo")
    if detail['description']: ok_parts.append(f"📝有简介")
    print(f'  ✅ {" ".join(ok_parts)}', file=sys.stderr)
    
    time.sleep(1.0)
    
    # 每50条保存一次
    if len(results) % 50 == 0:
        with open(OUT, 'w', encoding='utf-8') as f:
            json.dump(results, f, ensure_ascii=False, indent=2)

ok_count = len([r for r in results if r.get('detail_status') == 'ok'])
print(f'\nDone! Success: {ok_count}, Failed: {errors}', file=sys.stderr)

with open(OUT, 'w', encoding='utf-8') as f:
    json.dump(results, f, ensure_ascii=False, indent=2)
print(f'Saved to {OUT}', file=sys.stderr)
