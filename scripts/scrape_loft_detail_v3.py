#!/usr/bin/env python3
"""爬取公棚详情页 v3 - 基于 mo1 模板实际 HTML 结构分析"""
import json, re, urllib.request, time, sys, os, signal

BASE = 'https://gdgp.chinaxinge.com'
SCRIPTS_DIR = '/Users/scentlibrart-007/.qclaw/workspace-agent-f8126f99/xgjia-website/scripts'
LOFTS_LIST = f'{SCRIPTS_DIR}/lofts_list.json'
OUT = f'{SCRIPTS_DIR}/lofts_detail_v3.json'

with open(LOFTS_LIST, encoding='utf-8') as f:
    lofts = json.load(f)

lofts_sorted = sorted(lofts, key=lambda l: (0 if l['is_gold'] else 1, int(l['gp_id'])))

def fetch(url):
    req = urllib.request.Request(url, headers={
        'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
        'Accept-Language': 'zh-CN,zh;q=0.9',
    })
    try:
        data = urllib.request.urlopen(req, timeout=20).read()
        return data.decode('gbk', errors='replace')
    except Exception as e:
        print(f'  FAIL: {e}', file=sys.stderr)
        return None

def try_match(patterns, text, flags=0):
    for pattern in patterns:
        m = re.search(pattern, text, flags)
        if m:
            return m.group(1).strip()
    return ''

def clean_text(text):
    text = re.sub(r'<[^>]+>', ' ', text)
    text = re.sub(r'&nbsp;', ' ', text)
    text = re.sub(r'\s+', ' ', text)
    return text.strip()

