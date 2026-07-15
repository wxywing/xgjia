#!/usr/bin/env python3
"""补充抓取 about.asp（容量/简介/届数/比赛类型）- v2修复版
修复：横向表格解析（标签行+值行按列对齐），多模板支持，改进简介提取
"""
import json, re, urllib.request, time, sys, os, signal
from concurrent.futures import ThreadPoolExecutor, as_completed

BASE = 'https://gdgp.chinaxinge.com'
SCRIPTS_DIR = '/Users/scentlibrart-007/.qclaw/workspace-agent-f8126f99/xgjia-website/scripts'
LOFTS_LIST = f'{SCRIPTS_DIR}/lofts_list.json'
OUT = f'{SCRIPTS_DIR}/lofts_about_batch_v2.json'

with open(LOFTS_LIST, encoding='utf-8') as f:
    lofts = json.load(f)
lofts_sorted = sorted(lofts, key=lambda l: int(l['gp_id']))

def fetch(url, timeout=15):
    req = urllib.request.Request(url, headers={
        'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
        'Accept-Language': 'zh-CN,zh;q=0.9',
    })
    try:
        data = urllib.request.urlopen(req, timeout=timeout).read()
        if len(data) < 500:
            return None
        return data.decode('gbk', errors='replace')
    except Exception:
        return None

def extract_horizontal_table(html):
    """解析横向表格：标签在一行，值在下一行，按列对齐"""
    # 找到包含关键字的表格
    for anchor in ['举办比赛性质', '可容量羽数', '容量', '举办届数']:
        pos = html.find(anchor)
        if pos < 0:
            continue
        # 向前找 <table
        table_start = html.rfind('<table', 0, pos)
        if table_start < 0:
            continue
        table_end = html.find('</table>', pos)
        if table_end < 0:
            continue
        table_html = html[table_start:table_end+8]
        
        # 提取每行的 cells
        rows = re.findall(r'<tr[^>]*>(.*?)</tr>', table_html, re.DOTALL)
        row_cells = []
        for row in rows:
            cells = re.findall(r'<td[^>]*>(.*?)</td>', row, re.DOTALL)
            texts = []
            for c in cells:
                t = re.sub(r'<[^>]+>', '', c).strip()
                t = re.sub(r'&nbsp;', ' ', t)
                t = re.sub(r'\s+', ' ', t).strip()
                texts.append(t)
            row_cells.append(texts)
        
        if len(row_cells) >= 2:
            # 横向模式：行0=标签，行1=值
            labels = row_cells[0]
            values = row_cells[1]
            result = {}
            # labels 和 values 按列对齐
            for i in range(min(len(labels), len(values))):
                label = labels[i].strip()
                value = values[i].strip()
                if value and value not in ('查看', '查看监控', '查看地图', ''):
                    result[label] = value
            # 也可能有更多行（纵向模式补充）
            for row_idx in range(2, len(row_cells)):
                cells = row_cells[row_idx]
                for i in range(0, len(cells)-1, 2):
                    label = cells[i].strip()
                    value = cells[i+1].strip() if i+1 < len(cells) else ''
                    if label and value and value not in ('查看', ''):
                        result[label] = value
            return result
    return {}

def extract_description(html):
    """提取公棚简介，多种模式"""
    # 模式1: "本公棚成立..." 开头
    m = re.search(r'本公棚[^\n<]{10,500}', html, re.DOTALL)
    if m:
        text = re.sub(r'<[^>]+>', ' ', m.group())
        text = re.sub(r'\s+', ' ', text).strip()
        if len(text) > 20:
            return text[:2000]
    
    # 模式2: 简介段落（td 中大段文字）
    tds = re.findall(r'<td[^>]*>(.*?)</td>', html, re.DOTALL)
    for td in tds:
        text = re.sub(r'<[^>]+>', '', td).strip()
        text = re.sub(r'&nbsp;', ' ', text)
        text = re.sub(r'\s+', ' ', text).strip()
        if len(text) > 50 and ('公棚' in text or '赛鸽' in text) and 'javascript' not in text.lower():
            # 排除导航项
            if text not in ('中信网', '各地公棚', '首页'):
                return text[:2000]
    
    # 模式3: <p> 标签中的描述
    ps = re.findall(r'<p[^>]*>(.*?)</p>', html, re.DOTALL)
    for p in ps:
        text = re.sub(r'<[^>]+>', '', p).strip()
        text = re.sub(r'\s+', ' ', text).strip()
        if len(text) > 50 and ('公棚' in text or '赛鸽' in text):
            return text[:2000]
    
    # 模式4: 任意长中文段落
    m = re.search(r'([\u4e00-\u9fa5]{50,2000})', html, re.DOTALL)
    if m:
        text = re.sub(r'<[^>]+>', ' ', m.group())
        text = re.sub(r'\s+', ' ', text).strip()
        # 排除导航/JS
        if 'javascript' not in text.lower() and 'function' not in text.lower():
            return text[:2000]
    
    return ''

