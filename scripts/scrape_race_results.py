#!/usr/bin/env python3
"""
P1: 抓取决赛成绩明细
读 races_p0.json → 过滤final → 逐场抓 gdgp_rcshow.asp?id=X&page=Y → race_results_p1.json
"""
import json, re, time, sys, os, ssl, urllib.request

ssl._create_default_https_context = ssl._create_unverified_context
SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
P0_FILE = os.path.join(SCRIPT_DIR, 'races_p0.json')
OUT_FILE = os.path.join(SCRIPT_DIR, 'race_results_p1.json')
CHECKPOINT_FILE = os.path.join(SCRIPT_DIR, 'race_results_p1_checkpoint.json')

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
    """Parse results table: rank, owner_name, region, ring_number, color, arrival_time, speed
    Strategy: scan ALL <tr> blocks in the full HTML, keep only rows with exactly 7 <td> cells
    where the first cell is a digit (this skips header and false matches)."""
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
    """Extract total pages and total records"""
    m = re.search(r'页码：\d+/(\d+).*?共\s*(\d+)\s*条', html)
    if m:
        return int(m.group(1)), int(m.group(2))
    return 1, len(parse_results(html))

def main():
    # Load P0 data
    with open(P0_FILE) as f:
        p0 = json.load(f)

    finals = [r for r in p0['races'] if r['race_category'] == 'final']
    print(f'P1: {len(finals)} finals to process', flush=True)

    # Check for checkpoint
    processed_ids = set()
    all_results = []
    errors = []

    if os.path.exists(CHECKPOINT_FILE):
        with open(CHECKPOINT_FILE) as f:
            cp = json.load(f)
            all_results = cp.get('results', [])
            errors = cp.get('errors', [])
            processed_ids = {r['source_id'] for r in all_results}
            print(f'Resuming from checkpoint: {len(processed_ids)} already processed', flush=True)

        # Delete corrupted output to force re-save
        if os.path.exists(OUT_FILE):
            os.remove(OUT_FILE)

    total_results = sum(r['total_results'] for r in all_results)

    for i, race in enumerate(finals):
        sid = race['source_id']
        loft = race['loft_name']
        name = race['name']
        returned = race['returned_count']

        print(f'[{i+1}/{len(finals)}] {loft} - {name} (id={sid}, returned={returned})', flush=True)

        if sid in processed_ids:
            print(f'  SKIP (already done)', flush=True)
            continue

        try:
            html_p1 = fetch_page(sid, 1)
            if not html_p1:
                errors.append(f'Page1 fail: {sid} {loft} {name}')
                continue

            total_pages, total_records = parse_pagination(html_p1)
            print(f'  pages={total_pages}, total_records={total_records}', flush=True)

            race_results = {
                'source_id': sid,
                'gp_id': race['gp_id'],
                'loft_name': loft,
                'race_name': name,
                'total_results': total_records,
                'results': parse_results(html_p1)
            }

            # Fetch remaining pages
            for pg in range(2, total_pages + 1):
                time.sleep(0.4)
                html = fetch_page(sid, pg)
                if html:
                    page_results = parse_results(html)
                    race_results['results'].extend(page_results)
                    if pg % 5 == 0 or pg == total_pages:
                        print(f'    page {pg}/{total_pages}, +{len(page_results)} rows (total: {len(race_results["results"])})', flush=True)

            all_results.append(race_results)
            total_results += total_records
            processed_ids.add(sid)

        except Exception as e:
            print(f'  ERROR: {e}', flush=True)
            errors.append(f'{sid} {loft}: {e}')

        # Save checkpoint every 3 races
        if (i + 1) % 3 == 0:
            cp = {'processed': i + 1, 'total': len(finals), 'results': all_results, 'errors': errors}
            with open(CHECKPOINT_FILE, 'w') as f:
                json.dump(cp, f, ensure_ascii=False)
            print(f'  [checkpoint at {i+1}/{len(finals)} | {total_results} total results]', flush=True)

        time.sleep(0.6)

    # Final output
    output = {
        'total_races': len(finals),
        'total_results': total_results,
        'results': all_results,
        'errors': errors
    }
    with open(OUT_FILE, 'w') as f:
        json.dump(output, f, ensure_ascii=False)

    print()
    print('=== P1 DONE ===', flush=True)
    print(f'Races: {len(all_results)}/{len(finals)}', flush=True)
    print(f'Total results: {total_results}', flush=True)
    print(f'Errors: {len(errors)}', flush=True)
    print(f'Output: {OUT_FILE}', flush=True)

if __name__ == '__main__':
    main()
