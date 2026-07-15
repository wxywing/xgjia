#!/usr/bin/env python3
"""P0 全量赛事列表爬虫"""
import json, re, urllib.request, time, sys, os, ssl

ssl._create_default_https_context = ssl._create_unverified_context

BASE = "https://gdgp.chinaxinge.com"
STYLE = "/style/mo30"
SCRIPTS_DIR = os.path.dirname(os.path.abspath(__file__))
LOFTS_LIST = f"{SCRIPTS_DIR}/lofts_list.json"
OUT = f"{SCRIPTS_DIR}/races_p0.json"
LOG = f"{SCRIPTS_DIR}/races_p0_log.txt"

DELAY = 0.8
TIMEOUT = 20

def fetch(url):
    req = urllib.request.Request(url, headers={
        "User-Agent": "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36",
        "Accept-Language": "zh-CN,zh;q=0.9",
    })
    try:
        data = urllib.request.urlopen(req, timeout=TIMEOUT).read()
        if len(data) < 500:
            return None
        return data.decode("gbk", errors="replace")
    except:
        return None

def clean(text):
    return re.sub(r'\s+', ' ', re.sub(r'&nbsp;', ' ', re.sub(r'<[^>]+>', ' ', text))).strip()

def parse_races(html, gp_id, loft_name):
    races = []
    parts = re.split(r'(onclick="window\.open\(\'/gdgp_rcshow\.asp\?id=\d+\'\))', html)
    blocks = []
    for i in range(1, len(parts) - 1, 2):
        onclick = parts[i]
        block = onclick + parts[i + 1] if i + 1 < len(parts) else onclick
        m = re.search(r"gdgp_rcshow\.asp\?id=(\d+)", onclick)
        if m:
            blocks.append((m.group(1), block))
    if not blocks:
        return races
    for race_id, block in blocks:
        race = {"gp_id": gp_id, "loft_name": loft_name, "source_id": race_id}
        m = re.search(r'<div class="ss_title">(.*?)</div>', block, re.DOTALL)
        if m: race["name"] = clean(m.group(1))
        m = re.search(r'开笼时间[：:]\s*</span>(.*?)</div>', block, re.DOTALL)
        if m: race["release_time"] = clean(m.group(1))
        m = re.search(r'<div class="ss_gcsz">\s*(\d+)\s*</div>', block, re.DOTALL)
        if m: race["returned_count"] = int(m.group(1))
        m = re.search(r'上笼羽数[：:]\s*</span>\s*(\*|\d+)', block, re.DOTALL)
        if m:
            val = m.group(1)
            race["entry_count"] = 0 if val == "*" else int(val)
        if race.get("name"):
            races.append(race)
    return races

def classify_race(name):
    if "决赛" in name: return "final"
    if "预赛" in name or ("关" in name and "决赛" not in name): return "pre"
    if "训放" in name or "家飞" in name or "扫描" in name: return "train"
    if "收费站" in name or "收费" in name: return "toll"
    return "other"

def extract_season_year(name, release_time):
    if release_time:
        m = re.search(r"(\d{4})", release_time)
        if m: return int(m.group(1))
    if name:
        m = re.search(r"(\d{4})", name)
        if m: return int(m.group(1))
    return None

def extract_season_type(release_time):
    if not release_time: return "other"
    m = re.search(r"(\d{4})-(\d{2})", release_time)
    if m:
        month = int(m.group(2))
        if 1 <= month <= 6: return "spring"
        if 7 <= month <= 12: return "autumn"
    return "other"

def main():
    with open(LOFTS_LIST, encoding="utf-8") as f:
        lofts = json.load(f)
    total = len(lofts)
    all_races = []
    empty_lofts = []
    errors = []
    print(f"P0 race list scraper - {total} lofts, {DELAY}s delay")
    for i, loft in enumerate(lofts):
        gp_id = loft["gp_id"]
        name = loft["name"]
        html = fetch(f"{BASE}{STYLE}/race_rclist.asp?gp_id={gp_id}")
        if html is None:
            errors.append(f"[{i+1}/{total}] {name} (gp_id={gp_id})")
            print(f"[{i+1}/{total}] {name} - FAIL")
            time.sleep(DELAY)
            continue
        races = parse_races(html, gp_id, name)
        if not races:
            empty_lofts.append({"gp_id": gp_id, "name": name})
            print(f"[{i+1}/{total}] {name} - 0")
        else:
            for r in races:
                r["race_category"] = classify_race(r.get("name", ""))
                r["season_year"] = extract_season_year(r.get("name", ""), r.get("release_time", ""))
                r["season_type"] = extract_season_type(r.get("release_time", ""))
            all_races.extend(races)
            print(f"[{i+1}/{total}] {name} - {len(races)}")
        if (i + 1) % 50 == 0:
            with open(f"{SCRIPTS_DIR}/races_p0_checkpoint.json", "w", encoding="utf-8") as f:
                json.dump(all_races, f, ensure_ascii=False, indent=2)
            print(f"  [checkpoint {i+1}/{total} | {len(all_races)} races]")
        time.sleep(DELAY)
    with open(OUT, "w", encoding="utf-8") as f:
        json.dump({"total_lofts": total, "empty_lofts": empty_lofts, "total_races": len(all_races), "races": all_races, "errors": errors}, f, ensure_ascii=False, indent=2)
    from collections import Counter
    cats = Counter(r.get("race_category", "unknown") for r in all_races)
    with open(LOG, "w", encoding="utf-8") as f:
        f.write(f"=== P0 Done ===\nTotal lofts: {total}\nRaces: {len(all_races)}\nEmpty: {len(empty_lofts)}\nErrors: {len(errors)}\n\nBy category:\n")
        for cat, cnt in cats.most_common():
            f.write(f"  {cat}: {cnt}\n")
    print(f"\n=== DONE ===\nLofts: {total}\nRaces: {len(all_races)}\nEmpty: {len(empty_lofts)}\nErrors: {len(errors)}")
    for cat, cnt in cats.most_common():
        print(f"  {cat}: {cnt}")

if __name__ == "__main__":
    main()
