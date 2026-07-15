#!/usr/bin/env python3
"""补充抓取 about.asp（容量/简介）"""
import json, re, urllib.request, time, sys, os, signal
from concurrent.futures import ThreadPoolExecutor, as_completed

BASE = 'https://gdgp.chinaxinge.com'
SCRIPTS_DIR = '/Users/scentlibrart-007/.qclaw/workspace-agent-f8126f99/xgjia-website/scripts'
LOFTS_LIST = f'{SCRIPTS_DIR}/lofts_list.json'
OUT = f'{SCRIPTS_DIR}/lofts_about_batch.json'

with open(LOFTS_LIST, encoding='utf-8') as f:
    lofts = json.load(f)

lofts_sorted = sorted(lofts, key=lambda l: int(l['gp_id']))

def fetch(url, timeout=15):
    req = urllib.request.Request(url, headers={
        'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
        'Accept-Language': 'zh-CN,zh;q=0.9',
    })
    try:
        return urllib.request.urlopen(req, timeout=timeout).read().decode('gbk', errors='replace')
    except Exception as e:
        return None

def extract_about(html, gp_id):
    """从 about.asp 提取容量、届数、简介"""
    if not html or len(html) < 1000:
        return {'gp_id': gp_id, 'status': 'failed'}

    result = {'gp_id': gp_id, 'status': 'ok',
              'capacity': '', 'limit': '', 'description': '',
              'race_type': '', 'edition': ''}

    # 提取所有 <td> 单元格
    cells = re.findall(r'<td[^>]*>(.*?)</td>', html, re.DOTALL)
    text_cells = []
    for c in cells:
        t = re.sub(r'<[^>]+>', '', c).strip()
        t = re.sub(r'&nbsp;', ' ', t)
        t = re.sub(r'\s+', ' ', t)
        text_cells.append(t)

    # 在连续的两个单元格中找标签→值对
    # 标签: 可容量羽数 / 限收羽数 / 举办届数 / 举办比赛性质
    label_map = {
        '可容量羽数': 'capacity',
        '限收羽数': 'limit',
        '举办届数': 'edition',
        '举办比赛性质': 'race_type',
    }
    for i, cell in enumerate(text_cells):
        for label, field in label_map.items():
            if label == cell.strip():
                val = text_cells[i+1].strip() if i+1 < len(text_cells) else ''
                val = re.sub(r'\s+', '', val)
                if val and val not in ('查看', ''):
                    result[field] = val[:30]
                break

    # 简介
    desc_m = re.search(r'本公棚成立[^\n]{0,500}', html, re.DOTALL)
    if desc_m:
        text = re.sub(r'<[^>]+>', ' ', desc_m.group())
        text = re.sub(r'\s+', ' ', text).strip()
        result['description'] = text[:2000]
    else:
        # Try alt: description text
        m2 = re.search(r'简介[:：]?\s*([\u4e00-\u9fa5]{10,2000})', html, re.DOTALL)
        if m2:
            result['description'] = m2.group(1)[:2000]

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

def process(loft):
    gp_id = loft['gp_id']
    name = loft['name']
    if gp_id in done_ids:
        return None
    url = f'{BASE}/style/mo1/about.asp?gp_id={gp_id}'
    html = fetch(url)
    if html:
        detail = extract_about(html, gp_id)
        ok_parts = [f'cap={detail["capacity"]}', f'limit={detail["limit"]}',
                    f'race_type={detail["race_type"]}', f'edition={detail["edition"]}']
        print(f'[{gp_id}] {name[:15]}: {" ".join(ok_parts)}', file=sys.stderr)
    else:
        detail = {'gp_id': gp_id, 'status': 'failed'}
        print(f'[{gp_id}] FAILED', file=sys.stderr)
    time.sleep(0.5)
    return detail

signal.signal(signal.SIGTERM, lambda s,f: (save(), sys.exit(1)))
signal.signal(signal.SIGINT, lambda s,f: (save(), sys.exit(1)))

def save():
    with open(OUT, 'w', encoding='utf-8') as f:
        json.dump(batch_results, f, ensure_ascii=False, indent=2)

count = 0
with ThreadPoolExecutor(max_workers=10) as executor:
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
print(f'\nDone! ok={ok}/{len(batch_results)}', file=sys.stderr)