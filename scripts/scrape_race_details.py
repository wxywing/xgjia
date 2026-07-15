#!/usr/bin/env python3
"""
赛事详情补全爬虫
逐场抓 gdgp_rcshow.asp?id={source_id} 提取：
  - name (赛事名称)        从 ss_title
  - distance_km (空距)     从 司放空距
  - release_location (司放)从 司放地点
  - participant_count (羽数)从 参赛羽数
输出 UPDATE SQL
"""
import json, re, time, os, ssl, urllib.request, sys, signal

ssl._create_default_https_context = ssl._create_unverified_context
SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
OUT_SQL = os.path.join(SCRIPT_DIR, "update_race_details.sql")
CHECKPOINT = os.path.join(SCRIPT_DIR, "race_details_checkpoint.json")
MYSQL = "/Applications/phpstudy/Extensions/MySQL5.7.28/bin/mysql"

HEADERS = {
    "User-Agent": "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36",
    "Referer": "https://gdgp.chinaxinge.com/style/mo30/race_rclist.asp?gp_id=2648"
}

running = True

def sig_handler(sig, frame):
    global running
    running = False
    print("\nSIGTERM received, saving checkpoint...", flush=True)

signal.signal(signal.SIGTERM, sig_handler)
signal.signal(signal.SIGINT, sig_handler)

def get_source_ids():
    """从数据库读取所有 race_id -> source_id 映射"""
    import subprocess
    cmd = [MYSQL, "-u", "root", "-p123456", "xgjia", "-N", "-e",
           "SELECT id, source_id FROM races ORDER BY id"]
    out = subprocess.check_output(cmd, stderr=subprocess.DEVNULL)
    ids = {}
    for line in out.decode("utf-8").strip().split("\n"):
        if line:
            parts = line.split("\t")
            if len(parts) == 2:
                ids[int(parts[0])] = int(parts[1])
    return ids

def fetch_page(source_id, timeout=15):
    url = f"https://gdgp.chinaxinge.com/gdgp_rcshow.asp?id={source_id}&page=1&o=0"
    req = urllib.request.Request(url, headers=HEADERS)
    try:
        data = urllib.request.urlopen(req, timeout=timeout).read()
        return data.decode("gbk", errors="replace")
    except Exception as e:
        print(f"  FETCH ERROR: {e}", flush=True)
        return None

def parse_detail(html):
    """提取: name, distance_km, release_location, participant_count"""
    result = {}
    # 赛事名称 (ss_title)
    m = re.search(r'id="ss_title"[^>]*>([^<]+)<', html)
    if m:
        result["name"] = m.group(1).strip()
    # 司放地点
    m = re.search(r'司放地点[：:].*?</td>\s*<td[^>]*>([^<]+)<', html, re.DOTALL)
    if m:
        result["release_location"] = m.group(1).strip()
    # 司放空距
    m = re.search(r'司放空距[：:].*?</td>\s*<td[^>]*>([^<]+)<', html, re.DOTALL)
    if m:
        raw = m.group(1).strip()
        num = re.search(r"([\d.]+)", raw)
        if num:
            result["distance_km"] = num.group(1)
    # 参赛羽数
    m = re.search(r'参赛羽数[：:].*?</td>\s*<td[^>]*>([^<]+)<', html, re.DOTALL)
    if m:
        raw = m.group(1).strip()
        if raw.isdigit():
            result["participant_count"] = int(raw)
    return result

def main():
    global running
    race_ids = get_source_ids()
    total = len(race_ids)
    print(f"Total: {total} races to process", flush=True)

    # Load checkpoint
    done = {}
    errors = []
    if os.path.exists(CHECKPOINT):
        with open(CHECKPOINT, encoding="utf-8") as f:
            cp = json.load(f)
            done = {int(k): v for k, v in cp.get("done", {}).items()}
            errors = cp.get("errors", [])
            print(f"Checkpoint: {len(done)} done, {len(errors)} errors", flush=True)

    # Filter pending
    pending = {k: v for k, v in race_ids.items() if k not in done}

    sql_lines = []
    count = len(done)
    start = time.time()

    for race_id, source_id in pending.items():
        if not running:
            break
        count += 1
        elapsed = time.time() - start
        rate = count / elapsed * 60 if elapsed > 0 else 0
        print(f"[{count}/{total}] race_id={race_id} src={source_id} ({rate:.1f}/min)", flush=True)

        html = fetch_page(source_id)
        if not html:
            errors.append({"race_id": race_id, "source_id": source_id, "error": "fetch"})
            done[race_id] = {"status": "fetch_failed"}
            continue

        detail = parse_detail(html)
        if not detail:
            errors.append({"race_id": race_id, "source_id": source_id, "error": "parse"})
            done[race_id] = {"status": "parse_empty"}
            continue

        cols = []
        vals = []
        if "name" in detail:
            safe_name = detail["name"].replace("\\", "\\\\").replace("'", "\\'")
            cols.append("name")
            vals.append(f"'{safe_name}'")
        if "distance_km" in detail:
            cols.append("distance_km")
            vals.append(detail["distance_km"])
        if "release_location" in detail:
            safe = detail["release_location"].replace("\\", "\\\\").replace("'", "\\'")
            cols.append("release_location")
            vals.append(f"'{safe}'")
        if "participant_count" in detail:
            cols.append("participant_count")
            vals.append(str(detail["participant_count"]))

        if cols:
            set_clause = ", ".join(f"{c} = {v}" for c, v in zip(cols, vals))
            sql = f"UPDATE races SET {set_clause} WHERE id = {race_id};"
            sql_lines.append(sql)

        done[race_id] = {"status": "ok", "fields": list(detail.keys())}

        # Save every 50
        if len(sql_lines) >= 50:
            with open(OUT_SQL, "a", encoding="utf-8") as f:
                f.write("\n".join(sql_lines) + "\n")
            sql_lines.clear()
            with open(CHECKPOINT, "w", encoding="utf-8") as f:
                json.dump({"done": done, "errors": errors}, f, ensure_ascii=False, indent=2)

        time.sleep(0.5)

    # Final flush
    if sql_lines:
        with open(OUT_SQL, "a", encoding="utf-8") as f:
            f.write("\n".join(sql_lines) + "\n")

    with open(CHECKPOINT, "w", encoding="utf-8") as f:
        json.dump({"done": done, "errors": errors}, f, ensure_ascii=False, indent=2)

    ok = sum(1 for v in done.values() if v.get("status") == "ok")
    fail = len(done) - ok
    print(f"\nDone: {ok} ok, {fail} failed", flush=True)
    print(f"SQL: {OUT_SQL}", flush=True)

if __name__ == "__main__":
    main()