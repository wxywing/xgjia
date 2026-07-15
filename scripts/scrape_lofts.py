#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
中信网公棚数据爬虫 v1.0
目标：抓取 gdgp.chinaxinge.com 所有公棚数据，输出 JSON + SQL
"""
import requests
import time
import json
import re
import sys
from urllib.parse import urljoin, urlparse, parse_qs
from html.parser import HTMLParser

# ── 配置 ──────────────────────────────────────────────
BASE_URL = 'https://gdgp.chinaxinge.com'
LIST_URL = f'{BASE_URL}/gplist.asp'
DELAY = 1.5          # 礼貌延迟（秒）
MAX_RETRIES = 3
OUTPUT_JSON = 'lofts_raw.json'
OUTPUT_SQL = 'lofts_import.sql'

HEADERS = {
    'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
    'Accept-Language': 'zh-CN,zh;q=0.9,en;q=0.8',
    'Referer': BASE_URL,
}

session = requests.Session()
session.headers.update(HEADERS)


def fetch(url, encoding='gbk'):
    """获取页面，自动处理 GBK 编码"""
    for attempt in range(MAX_RETRIES):
        try:
            resp = session.get(url, timeout=30, verify=False)
            resp.encoding = encoding
            return resp.text
        except Exception as e:
            print(f'  ⚠️ 请求失败 (尝试 {attempt+1}/{MAX_RETRIES}): {e}')
            time.sleep(DELAY * 2)
    return None


def extract_loft_ids_from_list(html):
    """从公棚列表页提取所有 gp_id 和名称"""
    ids = []
    # 匹配 href 中包含 gp_id=XXX 的链接
    pattern = re.compile(r"gp_id=(\d+)")
    # 提取带名称的链接
    link_pattern = re.compile(
        r'<a[^>]+href="[^"]*gp_id=(\d+)[^"]*"[^>]*>(.*?)</a>',
        re.DOTALL
    )
    
    seen = set()
    for match in link_pattern.finditer(html):
        gp_id = match.group(1)
        raw_name = match.group(2)
        # 清理 HTML 标签
        name = re.sub(r'<[^>]+>', '', raw_name).strip()
        # 去重 + 过滤无效
        if gp_id not in seen and name and len(name) > 2 and '公棚' in html[max(0, match.start()-200):match.start()]:
            seen.add(gp_id)
            ids.append({'gp_id': gp_id, 'name': name})
    
    # 如果正则没匹配够，用简单模式兜底
    if not ids:
        for match in pattern.finditer(html):
            gp_id = match.group(1)
            if gp_id not in seen:
                seen.add(gp_id)
                ids.append({'gp_id': gp_id, 'name': ''})
    
    return ids


def extract_field(text, *labels):
    """从文本中提取标签后的值"""
    for label in labels:
        # 尝试 "标签：值" 或 "标签:值"
        for sep in ['：', ':', '：', ':']:
            pattern = re.compile(rf'{label}\s*{sep}\s*([^\n\r<\s,，。]+)', re.IGNORECASE)
            m = pattern.search(text)
            if m:
                return m.group(1).strip()
        # 尝试 "标签 值"（无分隔符）
        pattern = re.compile(rf'{label}\s*[：:：:]*\s*([^\n\r<\s,，]{2,30})', re.IGNORECASE)
        m = pattern.search(text)
        if m:
            return m.group(1).strip()
    return ''


def extract_loft_detail(html, gp_id, list_name=''):
    """从公棚详情页提取结构化数据"""
    # 提取 title
    title_match = re.search(r'<title>(.*?)</title>', html, re.DOTALL)
    title = title_match.group(1).strip() if title_match else ''
    
    # 去掉 HTML 标签获取纯文本
    text = re.sub(r'<script[^>]*>.*?</script>', '', html, flags=re.DOTALL)
    text = re.sub(r'<style[^>]*>.*?</style>', '', text, flags=re.DOTALL)
    text = re.sub(r'<[^>]+>', '\n', text)
    text = re.sub(r'\n{3,}', '\n\n', text)
    
    # 提取省份（从列表页 URL 参数或页面文本）
    province = extract_field(text, '所在省市', '省', '地址')
    
    # 提取联系信息
    contact_name = extract_field(text, '负责人', '联系人', '场长')
    phone = extract_field(text, '电话', '联系电话')
    mobile = extract_field(text, '手机', '手机号')
    
    # 提取公棚类型（春棚/秋棚）
    race_type = ''
    if '春棚' in text:
        race_type = '春棚'
    elif '秋棚' in text:
        race_type = '秋棚'
    if '春秋棚' in text or '春、秋棚' in text:
        race_type = '春秋棚'
    
    # 提取奖金
    prize_pool = extract_field(text, '总奖金', '奖金总额', '奖金')
    
    # 提取收鸽费用
    entry_fee = extract_field(text, '参赛费', '参赛费/羽', '每羽')
    management_fee = extract_field(text, '饲养费', '管理费')
    
    # 提取收鸽时间
    collect_start = extract_field(text, '收鸽开始', '收鸽时间')
    collect_end = ''
    
    # 提取比赛距离
    race_distance = extract_field(text, '决赛距离', '比赛距离', '决赛公里')
    
    # 提取地址
    address = extract_field(text, '详细地址', '通讯地址', '地址')
    if address and province and province in address:
        address = address.replace(province, '').strip()
    
    # 提取简介
    desc_match = re.search(r'(公棚简介|简介)[：:]*\s*\n?\s*(.*?)(?:\n\n|\n.{0,5}[：:])', text, re.DOTALL)
    description = desc_match.group(2).strip()[:500] if desc_match else ''
    
    # 确定 name
    name = list_name if list_name else title.split('-')[0].split('_')[0].strip()
    if not name or len(name) < 3:
        name = title.strip()
    
    return {
        'gp_id': gp_id,
        'name': name[:100],
        'province': province[:30],
        'city': '',
        'address': address[:255],
        'contact_name': contact_name[:50],
        'contact_phone': phone[:20] or mobile[:20],
        'description': description[:2000],
        'race_type': race_type[:50],
        'prize_pool': prize_pool,
        'entry_fee': entry_fee,
        'management_fee': management_fee,
        'race_distance': race_distance,
        'source_url': f'{BASE_URL}/default2.asp?gp_id={gp_id}',
        'status': 'draft',
    }


def get_list_pages(html):
    """检测列表页是否有分页，返回总页数"""
    # 检查是否存在分页链接
    page_match = re.search(r'page=(\d+).*?>(\d+)</a>', html)
    if page_match:
        # 找最大页码
        pages = re.findall(r'page=(\d+)', html)
        if pages:
            return max(int(p) for p in pages)
    # 检查 "共X页" 
    total_match = re.search(r'共\s*(\d+)\s*页', html)
    if total_match:
        return int(total_match.group(1))
    return 1


def scrape_all():
    """主爬取流程"""
    print(f'🚀 开始爬取中信网公棚数据...')
    print(f'📡 列表页: {LIST_URL}')
    
    # 1. 获取列表页
    print(f'\n📄 获取公棚列表...')
    list_html = fetch(LIST_URL)
    if not list_html:
        print('❌ 无法获取列表页，退出')
        return []
    
    # 提取所有 gp_id
    all_entries = extract_loft_ids_from_list(list_html)
    print(f'   首页找到 {len(all_entries)} 个公棚链接')
    
    # 检查分页
    total_pages = get_list_pages(list_html)
    print(f'   总页数: {total_pages}')
    
    for page in range(2, total_pages + 1):
        page_url = f'{LIST_URL}?sy=&area=&cid=&kwd=&page={page}'
        print(f'   📄 第 {page}/{total_pages} 页...')
        page_html = fetch(page_url)
        if page_html:
            entries = extract_loft_ids_from_list(page_html)
            all_entries.extend(entries)
            print(f'      +{len(entries)} 个公棚')
        time.sleep(DELAY)
    
    # 去重
    seen = set()
    unique = []
    for e in all_entries:
        if e['gp_id'] not in seen:
            seen.add(e['gp_id'])
            unique.append(e)
    all_entries = unique
    print(f'\n✅ 去重后共 {len(all_entries)} 个公棚')
    
    # 2. 逐个抓取详情
    results = []
    failed = []
    
    for i, entry in enumerate(all_entries, 1):
        gp_id = entry['gp_id']
        name = entry['name']
        print(f'\n[{i}/{len(all_entries)}] 爬取: {name or gp_id}')
        
        # 尝试不同模板
        detail_html = None
        for mo in range(1, 10):
            url = f'{BASE_URL}/default2.asp?gp_id={gp_id}'
            html = fetch(url)
            if html and len(html) > 500 and '公棚' in html:
                detail_html = html
                break
            time.sleep(0.3)
        
        if not detail_html:
            print(f'  ❌ 无法获取详情页')
            failed.append(entry)
            results.append({
                'gp_id': gp_id,
                'name': name,
                'status': 'failed',
            })
            time.sleep(DELAY)
            continue
        
        # 解析详情
        loft = extract_loft_detail(detail_html, gp_id, name)
        loft['status'] = 'ok'
        results.append(loft)
        
        print(f'  ✅ {loft["name"]}')
        if loft['province']:
            print(f'     📍 {loft["province"]}')
        if loft['contact_phone']:
            print(f'     📞 {loft["contact_phone"]}')
        
        time.sleep(DELAY)
    
    print(f'\n{"="*50}')
    print(f'🎉 爬取完成！')
    print(f'   成功: {len([r for r in results if r.get("status") == "ok"])}')
    print(f'   失败: {len(failed)}')
    
    return results


def save_json(lofts, filename=OUTPUT_JSON):
    """保存为 JSON"""
    with open(filename, 'w', encoding='utf-8') as f:
        json.dump(lofts, f, ensure_ascii=False, indent=2)
    print(f'💾 JSON → {filename} ({len(lofts)} 条)')


def generate_sql(lofts, filename=OUTPUT_SQL):
    """生成 SQL INSERT 语句（导入到 lofts 表）"""
    lines = [
        '-- 中信网公棚数据导入脚本',
        '-- 生成时间: ' + time.strftime('%Y-%m-%d %H:%M:%S'),
        '-- 数据来源: gdgp.chinaxinge.com',
        '-- ⚠️ user_id 设为 0（待商家认领）',
        '',
    ]
    
    for loft in lofts:
        if loft.get('status') != 'ok':
            continue
        
        # 字段映射到 lofts 表
        fields = {
            'user_id': '0',
            'name': escape_sql(loft.get('name', '')),
            'province': escape_sql(loft.get('province', '')),
            'city': escape_sql(loft.get('city', '')),
            'address': escape_sql(loft.get('address', '')),
            'contact_name': escape_sql(loft.get('contact_name', '')),
            'contact_phone': escape_sql(loft.get('contact_phone', '')),
            'description': escape_sql(loft.get('description', '')),
            'race_type': escape_sql(loft.get('race_type', '')),
            'status': '0',  # 待审核
        }
        
        cols = ', '.join(f'`{k}`' for k in fields)
        vals = ', '.join(fields.values())
        lines.append(f'INSERT INTO `lofts` ({cols}) VALUES ({vals});')
    
    sql = '\n'.join(lines)
    with open(filename, 'w', encoding='utf-8') as f:
        f.write(sql)
    print(f'💾 SQL → {filename}')


def escape_sql(val):
    """SQL 值转义"""
    if val is None or val == '':
        return 'NULL'
    return f"'{str(val).replace(chr(39), chr(39)+chr(39))}'"


if __name__ == '__main__':
    import urllib3
    urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)
    
    lofts = scrape_all()
    if lofts:
        save_json(lofts)
        generate_sql(lofts)
    else:
        print('❌ 未获取到任何数据')
        sys.exit(1)
