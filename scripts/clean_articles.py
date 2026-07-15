#!/usr/bin/env python3
"""
清洗爬虫抓取的中信网文章数据 v4 - 最终版
"""

import json
import re
import os

INPUT_FILE = os.path.join(os.path.dirname(__file__), 'news_articles.json')
OUTPUT_FILE = os.path.join(os.path.dirname(__file__), 'news_articles_clean.json')


def clean_html_entities(text):
    """清理HTML实体"""
    replacements = {
        '&middot;': '·', '&nbsp;': ' ', '&amp;': '&',
        '&lt;': '<', '&gt;': '>', '&quot;': '"', '&#39;': "'",
        '&ldquo;': '"', '&rdquo;': '"', '&hellip;': '…',
        '&mdash;': '—', '&ndash;': '–', '&times;': '×',
    }
    for old, new in replacements.items():
        text = text.replace(old, new)
    text = re.sub(r'&#(\d+);', lambda m: chr(int(m.group(1))), text)
    return text


def extract_images(text):
    """从内容中提取图片URL"""
    images = re.findall(r'(https?://[^\s"\'<>]+?\.(?:jpg|jpeg|png|gif|webp))', text, re.IGNORECASE)
    seen = set()
    result = []
    for img in images:
        if img in seen:
            continue
        seen.add(img)
        if any(skip in img.lower() for skip in ['icon', 'logo', 'btn', 'arrow', 'bg.']):
            continue
        result.append(img)
    return result


def find_content_line(lines):
    """找到包含正文内容的那一行（含日期标记的长行）"""
    for line in lines:
        if re.search(r'\d{4}/\d{1,2}/\d{1,2}\s+\d{1,2}:\d{2}', line) and len(line) > 200:
            return line
    for line in lines:
        if '中信网 在线铭鸽展售' in line or '中信网 各地鸽舍' in line:
            return line
    for line in lines:
        if len(line) > 200 and not line.startswith('发布时间') and '相关文章' not in line[:10]:
            return line
    return ''


def skip_incomplete_css(text):
    """跳过开头的不完整CSS规则，如 .to-top{width:50px;height:"""
    # 匹配 .classname{ 可能不完整的CSS
    while True:
        m = re.match(r'\s*\.[\w-]+\{[^}]*$', text)
        if m:
            # 找到这个不完整CSS规则的结束（换行或下一个非CSS内容）
            end = m.end()
            text = text[end:].lstrip()
        else:
            break
    return text


def clean_news_article(raw_content, title):
    """清洗标准资讯类文章"""
    lines = raw_content.split('\n')
    content_line = find_content_line(lines)
    
    if not content_line:
        return ''
    
    # 找日期+作者标记后的正文
    start_patterns = [
        r'\d{4}/\d{1,2}/\d{1,2}\s+\d{1,2}:\d{2}:\d{2}\s+\S+?\s+\d+评\s*',
        r'\d{4}/\d{1,2}/\d{1,2}\s+\d{1,2}:\d{2}:\d{2}\s*',
    ]
    
    content = content_line
    for pattern in start_patterns:
        m = re.search(pattern, content)
        if m:
            content = content[m.end():]
            break
    else:
        marker = '中信网 在线铭鸽展售 鸽具饲料展售 '
        idx = content.find(marker)
        if idx >= 0:
            content = content[idx + len(marker):]
            for p in start_patterns:
                m2 = re.search(p, content)
                if m2:
                    content = content[m2.end():]
                    break
    
    # 去除尾部噪音
    end_markers = ['发布时间：', '下一篇', '相关文章', '相关专题', '发表评论']
    for marker in end_markers:
        idx = content.find(marker)
        if idx > 50:
            content = content[:idx]
            break
    
    # 清理HTML标签
    content = re.sub(r'<[^>]+>', '', content)
    content = re.sub(r'\s{2,}', ' ', content)
    content = content.strip()
    
    return content


