#!/usr/bin/env python3
"""爬取铭鸽展品详情 - 足环号/血统/羽色/眼砂/性别/图片/价格等"""
import json, re, urllib.request, time, sys

BASE = 'https://www.chinaxinge.com'
OUT_DIR = '/Users/scentlibrart-007/.qclaw/workspace-agent-f8126f99/xgjia-website/scripts'

def fetch(url, encoding='gbk'):
    req = urllib.request.Request(url, headers={
        'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36'
    })
    for attempt in range(3):
        try:
            data = urllib.request.urlopen(req, timeout=20).read()
            return data.decode(encoding, errors='replace')
        except Exception as e:
            if attempt == 2:
                print(f'  FAIL: {e}', file=sys.stderr)
            time.sleep(2)
    return None

def extract_product(html, product_id, shop_id):
    """从展品详情页提取结构化数据"""
    result = {
        'product_id': product_id,
        'shop_id': shop_id,
        'name': '',
        'ring_number': '',
        'bloodline': '',
        'color': '',
        'eye_color': '',
        'gender': '',
        'category': '',
        'price': '',
        'images': [],
        'description': '',
    }
    
    # Title 提取名称
    title_m = re.search(r'<title>(.*?)</title>', html)
    if title_m:
        name = title_m.group(1).split('-')[0].strip()
        result['name'] = name[:100]
    
    # 去掉 script/style
    text = re.sub(r'<script[^>]*>.*?</script>', '', html, flags=re.DOTALL)
    text = re.sub(r'<style[^>]*>.*?</style>', '', text, flags=re.DOTALL)
    
    # 字段对提取
    rows = re.findall(r'align="right"[^>]*class=p1[^>]*>(.*?)</td>\s*<td[^>]*class=p1[^>]*>(.*?)</td>', text, re.DOTALL)
    for k, v in rows:
        k = re.sub(r'<[^>]+>', '', k).strip().replace('\u3000', '')
        v = re.sub(r'<[^>]+>', '', v).strip()
        
        if '鸽名' in k or '名　' in k:
            result['name'] = v[:100]
        elif '环号' in k or '环　' in k:
            result['ring_number'] = re.sub(r'\s+', ' ', v)[:50]
            # 从环号行提取性别
            gender_m = re.search(r'(雄|雌|母|公)', v)
            if gender_m:
                g = gender_m.group(1)
                if g in ('雄', '公'):
                    result['gender'] = '1'
                elif g in ('雌', '母'):
                    result['gender'] = '2'
        elif '血统' in k:
            result['bloodline'] = v[:100]
        elif '羽色' in k or '羽　' in k:
            result['color'] = v[:30]
        elif '眼砂' in k or '眼　' in k:
            result['eye_color'] = v[:30]
        elif '性别' in k or '性　' in k:
            if '雄' in v or '公' in v:
                result['gender'] = '1'
            elif '雌' in v or '母' in v:
                result['gender'] = '2'
        elif '目录' in k or '目　' in k:
            cat = re.sub(r'[\s【】]', '', v)
            if cat:
                result['category'] = cat[:100]
    
    # 价格
    price_m = re.search(r'[¥￥]\s*([\d,.]+)', html)
    if price_m:
        result['price'] = price_m.group(1).replace(',', '')
    
    # 图片 - 大图优先
    big_imgs = re.findall(r'src="(http://pic\.chinaxinge\.com/[^"]*big[^"]*\.(?:jpg|jpeg|png))"', html)
    small_imgs = re.findall(r'src="(http://pic\.chinaxinge\.com/[^"]*small[^"]*\.(?:jpg|jpeg|png))"', html)
    all_imgs = list(dict.fromkeys(big_imgs + small_imgs))[:10]  # 去重，最多10张
    result['images'] = all_imgs
    
    # 简介 - 找"简介"后的文本
    desc_m = re.search(r'(?:简介|介绍|详细)[：:]*\s*(.*?)(?:<br\s*/?>\s*<br|</div>|</td></tr>|</table>)', text, re.DOTALL)
    if desc_m:
        desc = re.sub(r'<[^>]+>', '', desc_m.group(1)).strip()
        desc = desc.replace('&nbsp;', ' ').strip()[:2000]
        if len(desc) > 10:  # 太短可能是导航文字
            result['description'] = desc
    
    return result

