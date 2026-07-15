#!/usr/bin/env python3
"""中信网鸽友圈动态爬虫 - 抓取协会/公棚动态
数据源: http://gdxh.chinaxinge.com/detail.asp?id=XXX
入口: https://trade.chinaxinge.com/ 首页链接
"""
import json, re, urllib.request, time, sys, os, signal

SCRIPTS_DIR = '/Users/scentlibrart-007/.qclaw/workspace-agent-f8126f99/xgjia-website/scripts'
OUT = f'{SCRIPTS_DIR}/dynamics_gdxh.json'
MAX_DYNAMICS = 100

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

def get_dynamic_urls(html):
    """从首页提取动态链接（保留 sjm 参数）"""
    urls = re.findall(r'https?://gdxh\.chinaxinge\.com/detail\.asp\?id=\d+&sjm=[a-f0-9]+', html)
    return list(set(urls))

def extract_dynamic(html, url):
    """从详情页提取动态"""
    result = {'source_url': url, 'status': 'ok'}
    
    # ID
    id_m = re.search(r'id=(\d+)', url)
    if id_m:
        result['id'] = id_m.group(1)
    
    # 标题
    title_m = re.search(r'<title>(.*?)</title>', html, re.DOTALL)
    if title_m:
        title = clean(title_m.group(1))
        title = re.sub(r'\s*[-–—]\s*(?:中信网|中国信鸽信息网|各地信鸽协会).*$', '', title)
        result['title'] = title.strip()[:200]
    
    # 日期
    date_m = re.search(r'(\d{4})[-/年](\d{1,2})[-/月](\d{1,2})', html)
    if date_m:
        result['published_at'] = f'{date_m.group(1)}-{int(date_m.group(2)):02d}-{int(date_m.group(3)):02d}'
    
    # 来源/协会
    # 标题中通常包含 " - XX市信鸽协会"
    src_m = re.search(r'[-–—]\s*([\u4e00-\u9fa5]+(?:信鸽协会|鸽会|赛鸽俱乐部))', html)
    if src_m:
        result['source_org'] = src_m.group(1).strip()
    
    # 正文
    texts = re.findall(r'>([^<]{20,})<', html)
    paragraphs = []
    for t in texts:
        t = clean(t)
        if len(t) > 15 and any('\u4e00' <= c <= '\u9fff' for c in t[:5]):
            if 'function' not in t.lower() and 'javascript' not in t.lower():
                if t not in ('中信网', '各地信鸽协会', '中国信鸽信息网'):
                    paragraphs.append(t)
    
    if paragraphs:
        result['content'] = '\n\n'.join(paragraphs[:20])
    
    if not result.get('title') and not result.get('content'):
        result['status'] = 'no_data'
    
    return result

# 断点续爬
results = []
done_ids = set()
if os.path.exists(OUT):
    with open(OUT, encoding='utf-8') as f:
        results = json.load(f)
    done_ids = {r.get('id') for r in results}
    print(f'Resuming from {len(done_ids)} done', file=sys.stderr)

def save():
    with open(OUT, 'w', encoding='utf-8') as f:
        json.dump(results, f, ensure_ascii=False, indent=2)

signal.signal(signal.SIGTERM, lambda s,f: (save(), sys.exit(1)))
signal.signal(signal.SIGINT, lambda s,f: (save(), sys.exit(1)))

# 1. 从首页获取动态链接
print('Fetching homepage for dynamic links...', file=sys.stderr)
home_html = fetch('https://trade.chinaxinge.com/')
if not home_html:
    print('Failed to fetch homepage', file=sys.stderr)
    sys.exit(1)

dynamic_urls = get_dynamic_urls(home_html)
print(f'Found {len(dynamic_urls)} dynamic URLs', file=sys.stderr)

# 也从 news.chinaxinge.com 首页找更多动态
news_html = fetch('https://news.chinaxinge.com/')
if news_html:
    news_dyn_urls = get_dynamic_urls(news_html)
    dynamic_urls.extend(news_dyn_urls)
    dynamic_urls = list(set(dynamic_urls))
    print(f'Total: {len(dynamic_urls)} dynamic URLs after adding news page', file=sys.stderr)

# 2. 逐个抓取
count = 0
for url in dynamic_urls:
    url_id = re.search(r'id=(\d+)', url)
    if url_id and url_id.group(1) in done_ids:
        continue
    if len(results) >= MAX_DYNAMICS:
        break
    
    html = fetch(url)
    
    if html:
        dynamic = extract_dynamic(html, url)
        results.append(dynamic)
        count += 1
        
        title_short = dynamic.get('title', '?')[:20]
        org = dynamic.get('source_org', '')
        print(f'[{len(results)}/{MAX_DYNAMICS}] {title_short} [{org}]', file=sys.stderr)
    else:
        results.append({'source_url': url, 'status': 'failed'})
    
    time.sleep(0.5)
    if count % 20 == 0:
        save()

save()
ok = len([r for r in results if r.get('status') == 'ok'])
print(f'\nDone! ok={ok}/{len(results)}', file=sys.stderr)