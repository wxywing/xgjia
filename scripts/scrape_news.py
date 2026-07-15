#!/usr/bin/env python3
"""中信网资讯爬虫 - 抓取新闻文章
数据源: https://news.chinaxinge.com/
页面: 列表页 → shownews.asp?id=XXX
"""
import json, re, urllib.request, time, sys, os, signal

BASE = 'https://news.chinaxinge.com'
SCRIPTS_DIR = '/Users/scentlibrart-007/.qclaw/workspace-agent-f8126f99/xgjia-website/scripts'
OUT = f'{SCRIPTS_DIR}/news_articles.json'
MAX_ARTICLES = 100  # 抓取数量

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
    text = re.sub(r'&emsp;', ' ', text)
    text = re.sub(r'&amp;', '&', text)
    text = re.sub(r'&lt;', '<', text)
    text = re.sub(r'&gt;', '>', text)
    text = re.sub(r'&quot;', '"', text)
    text = re.sub(r'&#\d+;', '', text)
    text = re.sub(r'\s+', ' ', text)
    return text.strip()

def get_article_list(html):
    """从列表页提取文章ID和基本信息"""
    articles = []
    # 找 shownews.asp?id=XXXX 链接
    links = re.findall(r'href=["\']shownews\.asp\?id=(\d+)[^"\']*["\']', html)
    seen = set()
    for aid in links:
        if aid not in seen:
            seen.add(aid)
            articles.append(aid)
    return articles

def extract_article(html, article_id):
    """从详情页提取文章内容"""
    result = {'id': article_id, 'status': 'ok'}
    
    # 标题
    title_m = re.search(r'<title>(.*?)</title>', html, re.DOTALL)
    if title_m:
        title = clean(title_m.group(1))
        # 去掉 " - 中信网" 等后缀
        title = re.sub(r'\s*[-–—]\s*(?:中信网|中国信鸽信息网).*$', '', title)
        result['title'] = title.strip()[:200]
    
    # 日期
    date_m = re.search(r'(\d{4})[-/年](\d{1,2})[-/月](\d{1,2})[日]?', html)
    if date_m:
        result['published_at'] = f'{date_m.group(1)}-{int(date_m.group(2)):02d}-{int(date_m.group(3)):02d}'
    
    # 来源
    src_m = re.search(r'来源[：:]\s*([^\s<]{2,20})', html)
    if src_m:
        result['source'] = src_m.group(1).strip()
    
    # 作者
    author_m = re.search(r'作者[：:]\s*([^\s<]{2,20})', html)
    if author_m:
        result['author'] = author_m.group(1).strip()
    
    # 分类
    cat_m = re.search(r'所属栏目[：:]\s*([^<\s]{2,20})', html)
    if cat_m:
        result['category'] = cat_m.group(1).strip()
    
    # 正文内容 - 中信网新闻常见格式：<br> 分段
    # 找标题后的内容区域
    title_text = result.get('title', '')
    if title_text:
        pos = html.find(title_text[:10])
        if pos > 0:
            content_area = html[pos:pos+30000]
        else:
            content_area = html
    else:
        content_area = html
    
    # 按 <br> 分段，提取含中文的段落
    segments = re.split(r'<br\s*/?>', content_area)
    paragraphs = []
    for seg in segments:
        text = clean(seg)
        if len(text) > 15 and any('\u4e00' <= c <= '\u9fff' for c in text[:5]):
            # 排除导航、页脚等
            if text in ('中国.深圳', '中信网', '首页', '公棚', '协会'):
                continue
            if 'function' in text.lower() or 'javascript' in text.lower():
                continue
            if len(text) > 2000:
                text = text[:2000]
            paragraphs.append(text)
    
    if paragraphs:
        result['content'] = '\n\n'.join(paragraphs)
    else:
        # 备用：找所有长中文文本
        all_texts = re.findall(r'>([^<]{30,})<', html)
        for t in all_texts:
            t = clean(t)
            if len(t) > 20 and any('\u4e00' <= c <= '\u9fff' for c in t[:5]):
                if 'function' not in t.lower():
                    paragraphs.append(t)
        result['content'] = '\n\n'.join(paragraphs[:20]) if paragraphs else ''
    
    # 如果没有标题和内容，标记失败
    if not result.get('title') and not result.get('content'):
        result['status'] = 'no_data'
    
    return result

# 断点续爬
results = []
done_ids = set()
if os.path.exists(OUT):
    with open(OUT, encoding='utf-8') as f:
        results = json.load(f)
    done_ids = {r['id'] for r in results}
    print(f'Resuming from {len(done_ids)} done', file=sys.stderr)

def save():
    with open(OUT, 'w', encoding='utf-8') as f:
        json.dump(results, f, ensure_ascii=False, indent=2)

signal.signal(signal.SIGTERM, lambda s,f: (save(), sys.exit(1)))
signal.signal(signal.SIGINT, lambda s,f: (save(), sys.exit(1)))

# 1. 获取列表页，收集文章ID
print('Fetching article list...', file=sys.stderr)
list_html = fetch(f'{BASE}/')
if not list_html:
    print('Failed to fetch list page', file=sys.stderr)
    sys.exit(1)

article_ids = get_article_list(list_html)
print(f'Found {len(article_ids)} article IDs', file=sys.stderr)

# 2. 逐个抓取文章
count = 0
for aid in article_ids:
    if aid in done_ids:
        continue
    if len(results) >= MAX_ARTICLES:
        break
    
    url = f'{BASE}/shownews.asp?id={aid}'
    # 加上 sjm 参数（某些文章需要）
    html = fetch(url)
    if not html:
        # 尝试从列表页提取 sjm
        sjm_m = re.search(rf'shownews\.asp\?id={aid}[^"\']*sjm=([a-f0-9]+)', list_html)
        if sjm_m:
            html = fetch(f'{BASE}/shownews.asp?id={aid}&sjm={sjm_m.group(1)}')
    
    if html:
        article = extract_article(html, aid)
        results.append(article)
        count += 1
        
        title_short = article.get('title', '?')[:20]
        content_len = len(article.get('content', ''))
        print(f'[{len(results)}/{MAX_ARTICLES}] id={aid}: {title_short} ({content_len} chars)', file=sys.stderr)
    else:
        results.append({'id': aid, 'status': 'failed'})
        print(f'[{len(results)}/{MAX_ARTICLES}] id={aid}: FAILED', file=sys.stderr)
    
    time.sleep(0.5)
    if count % 20 == 0:
        save()

save()
ok = len([r for r in results if r.get('status') == 'ok'])
print(f'\nDone! ok={ok}/{len(results)}', file=sys.stderr)