def clean_geyouquan_article(raw_content, title):
    """清洗鸽友圈类文章"""
    content = raw_content
    
    # 找最后一个完整CSS规则的结束位置
    last_css_end = 0
    for m in re.finditer(r'\.[\w-]+\{[^}]*\}', content[:3000]):
        if m.end() > last_css_end:
            last_css_end = m.end()
    
    if last_css_end > 100:
        content = content[last_css_end:]
    
    # 跳过不完整的CSS规则
    content = skip_incomplete_css(content)
    content = content.lstrip()
    
    # 去除尾部噪音
    end_markers = ['点击添加表情', '更多评论', '发表评论', '相关动态']
    for marker in end_markers:
        idx = content.find(marker)
        if idx > 10:
            content = content[:idx]
            break
    
    # 清理HTML标签
    content = re.sub(r'<[^>]+>', '', content)
    content = re.sub(r'\s{2,}', ' ', content)
    content = content.strip()
    
    return content


def clean_geshe_article(raw_content, title):
    """清洗鸽舍类文章"""
    content = raw_content
    
    start_markers = ['一、', '一、', '（一）', '您是什么时候', '您从什么', '我养鸽']
    start_pos = -1
    for marker in start_markers:
        idx = content.find(marker)
        if idx > 50:
            start_pos = idx
            break
    
    if start_pos > 0:
        content = content[start_pos:]
    
    end_markers = ['发布时间：', '下一篇', '相关文章', '发表评论', '相关专题']
    for marker in end_markers:
        idx = content.find(marker)
        if idx > 50:
            content = content[:idx]
            break
    
    content = re.sub(r'<[^>]+>', '', content)
    content = re.sub(r'\s{2,}', ' ', content)
    content = content.strip()
    
    return content


def clean_image_gallery_article(raw_content, title):
    """清洗图库类文章（含JS代码和缩略图）"""
    content = raw_content
    
    # 找 "作品说明" 或正文描述
    m = re.search(r'作品说明[：:]\s*', content)
    if m:
        content = content[m.end():]
    else:
        # 用普通资讯清洗逻辑
        return clean_news_article(raw_content, title)
    
    # 去除JS代码（840){this.width=... 等模式）
    content = re.sub(r'\d+\)\{this\.\w+[^}]*\}', '', content)
    content = re.sub(r"document\.\w+[^;]*;", '', content)
    content = re.sub(r"getElementById\([^)]+\)[^;]*;", '', content)
    content = re.sub(r"var\s+\w+\s*=\s*[^;]+;", '', content)
    
    # 去除页码显示
    content = re.sub(r'\d+/\d+\s+显示\s+\d+-\d+\s+共\s*\d+张', '', content)
    
    # 去除尾部噪音
    end_markers = ['发布时间：', '下一篇', '相关文章', '发表评论', '点击添加表情']
    for marker in end_markers:
        idx = content.find(marker)
        if idx > 10:
            content = content[:idx]
            break
    
    content = re.sub(r'<[^>]+>', '', content)
    content = re.sub(r'\s{2,}', ' ', content)
    content = content.strip()
    
    return content


def generate_summary(content, max_length=150):
    """从清洗后的内容生成摘要"""
    if not content:
        return ''
    if len(content) <= max_length:
        return content
    
    truncated = content[:max_length + 50]
    for i in range(min(len(truncated), max_length + 30), max_length - 20, -1):
        if i < len(truncated) and truncated[i] in '。！？；…':
            return truncated[:i + 1]
    
    return content[:max_length - 3] + '...'


def classify_article(title, content):
    """分类文章类型"""
    if '鸽友圈' in title:
        return '鸽友圈'
    if '.v_text' in content[:500] or '鸽友圈栏目' in content[:500]:
        return '鸽友圈'
    if '-甜茶' in title or ('中信网各地鸽舍' in content[:300]):
        return '鸽舍'
    # 图库文章特征
    if '作品说明' in content[:500] and ('840){this' in content or 'getElementById' in content):
        return '图库'
    return '资讯'


