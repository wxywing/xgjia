#!/usr/bin/env python3
"""中信网分类信息爬虫 - 抓取交易信息
数据源: http://info.chinaxinge.com/htmltrade/YYYYMM/XXXXXX.html
入口: https://trade.chinaxinge.com/ 首页链接
"""
import json, re, urllib.request, time, sys, os, signal

SCRIPTS_DIR = '/Users/scentlibrart-007/.qclaw/workspace-agent-f8126f99/xgjia-website/scripts'
OUT = f'{SCRIPTS_DIR}/trade_listings.json'
MAX_LISTINGS = 100

def fetch(url, timeout=15):
    req = urllib.request.Request(url, headers={
        'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
        'Accept-Language': 'zh-CN,zh;q=0.9',
    })
    try:
        data = urllib.request.urlopen(req, timeout=timeout).read()
        if len(data) < 500: return None
        return data.decode('gbk', errors='replace')
    except: return None

def clean(text):
    text = re.sub(r'<[^>]+>', '', text)
    text = re.sub(r'&nbsp;', ' ', text)
    text = re.sub(r'&ensp;', ' ', text)
    text = re.sub(r'&amp;', '&', text)
    text = re.sub(r'&#\d+;', '', text)
    text = re.sub(r'\s+', ' ', text)
    return text.strip()

def get_listing_urls(html):
    """从首页提取分类信息链接"""
    # http://info.chinaxinge.com/htmltrade/YYYYMM/XXXXXX.html
    urls = re.findall(r'https?://info\.chinaxinge\.com/htmltrade/\d{6}/\d+\.html', html)
    return list(set(urls))

def extract_listing(html, url):
    """从详情页提取分类信息"""
    result = {'source_url': url, 'status': 'ok'}
    
    # 从URL提取ID
    id_m = re.search(r'/(\d+)\.html', url)
    if id_m:
        result['id'] = id_m.group(1)
    
    # 从URL提取月份
    month_m = re.search(r'/htmltrade/(\d{6})/', url)
    if month_m:
        ym = month_m.group(1)
        result['source_month'] = f'{ym[:4]}-{ym[4:]}'
    
    # 标题
    title_m = re.search(r'<title>(.*?)</title>', html, re.DOTALL)
    if title_m:
        title = clean(title_m.group(1))
        title = re.sub(r'\s*[-–—]\s*(?:中国信鸽信息网|中信网).*$', '', title)
        result['title'] = title.strip()[:200]
    
    # 日期
    date_m = re.search(r'(\d{4})[-/年](\d{1,2})[-/月](\d{1,2})', html)
    if date_m:
        result['published_at'] = f'{date_m.group(1)}-{int(date_m.group(2)):02d}-{int(date_m.group(3)):02d}'
    
    # 联系方式
    phone_m = re.search(r'(?:电话|手机|联系)[：:]\s*([0-9\-]{7,15})', html)
    if phone_m:
        result['contact_phone'] = phone_m.group(1)
    
    contact_m = re.search(r'联系人[：:]\s*([^\s<,，]{2,10})', html)
    if contact_m:
        result['contact_name'] = contact_m.group(1)
    
    # 地区
    area_m = re.search(r'(?:地区|所在地|地址)[：:]\s*([\u4e00-\u9fa5]{2,20})', html)
    if area_m:
        result['region'] = area_m.group(1).strip()
    
    # 价格
    price_m = re.search(r'(?:价格|售价|转让价)[：:]\s*([\d,.]+万?元?)', html)
    if price_m:
        result['price'] = price_m.group(1)
    
    # 正文
    texts = re.findall(r'>([^<]{20,})<', html)
    paragraphs = []
    for t in texts:
        t = clean(t)
        if len(t) > 15 and any('\u4e00' <= c <= '\u9fff' for c in t[:5]):
            if 'function' not in t.lower() and 'javascript' not in t.lower():
                if t not in ('中国信鸽信息网', '中信网'):
                    paragraphs.append(t)
    
    if paragraphs:
        result['content'] = '\n\n'.join(paragraphs[:20])
    
    # 分类（从页面中找分类标签）
    cat_m = re.search(r'所属分类[：:]\s*([^<\s]{2,20})', html)
    if cat_m:
        result['category'] = cat_m.group(1).strip()
    else:
        # 根据标题推断
        title = result.get('title', '')
        if '出售' in title or '转让' in title:
            result['category'] = '出售'
        elif '求购' in title:
            result['category'] = '求购'
        elif '招聘' in title:
            result['category'] = '招聘'
        elif '配对' in title:
            result['category'] = '配对'
    
    if not result.get('title') and not result.get('content'):
        result['status'] = 'no_data'
    
    return result

# 断点续爬
results = []
done_urls = set()
if os.path.exists(OUT):
    with open(OUT, encoding='utf-8') as f:
        results = json.load(f)
    done_urls = {r['source_url'] for r in results}
    print(f'Resuming from {len(done_urls)} done', file=sys.stderr)

def save():
    with open(OUT, 'w', encoding='utf-8') as f:
        json.dump(results, f, ensure_ascii=False, indent=2)

signal.signal(signal.SIGTERM, lambda s,f: (save(), sys.exit(1)))
signal.signal(signal.SIGINT, lambda s,f: (save(), sys.exit(1)))

# 1. 从首页获取链接
print('Fetching trade homepage...', file=sys.stderr)
home_html = fetch('https://trade.chinaxinge.com/')
if not home_html:
    print('Failed to fetch homepage', file=sys.stderr)
    sys.exit(1)

listing_urls = get_listing_urls(home_html)
print(f'Found {len(listing_urls)} listing URLs', file=sys.stderr)

# 2. 如果首页链接不够，尝试分页
# trade.chinaxinge.com 可能有分页，先不搞，100条够了

# 3. 逐个抓取
count = 0
for url in listing_urls:
    if url in done_urls:
        continue
    if len(results) >= MAX_LISTINGS:
        break
    
    html = fetch(url)
    if html:
        listing = extract_listing(html, url)
        results.append(listing)
        count += 1
        
        title_short = listing.get('title', '?')[:20]
        print(f'[{len(results)}/{MAX_LISTINGS}] {title_short}', file=sys.stderr)
    else:
        results.append({'source_url': url, 'status': 'failed'})
    
    time.sleep(0.5)
    if count % 20 == 0:
        save()

save()
ok = len([r for r in results if r.get('status') == 'ok'])
print(f'\nDone! ok={ok}/{len(results)}', file=sys.stderr)