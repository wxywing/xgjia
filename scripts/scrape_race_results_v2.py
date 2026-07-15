#!/usr/bin/env python3
"""
P1 v2: 全量赛事成绩明细爬虫（方案B）
读 races_p0.json → 全部2872场 → 逐场抓 gdgp_rcshow.asp?id=X&page=Y
复用已有 P1 数据（171场决赛），只抓新增的2701场
"""
import json, re, time, os, ssl, urllib.request

ssl._create_default_https_context = ssl._create_unverified_context
SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
P0_FILE = os.path.join(SCRIPT_DIR, 'races_p0.json')
P1_OLD = os.path.join(SCRIPT_DIR, 'race_results_p1.json')
OUT_FILE = os.path.join(SCRIPT_DIR, 'race_results_p1_full.json')
CHECKPOINT_FILE = os.path.join(SCRIPT_DIR, 'race_results_p1_full_checkpoint.json')

HEADERS = {
    'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
    'Referer': 'https://gdgp.chinaxinge.com/style/mo30/race_rclist.asp?gp_id='
}

def fetch_page(race_id, page=1, timeout=20):
    url = f'https://gdgp.chinaxinge.com/gdgp_rcshow.asp?id={race_id}&page={page}&o=0'
    req = urllib.request.Request(url, headers=HEADERS)
    try:
        data = urllib.request.urlopen(req, timeout=timeout).read()
        return data.decode('gbk', errors='replace')
    except Exception as e:
        print(f'  FETCH ERROR page={page}: {e}', flush=True)
        return None

def parse_results(html):
    results = []
    rows = re.findall(r'<tr[^>]*>(.*?)</tr>', html, re.DOTALL | re.IGNORECASE)
    for row in rows:
        cells = re.findall(r'<td[^>]*>(.*?)</td>', row, re.DOTALL | re.IGNORECASE)
        clean = [re.sub(r'<[^>]+>', '', c).strip() for c in cells]
        if len(clean) == 7 and clean[0].isdigit():
            try:
                results.append({
                    'rank': int(clean[0]),
                    'owner_name': clean[1],
                    'region': clean[2],
                    'ring_number': clean[3],
                    'color': clean[4],
                    'arrival_time': clean[5],
                    'speed': float(clean[6]) if clean[6].replace('.', '').replace('-', '').isdigit() else 0.0
                })
            except (ValueError, IndexError):
                pass
    return results

def parse_pagination(html):
    m = re.search(r'页码[：:]\s*\d+/(\d+).*?共\s*(\d+)\s*条', html)
    if m:
        return int(m.group(1)), int(m.group(2))
    return 1, len(parse_results(html))