def clean_title(title):
    """清理标题"""
    t = re.sub(r'\s*[-–—]\s*(中信网|鸽友圈|中国信鸽信息网|甜茶鸽舍)\s*$', '', title).strip()
    if not t:
        t = title
    return t


def extract_author(raw_content):
    """从原始内容中提取作者名"""
    m = re.search(r'\d{4}/\d{1,2}/\d{1,2}\s+\d{1,2}:\d{2}:\d{2}\s+(\S+?)\s+\d+评', raw_content)
    if m:
        return m.group(1)
    return ''


def clean_field(value):
    """清理字段"""
    if not value:
        return ''
    value = clean_html_entities(value)
    value = re.sub(r'&\w+;', '', value)
    return value.strip()


def process_article(article):
    """处理单篇文章"""
    if article.get('status') != 'ok':
        return None
    
    title = article.get('title', '').strip()
    raw_content = article.get('content', '')
    
    if not title or not raw_content:
        return None
    
    raw_content = clean_html_entities(raw_content)
    
    images = extract_images(raw_content)
    cover = images[0] if images else ''
    
    author = extract_author(raw_content)
    
    article_type = classify_article(title, raw_content)
    
    if article_type == '鸽友圈':
        clean_content = clean_geyouquan_article(raw_content, title)
    elif article_type == '鸽舍':
        clean_content = clean_geshe_article(raw_content, title)
    elif article_type == '图库':
        clean_content = clean_image_gallery_article(raw_content, title)
    else:
        clean_content = clean_news_article(raw_content, title)
    
    if len(clean_content) < 30:
        return None
    
    summary = generate_summary(clean_content, 150)
    clean_title_text = clean_title(title)
    
    source = clean_field(article.get('source', '中信网')) or '中信网'
    author = clean_field(author) if author else clean_field(article.get('author', ''))
    
    published_at = article.get('published_at', article.get('date', ''))
    if published_at:
        published_at = published_at.strip()
    
    return {
        'original_id': article.get('id', ''),
        'title': clean_title_text,
        'summary': summary,
        'content': clean_content,
        'cover': cover,
        'source': source,
        'author': author,
        'published_at': published_at,
        'article_type': article_type,
    }


def main():
    with open(INPUT_FILE, 'r', encoding='utf-8') as f:
        articles = json.load(f)
    
    print(f'读取 {len(articles)} 条原始数据')
    
    cleaned = []
    skipped = 0
    
    for article in articles:
        result = process_article(article)
        if result:
            cleaned.append(result)
        else:
            skipped += 1
    
    print(f'清洗完成: {len(cleaned)} 条有效, {skipped} 条跳过')
    
    type_counts = {}
    for a in cleaned:
        t = a['article_type']
        type_counts[t] = type_counts.get(t, 0) + 1
    print('类型分布:')
    for t, c in type_counts.items():
        print(f'  {t}: {c}')
    
    cover_count = sum(1 for a in cleaned if a['cover'])
    print(f'有封面图: {cover_count}/{len(cleaned)}')
    
    with open(OUTPUT_FILE, 'w', encoding='utf-8') as f:
        json.dump(cleaned, f, ensure_ascii=False, indent=2)
    
    print(f'已保存至 {OUTPUT_FILE}')
    
    print('\n=== 清洗样例 ===')
    for a in cleaned[:5]:
        print(f'\n标题: {a["title"]}')
        print(f'摘要: {a["summary"][:120]}')
        print(f'正文前150字: {a["content"][:150]}...')
        print(f'来源: {a["source"]} | 作者: {a["author"]} | 类型: {a["article_type"]}')
        print(f'封面: {"有" if a["cover"] else "无"}')


if __name__ == '__main__':
    main()
