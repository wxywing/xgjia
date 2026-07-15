#!/usr/bin/env python3
"""爬取铭鸽展厅详情 - 联系信息 + 展品分类目录 + 展品ID列表"""
import json, re, urllib.request, time, sys

BASE = 'https://www.chinaxinge.com'
OUT_DIR = '/Users/scentlibrart-007/.qclaw/workspace-agent-f8126f99/xgjia-website/scripts'

with open(f'{OUT_DIR}/shops_list.json', encoding='utf-8') as f:
    shops = json.load(f)

def fetch(url, encoding='gbk'):
    req = urllib.request.Request(url, headers={
        'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36'
    })
    for attempt in range(3):
        try:
            data = urllib.request.urlopen(req, timeout=20).read()
            return data.decode(encoding, errors='replace')
        except Exception as e:
            print(f'  Retry {attempt+1}: {e}', file=sys.stderr)
            time.sleep(2)
    return None

def extract_shop_detail(html, shop_id):
    """从展厅首页提取联系信息和展品分类"""
    result = {
        'shop_id': shop_id,
        'contact_name': '',
        'contact_phone': '',
        'email': '',
        'address': '',
        'province': '',
        'city': '',
        'website': '',
        'model': '',
        'description': '',
        'categories': [],   # [{source_id, name, pigeon_count}]
        'product_ids': [],  # [id1, id2, ...]
    }
    
    # 提取 model 编号（从 guestbook 链接或 CSS 路径）
    model_m = re.search(r'model(\d+)/', html)
    if model_m:
        result['model'] = model_m.group(1)
    
    # 联系人
    contact_m = re.search(r'联系人[：:]\s*([\u4e00-\u9fa5A-Za-z·\s]+?)(?:\s|&|<|$)', html)
    if contact_m:
        result['contact_name'] = contact_m.group(1).strip()[:50]
    
    # 电话
    phone_m = re.search(r'电话[：:]\s*([\d\-\+\(\)\s]{7,30})', html)
    if phone_m:
        result['contact_phone'] = phone_m.group(1).strip()[:20]
    
    # 手机号
    if not result['contact_phone'] or len(result['contact_phone']) < 7:
        mobile_m = re.search(r'(1[3-9]\d{9})', html)
        if mobile_m:
            result['contact_phone'] = mobile_m.group(1)
    
    # Email
    email_m = re.search(r'Email[：:]\s*<a[^>]*>([^<]+)</a>', html, re.IGNORECASE)
    if email_m:
        result['email'] = email_m.group(1).strip()[:100]
    
    # 地址
    addr_m = re.search(r'地址[：:]\s*(.*?)(?:\s*邮政编码|<br|</p|$)', html)
    if addr_m:
        raw = re.sub(r'<[^>]+>', '', addr_m.group(1)).strip()
        result['address'] = raw[:255]
        # 提取省份
        prov_m = re.match(r'([\u4e00-\u9fa5]{2,3}省|北京|上海|天津|重庆|内蒙古|广西|西藏|宁夏|新疆|香港|澳门)', raw)
        if prov_m:
            result['province'] = prov_m.group(1)
            rest = raw[len(prov_m.group(1)):].lstrip('省市')
            city_m = re.match(r'([\u4e00-\u9fa5]{2,4}市|[\u4e00-\u9fa5]{2,4}州)', rest)
            if city_m:
                result['city'] = city_m.group(1)
    
    # 独立域名
    web_m = re.search(r'<p>(https?://[a-z0-9\-]+(?:\.chinaxinge\.com|\.ag188\.com)[^<]*)</p>', html)
    if web_m:
        result['website'] = web_m.group(1)[:255]
    
    # 简介（about 页可能更全，但首页也有）
    # 暂时跳过，单独爬 about 页
    
    # 展品分类目录
    cat_pattern = re.compile(
        r'shop_gride=(\d+)[^>]*>\s*【?([^<【】]+?)】?\s*\((\d+)\)\s*</a>',
    )
    for m in cat_pattern.finditer(html):
        result['categories'].append({
            'source_id': m.group(1),
            'name': m.group(2).strip()[:100],
            'pigeon_count': int(m.group(3)),
        })
    
    # 展品 ID 列表
    prod_pattern = re.compile(r'showit\.asp\?shop_id=\d+&id=(\d+)')
    seen = set()
    for m in prod_pattern.finditer(html):
        pid = m.group(1)
        if pid not in seen:
            seen.add(pid)
            result['product_ids'].append(pid)
    
    return result

# 主流程
print(f'🚀 开始爬取 {len(shops)} 个展厅详情...', file=sys.stderr)

results = []
errors = 0

for i, shop in enumerate(shops):
    sid = shop['shop_id']
    name = shop.get('name', '')
    print(f'[{i+1}/{len(shops)}] {name} (shop_id={sid})', file=sys.stderr)
    
    # 从首页提取联系信息和分类
    url = f'{BASE}/xinge/shop/default.asp?method=home&shop_id={sid}'
    html = fetch(url)
    
    if not html or len(html) < 300:
        print(f'  ❌ 无法获取首页', file=sys.stderr)
        errors += 1
        results.append({**shop, 'detail_status': 'failed'})
        time.sleep(1.0)
        continue
    
    detail = extract_shop_detail(html, sid)
    
    # 如果有 model 编号，用它构建正确的展品路径
    model = detail.get('model', '')
    if model and len(detail['product_ids']) < 5:
        # 首页可能只有少量展品，需要从分类页取更多
        # 暂时只用首页数据
        pass
    
    result = {**shop, **detail, 'detail_status': 'ok'}
    results.append(result)
    
    cat_count = len(detail['categories'])
    prod_count = len(detail['product_ids'])
    parts = []
    if detail['province']: parts.append(f"📍{detail['province']}")
    if detail['contact_phone']: parts.append(f"📞{detail['contact_phone']}")
    if cat_count: parts.append(f"📂{cat_count}分类")
    if prod_count: parts.append(f"🐦{prod_count}展品")
    print(f'  ✅ {" ".join(parts)}', file=sys.stderr)
    
    time.sleep(1.0)

ok_count = len([r for r in results if r.get('detail_status') == 'ok'])
print(f'\nDone! Success: {ok_count}, Failed: {errors}', file=sys.stderr)

# 统计
total_cats = sum(len(r.get('categories', [])) for r in results if r.get('detail_status') == 'ok')
total_prods = sum(len(r.get('product_ids', [])) for r in results if r.get('detail_status') == 'ok')
has_phone = len([r for r in results if r.get('contact_phone')])
has_addr = len([r for r in results if r.get('province')])
print(f'  分类: {total_cats}, 展品ID: {total_prods}, 有电话: {has_phone}, 有省份: {has_addr}', file=sys.stderr)

out = f'{OUT_DIR}/shops_detail.json'
with open(out, 'w', encoding='utf-8') as f:
    json.dump(results, f, ensure_ascii=False, indent=2)
print(f'💾 → {out}', file=sys.stderr)