# 主流程 - 从 shops_detail.json 读取展品ID列表
detail_path = f'{OUT_DIR}/shops_detail.json'
try:
    with open(detail_path, encoding='utf-8') as f:
        shops = json.load(f)
except FileNotFoundError:
    print('❌ 请先运行 scrape_shop_detail.py 生成 shops_detail.json', file=sys.stderr)
    sys.exit(1)

# 收集所有展品
all_products = []
for shop in shops:
    if shop.get('detail_status') != 'ok':
        continue
    sid = shop['shop_id']
    model = shop.get('model', '') or '12'
    for pid in shop.get('product_ids', []):
        all_products.append((sid, model, pid))

# 去重
seen = set()
unique_products = []
for sid, model, pid in all_products:
    if pid not in seen:
        seen.add(pid)
        unique_products.append((sid, model, pid))

print(f'🚀 共需爬取 {len(unique_products)} 个展品 (来自 {len(shops)} 个展厅)', file=sys.stderr)

import os, signal
results = []
errors = 0
# 断点续爬
if os.path.exists(f'{OUT_DIR}/products_detail.json'):
    with open(f'{OUT_DIR}/products_detail.json', encoding='utf-8') as f:
        results = json.load(f)
    done_pids = {r['product_id'] for r in results if r.get('product_id')}
    print(f'🔄 从断点续爬，已跳过 {len(done_pids)} 条', file=sys.stderr)
else:
    done_pids = set()

def save_and_exit(signum, frame):
    with open(f'{OUT_DIR}/products_detail.json', 'w', encoding='utf-8') as f:
        json.dump(results, f, ensure_ascii=False, indent=2)
    print(f'\n⚠️ 信号{signum}，已保存{len(results)}条', file=sys.stderr)
    sys.exit(1)
signal.signal(signal.SIGTERM, save_and_exit)
signal.signal(signal.SIGINT, save_and_exit)

for i, (sid, model, pid) in enumerate(unique_products):
    if pid in done_pids:
        continue
    if i > 0 and i % 100 == 0:
        # 每100条保存一次中间结果
        with open(f'{OUT_DIR}/products_detail.json', 'w', encoding='utf-8') as f:
            json.dump(results, f, ensure_ascii=False, indent=2)
        print(f'  💾 保存: {len(results)} 条', file=sys.stderr)
    
    if i % 50 == 0:
        print(f'[{i+1}/{len(unique_products)}] shop={sid} product={pid}', file=sys.stderr)
    
    # 尝试带 model 的路径
    url = f'{BASE}/xinge/shop/model{model}/showit.asp?shop_id={sid}&id={pid}'
    html = fetch(url)
    
    if not html or len(html) < 500:
        errors += 1
        results.append({'product_id': pid, 'shop_id': sid, 'status': 'failed'})
        time.sleep(0.5)
        continue
    
    # 检查是否有实质展品内容（空展品页只有导航没有数据）
    if '鸽' not in html and '环' not in html:
        results.append({'product_id': pid, 'shop_id': sid, 'status': 'empty'})
        time.sleep(0.5)
        continue
    
    product = extract_product(html, pid, sid)
    product['status'] = 'ok'
    results.append(product)
    
    time.sleep(0.8)

ok_count = len([r for r in results if r.get('status') == 'ok'])
print(f'\nDone! Success: {ok_count}, Failed: {errors}', file=sys.stderr)

# 统计
has_ring = len([r for r in results if r.get('ring_number')])
has_blood = len([r for r in results if r.get('bloodline')])
has_img = len([r for r in results if r.get('images')])
print(f'  有环号: {has_ring}, 有血统: {has_blood}, 有图片: {has_img}', file=sys.stderr)

out = f'{OUT_DIR}/products_detail.json'
with open(out, 'w', encoding='utf-8') as f:
    json.dump(results, f, ensure_ascii=False, indent=2)
print(f'💾 → {out}', file=sys.stderr)
