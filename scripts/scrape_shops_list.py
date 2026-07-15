#!/usr/bin/env python3
"""修正版：中信网铭鸽展厅列表爬虫 - 精确匹配 shop_id + 名称"""
import re, urllib.request, json, sys, time

BASE = 'https://www.chinaxinge.com'
LIST_URL = f'{BASE}/xinge/product/netshop.asp'
OUT = '/Users/scentlibrart-007/.qclaw/workspace-agent-f8126f99/shops_list.json'

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

def extract_shops(html):
    """从列表页提取展厅信息 - 匹配含 shop_id 和 <span> 名称的 <a> 标签"""
    shops = []
    seen = set()
    
    # 匹配模式: <a href='...shop_id=XXX...'><span...>名称</span></a>
    # 关键：shop_id 和名称在同一个 <a> 标签内
    pattern = re.compile(
        r"<a\s+href=['\"][^'\"]*shop_id=(\d+)[^'\"]*['\"][^>]*>\s*"
        r"<span\s+style=['\"][^'\"]*font-size:18px[^'\"]*['\"][^>]*>"
        r"([\u4e00-\u9fa5A-Za-z0-9·\-\s]+?)"
        r"</span>\s*</a>",
        re.DOTALL
    )
    
    # 头像匹配: shop_id 在 img 之前的 <a> 中
    avatar_pattern = re.compile(
        r"<a\s+href=['\"][^'\"]*shop_id=(\d+)[^'\"]*['\"][^>]*>\s*"
        r"<img\s+src=['\"]?(//pic\d+\.chinaxinge\.com/[^'\"\s>]+\.(?:jpg|jpeg|png|gif))['\"]?\s",
        re.DOTALL
    )
    
    # 先提取头像
    avatars = {}
    for m in avatar_pattern.finditer(html):
        sid = m.group(1)
        url = m.group(2)
        if url.startswith('//'):
            url = 'https:' + url
        avatars[sid] = url
    
    # 提取名称
    for m in pattern.finditer(html):
        sid = m.group(1)
        name = m.group(2).strip()
        if sid in seen or not name:
            continue
        seen.add(sid)
        shops.append({
            'shop_id': sid,
            'name': name,
            'avatar': avatars.get(sid, ''),
        })
    
    return shops

def get_total_pages(html):
    pages = re.findall(r'page=(\d+)', html)
    if pages:
        return max(int(p) for p in pages)
    return 1

# 主流程
print('🚀 开始爬取铭鸽展厅列表 (修正版)...', file=sys.stderr)

all_shops = []
seen_ids = set()

html = fetch(f'{LIST_URL}?page=1')
if not html:
    print('❌ 无法获取展厅列表页', file=sys.stderr)
    sys.exit(1)

total_pages = get_total_pages(html)
print(f'📄 总页数: {total_pages}', file=sys.stderr)

for page in range(1, total_pages + 1):
    print(f'📄 第 {page}/{total_pages} 页...', file=sys.stderr)
    
    if page > 1:
        html = fetch(f'{LIST_URL}?page={page}')
        if not html:
            print(f'  ❌ 第{page}页获取失败', file=sys.stderr)
            continue
        time.sleep(1.0)
    
    shops = extract_shops(html)
    new_count = 0
    for s in shops:
        if s['shop_id'] not in seen_ids:
            seen_ids.add(s['shop_id'])
            all_shops.append(s)
            new_count += 1
    
    print(f'  +{new_count} 展厅 (累计 {len(all_shops)})', file=sys.stderr)

print(f'\n✅ 共提取 {len(all_shops)} 个展厅', file=sys.stderr)
has_name = len([s for s in all_shops if s['name']])
has_avatar = len([s for s in all_shops if s['avatar']])
print(f'   有名称: {has_name}, 有头像: {has_avatar}', file=sys.stderr)

for s in all_shops[:5]:
    print(json.dumps(s, ensure_ascii=False), file=sys.stderr)

with open(OUT, 'w', encoding='utf-8') as f:
    json.dump(all_shops, f, ensure_ascii=False, indent=2)
print(f'💾 → {OUT}', file=sys.stderr)