def extract_detail(html, gp_id, list_name):
    """从详情页提取结构化数据（v3 - 修正正则以匹配实际 HTML 结构）

    mo1 模板关键发现：
    - 电话格式： 电话：13269159919  （直接在文本中，无 TD 分隔）
    - 地址格式： 地址：河北省保定市... （直接在文本中）
    - 奖金格式： 总奖金：XXX （在表格 TD 中）
    - 负责人格式： 负责人：XXX （在左侧栏）
    """
    text = re.sub(r'<script[^>]*>.*?</script>', '', html, flags=re.DOTALL)
    text = re.sub(r'<style[^>]*>.*?</style>', '', text, flags=re.DOTALL)

    title_m = re.search(r'<title>(.*?)</title>', html, re.DOTALL)
    title = title_m.group(1).strip() if title_m else ''

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
        'manager': '',
    }

    # 1. 地址（mo1: 地址：xxx 直接文本）
    addr_patterns = [
        r'地址[：:]?\s*([^\n<a>]{5,255})',
        r'地\s*址[：:]?\s*([^\n<a>]{5,255})',
        r'通讯地址[：:]?\s*([^\n<a>]{5,255})',
    ]
    addr_raw = try_match(addr_patterns, html)
    if addr_raw:
        addr_clean = clean_text(addr_raw)
        result['address'] = addr_clean[:255]
        prov_m = re.match(r'([\u4e00-\u9fa5]{2,3}省|北京|上海|天津|重庆|内蒙古|广西|西藏|宁夏|新疆|香港|澳门)', addr_clean)
        if prov_m:
            result['province'] = prov_m.group(1)
            rest = addr_clean[len(result['province']):].lstrip('省市')
            city_m = re.match(r'([\u4e00-\u9fa5]{2,4}市|[\u4e00-\u9fa5]{2,4}州|[\u4e00-\u9fa5]{2,3}区)', rest)
            if city_m:
                result['city'] = city_m.group(1)

    # 2. 电话（mo1: 电话：13269159919 直接文本，无 TD 分隔）
    phone_patterns = [
        r'电话[：:]\s*([0-9\-]{7,20})',
        r'联系电话[：:]\s*([0-9\-]{7,20})',
        r'Tel[：:]\s*([0-9\-]{7,20})',
        r'电\s*话[：:]\s*([0-9\-]{7,20})',
    ]
    phone_raw = try_match(phone_patterns, html)
    if phone_raw:
        result['contact_phone'] = re.sub(r'\s+', '-', phone_raw.strip())[:20]
    # 手机号（备用）
    if not result['contact_phone']:
        mobile_m = re.search(r'(?:手机|移动电话)[：:]\s*(1[3-9]\d{9})', html, re.IGNORECASE)
        if mobile_m:
            result['contact_phone'] = mobile_m.group(1)

    # 3. 负责人/联系人
    contact_patterns = [
        r'负责人[：:]\s*([^\n<\s]{2,30})',
        r'联系人[：:]\s*([^\n<\s]{2,30})',
        r'场\s*长[：:]\s*([^\n<\s]{2,30})',
    ]
    contact_raw = try_match(contact_patterns, html)
    if contact_raw:
        result['contact_name'] = clean_text(contact_raw)[:50]
        result['manager'] = result['contact_name']

    # 4. 总奖金（mo1: 在表格中 <td>总奖金：</td><td>XXX</td>）
    prize_patterns = [
        r'总奖金[：:/\s]*([\d,.万千百十]+(?:万|元)?)',
        r'奖金\s*总额[：:/\s]*([\d,.万千百十]+(?:万|元)?)',
        r'总奖金额[：:/\s]*([\d,.万千百十]+(?:万|元)?)',
        r'奖金[：:/\s]*([\d,.万千百十]+(?:万|元)?)',
    ]
    prize_raw = try_match(prize_patterns, html)
    if prize_raw:
        result['prize_pool'] = prize_raw.replace(',', '')

    # 5. 参赛费
    fee_patterns = [
        r'参赛费[：:/\s每羽]*\s*([\d,.万千百十]+(?:万|元)?)',
        r'每\s*羽[：:/\s]*([\d,.万千百十]+(?:万|元)?)',
        r'缴费[：:/\s]*([\d,.万千百十]+(?:万|元)?)',
    ]
    fee_raw = try_match(fee_patterns, html)
    if fee_raw:
        result['entry_fee'] = fee_raw.replace(',', '')

    # 6. 饲养费/管理费
    mgmt_patterns = [
        r'饲养费[：:/\s每羽]*\s*([\d,.万千百十]+(?:万|元)?)',
        r'管理费[：:/\s每羽]*\s*([\d,.万千百十]+(?:万|元)?)',
        r'饲养管理费[：:/\s每羽]*\s*([\d,.万千百十]+(?:万|元)?)',
        r'饲养\s*管理\s*费[：:/\s每羽]*\s*([\d,.万千百十]+(?:万|元)?)',
    ]
    mgmt_raw = try_match(mgmt_patterns, html)
    if mgmt_raw:
        result['management_fee'] = mgmt_raw.replace(',', '')

    # 7. 收鸽容量
    cap_patterns = [
        r'容\s*量[：:\s]*([\d,]+)\s*羽',
        r'收鸽容量[：:\s]*([\d,]+)\s*羽',
        r'可\s*收[：:\s]*([\d,]+)\s*羽',
        r'容量[：:\s]*([\d,]+)\s*羽',
    ]
    cap_raw = try_match(cap_patterns, html)
    if cap_raw:
        result['capacity'] = cap_raw.replace(',', '')

    # 8. 决赛距离
    dist_patterns = [
        r'决赛距离[：:\s]*([\d,]+)\s*(?:公里|km|KM)',
        r'比赛距离[：:\s]*([\d,]+)\s*(?:公里|km|KM)',
        r'赛\s*程[：:\s]*([\d,]+)\s*(?:公里|km|KM)',
        r'(\d+)\s*(?:公里|km|KM)',
    ]
    dist_raw = try_match(dist_patterns, html)
    if dist_raw:
        result['race_distance'] = dist_raw.replace(',', '')

    # 9. 公棚类型
    has_spring = bool(re.search(r'春棚', html))
    has_autumn = bool(re.search(r'秋棚', html))
    if has_spring and has_autumn:
        result['race_type'] = '春秋棚'
    elif has_spring:
        result['race_type'] = '春棚'
    elif has_autumn:
        result['race_type'] = '秋棚'

    # 10. 收鸽时间
    collect_patterns = [
        r'收鸽\s*时间[：:\s]*([\d]{1,2}月[\d]{1,2}日\s*[-~]\s*[\d]{1,2}月[\d]{1,2}日)',
        r'收鸽\s*日期[：:\s]*([\d]{1,2}月[\d]{1,2}日\s*[-~]\s*[\d]{1,2}月[\d]{1,2}日)',
    ]
    collect_raw = try_match(collect_patterns, html)
    if collect_raw:
        dates = re.findall(r'(\d+)月(\d+)日', collect_raw)
        if len(dates) >= 2:
            result['collect_start'] = f'{dates[0][0]}-{dates[0][1]}'
            result['collect_end'] = f'{dates[1][0]}-{dates[1][1]}'

    # 11. 比赛日期
    race_date_patterns = [
        r'决赛日期[：:\s]*([\d]{1,2}月[\d]{1,2}日)',
        r'比赛日期[：:\s]*([\d]{1,2}月[\d]{1,2}日)',
        r'比赛\s*日期[：:\s]*([\d]{1,2}月[\d]{1,2}日)',
    ]
    race_date_raw = try_match(race_date_patterns, html)
    if race_date_raw:
        result['race_date'] = race_date_raw

    # 12. 公棚简介
    desc_patterns = [
        r'公棚简介[：:\s]*\s*(.*?)(?:<br\s*/?>[\s\n]*<br|</div>|参赛规程|互联网)',
        r'关于\s*我们[：:\s]*\s*(.*?)(?:<br\s*/?>[\s\n]*<br|</div>)',
        r'简\s*介[：:\s]*\s*(.*?)(?:<br\s*/?>[\s\n]*<br|</div>)',
    ]
    for pattern in desc_patterns:
        desc_m = re.search(pattern, html, re.DOTALL | re.IGNORECASE)
        if desc_m:
            result['description'] = clean_text(desc_m.group(1))[:2000]
            break

    # 13. Logo
    logo_patterns = [
        r'<img[^>]+src=["\']?(https?://img\d*\.chinaxinge\.com/[^"\'\s>]*logo[^"\'\s>]*\.(?:jpg|png|gif|jpeg))',
        r'<img[^>]+(logo)[^\>]*src=["\']?(https?://[^"\'\s>]+\.(?:jpg|png|gif|jpeg))',
        r'(https?://img\d*\.chinaxinge\.com/[^"\'\s>]*logo[^"\'\s>]*\.(?:jpg|png|gif|jpeg))',
    ]
    for pattern in logo_patterns:
        logo_m = re.search(pattern, html, re.IGNORECASE)
        if logo_m:
            result['logo'] = logo_m.group(1)
            break

    # 14. 官网
    website_patterns = [
        r'(?:官网|网址|网站)[：:\s]*(https?://[^\s<>]+)',
        r'互联网网址[：:\s]*<a[^>]+href=["\']?(https?://[^"\'\s>]+)',
    ]
    website_raw = try_match(website_patterns, html)
    if website_raw:
        result['website'] = website_raw[:255]

    return result