def extract_about(html, gp_id, template='mo1'):
    """从 about.asp 提取数据"""
    if not html or len(html) < 1000:
        return {'gp_id': gp_id, 'status': 'failed'}

    result = {'gp_id': gp_id, 'status': 'ok',
              'capacity': '', 'limit': '', 'description': '',
              'race_type': '', 'edition': '', 'area': ''}

    # 解析表格
    table_data = extract_horizontal_table(html)
    
    # 映射到字段
    field_map = {
        '举办比赛性质': 'race_type',
        '可容量羽数': 'capacity',
        '限收羽数': 'limit',
        '举办届数': 'edition',
        '公棚面积(平方米)': 'area',
        '公棚面积': 'area',
    }
    for label, field in field_map.items():
        if label in table_data:
            val = table_data[label].strip()
            val = re.sub(r'\s+', '', val)
            if val and val not in ('查看', '查看监控', '查看地图'):
                result[field] = val[:30]
    
    # 如果横向表格没提取到，尝试纵向模式（标签+值紧邻）
    if not result['capacity']:
        cells = re.findall(r'<td[^>]*>(.*?)</td>', html, re.DOTALL)
        text_cells = [re.sub(r'<[^>]+>', '', c).strip() for c in cells]
        text_cells = [re.sub(r'&nbsp;', ' ', t) for t in text_cells]
        text_cells = [re.sub(r'\s+', ' ', t).strip() for t in text_cells]
        for i, cell in enumerate(text_cells):
            for label, field in field_map.items():
                if cell.strip() == label and i+1 < len(text_cells):
                    val = text_cells[i+1].strip()
                    if val and val not in ('查看', ''):
                        result[field] = re.sub(r'\s+', '', val)[:30]

    # 简介
    result['description'] = extract_description(html)
    
    # 检查是否有有效数据
    has_data = any(result[f] for f in ['capacity', 'limit', 'race_type', 'edition', 'description'])
    if not has_data:
        result['status'] = 'no_data'
    
    return result

# 断点续爬
if os.path.exists(OUT):
    with open(OUT, encoding='utf-8') as f:
        batch_results = json.load(f)
    done_ids = {r['gp_id'] for r in batch_results}
    print(f'Resuming from {len(done_ids)} done', file=sys.stderr)
else:
    batch_results = []
    done_ids = set()

signal.signal(signal.SIGTERM, lambda s,f: (save(), sys.exit(1)))
signal.signal(signal.SIGINT, lambda s,f: (save(), sys.exit(1)))

def save():
    with open(OUT, 'w', encoding='utf-8') as f:
        json.dump(batch_results, f, ensure_ascii=False, indent=2)

def process(loft):
    gp_id = loft['gp_id']
    name = loft['name']
    if gp_id in done_ids:
        return None
    
    # 尝试多个模板
    for template in ['mo1', 'mo2', 'mo3', 'mo4', 'mo5', 'mo6', 'mo7', 'mo8', 'mo9', 'mo10',
                     'mo11', 'mo12', 'mo13', 'mo14', 'mo15', 'mo16', 'mo17', 'mo18', 'mo19',
                     'mo20', 'mo21', 'mo22', 'mo23', 'mo24', 'mo25', 'mo26', 'mo27', 'mo28',
                     'mo29', 'mo30', 'mo31', 'mo32', 'mo33', 'mo34', 'mo35', 'mo36', 'mo37', 'mo38']:
        url = f'{BASE}/style/{template}/about.asp?gp_id={gp_id}'
        html = fetch(url)
        if html:
            detail = extract_about(html, gp_id, template)
            if detail['status'] in ('ok', 'no_data'):
                detail['template'] = template
                ok_parts = []
                for f in ['capacity', 'race_type', 'edition', 'description']:
                    v = detail.get(f, '')
                    ok_parts.append(f'{f}={v[:20] if v else "-"}')
                print(f'[{template}/{gp_id}] {name[:15]}: {" ".join(ok_parts)}', file=sys.stderr)
                time.sleep(0.3)
                return detail
        time.sleep(0.1)
    
    # 所有模板都不行
    detail = {'gp_id': gp_id, 'status': 'all_failed'}
    print(f'[??/{gp_id}] FAILED all templates', file=sys.stderr)
    time.sleep(0.5)
    return detail

count = 0
with ThreadPoolExecutor(max_workers=5) as executor:
    futures = {executor.submit(process, l): l for l in lofts_sorted if l['gp_id'] not in done_ids}
    for fut in as_completed(futures):
        r = fut.result()
        if r:
            batch_results.append(r)
            count += 1
            if count % 50 == 0:
                save()

save()
ok = len([r for r in batch_results if r.get('status') == 'ok'])
no_data = len([r for r in batch_results if r.get('status') == 'no_data'])
print(f'\nDone! ok={ok}, no_data={no_data}, total={len(batch_results)}', file=sys.stderr)