def main():
    with open(P0_FILE) as f:
        p0 = json.load(f)

    all_races = p0['races']

    # 加载已有 P1 数据（171场决赛）
    processed_ids = set()
    all_results = []
    errors = []

    if os.path.exists(P1_OLD):
        with open(P1_OLD) as f:
            old = json.load(f)
            all_results = old.get('results', [])
            errors = old.get('errors', [])
            processed_ids = {r['source_id'] for r in all_results}
            old_total = old.get('total_results', 0)
            print(f'加载已有P1: {len(all_results)} 场, {old_total} 条记录', flush=True)
    else:
        old_total = 0
        print('未找到已有P1, 从头开始', flush=True)

    # Checkpoint 续跑
    if os.path.exists(CHECKPOINT_FILE):
        with open(CHECKPOINT_FILE) as f:
            cp = json.load(f)
            all_results = cp.get('results', [])
            errors = cp.get('errors', [])
            processed_ids = {r['source_id'] for r in all_results}
            print(f'从checkpoint续跑: {len(processed_ids)} 已处理', flush=True)

    total_results = sum(r['total_results'] for r in all_results)
    total_races = len(all_races)
    new_count = 0
    empty_count = 0
    fail_count = 0

    print(f'全量抓取: {total_races} 场, 已处理: {len(processed_ids)}, 待处理: {total_races - len(processed_ids)}', flush=True)
    print(f'当前累计: {len(all_results)} 场有数据, {total_results} 条记录', flush=True)
    print()

    for i, race in enumerate(all_races):
        sid = race['source_id']
        loft = race['loft_name']
        name = race['name']
        cat = race.get('race_category', '?')
        returned = race.get('returned_count', 0)

        if sid in processed_ids:
            continue

        print(f'[{i+1}/{total_races}] [{cat}] {loft} - {name}', flush=True)
        print(f'         id={sid}, returned={returned}', flush=True)

        try:
            html_p1 = fetch_page(sid, 1)
            if not html_p1:
                errors.append(f'Page1 fail: {sid} {loft} {name}')
                fail_count += 1
                print(f'  ❌ 页面获取失败', flush=True)
                processed_ids.add(sid)
                continue

            total_pages, total_records = parse_pagination(html_p1)

            if total_records == 0:
                empty_count += 1
                print(f'  ⚪ 无成绩数据 (pages={total_pages})', flush=True)
                # 记录空赛事，但不上传results
                all_results.append({
                    'source_id': sid,
                    'gp_id': race['gp_id'],
                    'loft_name': loft,
                    'race_name': name,
                    'total_results': 0,
                    'results': []
                })
            else:
                race_results = {
                    'source_id': sid,
                    'gp_id': race['gp_id'],
                    'loft_name': loft,
                    'race_name': name,
                    'total_results': total_records,
                    'results': parse_results(html_p1)
                }

                for pg in range(2, total_pages + 1):
                    time.sleep(0.4)
                    html = fetch_page(sid, pg)
                    if html:
                        page_results = parse_results(html)
                        race_results['results'].extend(page_results)
                        if pg % 10 == 0 or pg == total_pages:
                            print(f'    page {pg}/{total_pages} (累计 {len(race_results["results"])})', flush=True)

                all_results.append(race_results)
                total_results += total_records
                new_count += 1
                print(f'  ✅ {total_records} 条, {total_pages} 页', flush=True)

            processed_ids.add(sid)

        except Exception as e:
            print(f'  ❌ ERROR: {e}', flush=True)
            errors.append(f'{sid} {loft}: {e}')
            fail_count += 1
            processed_ids.add(sid)

        # 每3场存一次checkpoint
        processed_now = len(processed_ids)
        if processed_now % 3 == 0:
            cp = {'processed': processed_now, 'total': total_races, 'results': all_results, 'errors': errors}
            with open(CHECKPOINT_FILE, 'w') as f:
                json.dump(cp, f, ensure_ascii=False)
            print(f'  💾 checkpoint [{processed_now}/{total_races}] 累计 {total_results} 条 | 新增 {new_count} | 空 {empty_count} | 失败 {fail_count}', flush=True)

        time.sleep(0.5)

    # 最终输出
    output = {
        'total_races_processed': len(all_results),
        'total_results': total_results,
        'empty_races': empty_count,
        'failures': fail_count,
        'results': all_results,
        'errors': errors
    }
    with open(OUT_FILE, 'w') as f:
        json.dump(output, f, ensure_ascii=False)

    # 分类统计
    from collections import Counter
    cats = Counter()
    for r in all_results:
        # 从 P0 找 category
        for p0r in p0['races']:
            if p0r['source_id'] == r['source_id']:
                cats[p0r.get('race_category', '?')] += 1
                break

    print()
    print('=== P1 v2 DONE ===', flush=True)
    print(f'总场次: {len(all_results)}/{total_races}', flush=True)
    print(f'总记录: {total_results}', flush=True)
    print(f'无数据: {empty_count}', flush=True)
    print(f'失败: {fail_count}', flush=True)
    print()
    for cat, cnt in cats.most_common():
        print(f'  {cat}: {cnt}', flush=True)
    print(f'Output: {OUT_FILE}', flush=True)

if __name__ == '__main__':
    main()
