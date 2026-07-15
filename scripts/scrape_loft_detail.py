#!/usr/bin/env python3
"""爬取公棚详情页，补充联系信息/地址/奖金等字段"""
import json, re, urllib.request, time, sys

BASE = 'https://gdgp.chinaxinge.com'
OUT = '/Users/scentlibrart-007/.qclaw/workspace-agent-f8126f99/xgjia-website/scripts/lofts_detail.json'

with open('/Users/scentlibrart-007/.qclaw/workspace-agent-f8126f99/xgjia-website/scripts/lofts_list.json', encoding='utf-8') as f:
    lofts = json.load(f)

# 优先金牌，然后按 gp_id 排序
lofts_sorted = sorted(lofts, key=lambda l: (0 if l['is_gold'] else 1, int(l['gp_id'])))

def fetch(url):
    req = urllib.request.Request(url, headers={
        'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36'
    })
    try:
        data = urllib.request.urlopen(req, timeout=20).read()
        return data.decode('gbk', errors='replace')
    except Exception as e:
        print(f'  FAIL: {e}', file=sys.stderr)
        return None

def extract_detail(html, gp_id, list_name):
    """从详情页提取结构化数据"""
    # 获取 title
    title_m = re.search(r'<title>(.*?)</title>', html, re.DOTALL)
    title = title_m.group(1).strip() if title_m else ''
    
    # 去掉 script/style
    text = re.sub(r'<script[^>]*>.*?</script>', '', html, flags=re.DOTALL)
    text = re.sub(r'<style[^>]*>.*?</style>', '', text, flags=re.DOTALL)
    
    # 提取省份和城市 —— 列表页按省份分组，但详情页不一定有
    province = ''
    city = ''
    
    # 从详情页地址行提取
    addr_m = re.search(r'地址[：:]\s*(.*?)(?:<br|</td|</div|\n)', text)
    address = ''
    if addr_m:
        raw = re.sub(r'<[^>]+>', '', addr_m.group(1)).strip()
        address = raw[:255]
        # 尝试提取省份
        prov_m = re.match(r'([\u4e00-\u9fa5]{2,3}省|北京|上海|天津|重庆|内蒙古|广西|西藏|宁夏|新疆|香港|澳门)', raw)
        if prov_m:
            province = prov_m.group(1)
            rest = raw[len(province):].lstrip('省市')
            city_m = re.match(r'([\u4e00-\u9fa5]{2,4}市|[\u4e00-\u9fa5]{2,4}州)', rest)
            if city_m:
                city = city_m.group(1)
    
    # 联系人
    contact_m = re.search(r'(?:负责人|联系人|场长)[：:]\s*(.*?)(?:<br|</td|</div|手机|电话|\n)', text)
    contact_name = ''
    if contact_m:
        contact_name = re.sub(r'<[^>]+>', '', contact_m.group(1)).strip()[:50]
    
    # 电话
    phone_m = re.search(r'(?:电话|联系电话)[：:]\s*([\d\-\s]{7,20})', text)
    phone = phone_m.group(1).strip() if phone_m else ''
    
    # 手机
    mobile_m = re.search(r'(?:手机|移动电话)[：:]\s*(1[3-9]\d{9})', text)
    mobile = mobile_m.group(1) if mobile_m else ''
    contact_phone = phone or mobile
    
    # 奖金
    prize_m = re.search(r'(?:总奖金|奖金总额)[：:]\s*([\d,.万万]+)', text)
    prize_pool = ''
    if prize_m:
        prize_pool = prize_m.group(1).replace(',', '').replace('万万', '亿')
    
    # 参赛费
    fee_m = re.search(r'参赛费[：:/每羽]*\s*([\d,.]+)', text)
    entry_fee = fee_m.group(1).replace(',', '') if fee_m else ''
    
    # 管理费
    mgmt_m = re.search(r'(?:饲养|管理)费[：:/每羽]*\s*([\d,.]+)', text)
    management_fee = mgmt_m.group(1).replace(',', '') if mgmt_m else ''
    
    # 收鸽容量
    cap_m = re.search(r'(?:容量|收鸽|可收)[：:]*\s*([\d,]+)\s*羽', text)
    capacity = cap_m.group(1).replace(',', '') if cap_m else ''
    
    # 比赛距离
    dist_m = re.search(r'(?:决赛距离|比赛距离|赛程)[：:]*\s*([\d]+)\s*公里', text)
    race_distance = dist_m.group(1) if dist_m else ''
    
    # 简介
    desc_m = re.search(r'(?:公棚简介|简介|介绍)[：:]*\s*(.*?)(?:<br\s*/?>\s*<br|</div>|参赛规程)', text, re.DOTALL)
    description = ''
    if desc_m:
        description = re.sub(r'<[^>]+>', '', desc_m.group(1)).strip()
        description = description.replace('&nbsp;', ' ').strip()[:2000]
    
    # logo
    logo_m = re.search(r'(?:logo|Logo)\s*[：:]*\s*<img[^>]+src=["\']?(https?://[^"\'\s>]+\.(?:jpg|png|gif|jpeg))', html)
    logo = logo_m.group(1) if logo_m else ''
    if not logo:
        # 尝试从页面 top 区域取图
        logo_m2 = re.search(r'<img[^>]+src=["\']?(https?://img\d*\.chinaxinge\.com/[^"\'\s>]*logo[^"\'\s>]*\.(?:jpg|png|gif|jpeg))', html, re.IGNORECASE)
        logo = logo_m2.group(1) if logo_m2 else ''
    
    # name
    name = list_name if list_name else title.split('-')[0].split('_')[0].strip()
    
    return {
        'gp_id': gp_id,
        'name': name[:100],
        'province': province[:30],
        'city': city[:30],
        'address': address[:255],
        'contact_name': contact_name[:50],
        'contact_phone': contact_phone[:20],
        'logo': logo[:255],
        'description': description[:2000],
        'prize_pool': prize_pool,
        'entry_fee': entry_fee,
        'management_fee': management_fee,
        'capacity': capacity,
        'race_distance': race_distance,
    }

results = []
errors = 0
# 断点续爬：加载已有结果
import os
if os.path.exists(OUT):
    with open(OUT, encoding='utf-8') as f:
        results = json.load(f)
    done_ids = {r['gp_id'] for r in results}
    print(f'🔄 从断点续爬，已跳过 {len(done_ids)} 条', file=sys.stderr)
else:
    done_ids = set()

# 信号处理：SIGTERM/SIGINT 时保存
import signal
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
        # 直接尝试 style/mo1~mo30
        for mo in range(1, 31):
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
    if detail['prize_pool']: ok_parts.append(f"💰{detail['prize_pool']}")
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