# 断点续爬
if os.path.exists(OUT):
    with open(OUT, encoding='utf-8') as f:
        results = json.load(f)
    done_ids = {r['gp_id'] for r in results}
    print(f'Resuming: skip {len(done_ids)} done', file=sys.stderr)
else:
    results = []
    done_ids = set()

def save_and_exit(signum, frame):
    with open(OUT, 'w', encoding='utf-8') as f:
        json.dump(results, f, ensure_ascii=False, indent=2)
    print(f'\nSignal {signum}, saved {len(results)} to {OUT}', file=sys.stderr)
    sys.exit(1)

signal.signal(signal.SIGTERM, save_and_exit)
signal.signal(signal.SIGINT, save_and_exit)

for i, loft in enumerate(lofts_sorted):
    gp_id = loft['gp_id']
    name = loft['name']
    if gp_id in done_ids:
        continue
    print(f'[{i+1}/{len(lofts_sorted)}] {name} (gp_id={gp_id})', file=sys.stderr)

    html = None
    # 先试 default2.asp
    url = f'{BASE}/default2.asp?gp_id={gp_id}'
    html = fetch(url)
    if not html or len(html) < 500 or ('公棚' not in html and '赛鸽' not in html):
        # 遍历 mo1-mo38 找有效模板
        for mo in range(1, 39):
            url = f'{BASE}/style/mo{mo}/default.asp?gp_id={gp_id}'
            html = fetch(url)
            if html and len(html) > 500 and ('公棚' in html or '赛鸽' in html):
                print(f'  Template: mo{mo}', file=sys.stderr)
                break
            time.sleep(0.2)
            html = None

    if not html or len(html) < 500:
        print(f'  No content', file=sys.stderr)
        results.append({**loft, 'detail_status': 'failed'})
        time.sleep(0.8)
        continue

    detail = extract_detail(html, gp_id, name)
    result = {**loft, **detail, 'detail_status': 'ok'}
    results.append(result)

    ok_parts = []
    if detail['province']: ok_parts.append(f'P:{detail["province"]}')
    if detail['contact_phone']: ok_parts.append(f'T:{detail["contact_phone"]}')
    if detail['contact_name']: ok_parts.append(f'N:{detail["contact_name"]}')
    if detail['prize_pool']: ok_parts.append(f'M:{detail["prize_pool"]}')
    if detail['entry_fee']: ok_parts.append(f'F:{detail["entry_fee"]}')
    if detail['management_fee']: ok_parts.append(f'G:{detail["management_fee"]}')
    if detail['capacity']: ok_parts.append(f'C:{detail["capacity"]}')
    if detail['logo']: ok_parts.append('L')
    if detail['description']: ok_parts.append('D')
    print(f'  OK: {" ".join(ok_parts) if ok_parts else "no new fields"}', file=sys.stderr)

    time.sleep(1.0)
    if len(results) % 20 == 0:
        with open(OUT, 'w', encoding='utf-8') as f:
            json.dump(results, f, ensure_ascii=False, indent=2)

ok_count = len([r for r in results if r.get('detail_status') == 'ok'])
print(f'\nDone! ok={ok_count}', file=sys.stderr)
with open(OUT, 'w', encoding='utf-8') as f:
    json.dump(results, f, ensure_ascii=False, indent=2)
print(f'Saved {len(results)} to {OUT}', file=sys.stderr)