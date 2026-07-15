#!/usr/bin/env python3
"""
增量赛事爬虫 — 定时脚本
对比 DB 已有数据，只抓新增赛事 + 补全不完整赛事的结果数据

用法:
  python3 crawl_incremental.py                    # 默认：只抓增量
  python3 crawl_incremental.py --backfill         # 补全 result_count=0 的赛事成绩
  python3 crawl_incremental.py --dry-run          # 只对比不写入
  python3 crawl_incremental.py --loft-id 2648     # 只抓指定公棚

输出:
  scripts/crawl_incremental_report.json  — 本次运行报告
  scripts/crawl_incremental_checkpoint.json — 续跑断点
"""
import json, re, time, os, sys, ssl, urllib.request, subprocess, argparse
from datetime import datetime
from collections import OrderedDict

ssl._create_default_https_context = ssl._create_unverified_context

# ============================================================
# 配置
# ============================================================
SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
PROJECT_DIR = os.path.dirname(SCRIPT_DIR)
MYSQL = "/Applications/phpstudy/Extensions/MySQL5.7.28/bin/mysql"
MYSQL_DB = "xgjia"
MYSQL_USER = "root"
MYSQL_PASS = "123456"

BASE_URL = "https://gdgp.chinaxinge.com"
STYLE = "/style/mo30"
RACE_LIST_PATH = f"{STYLE}/race_rclist.asp"        # 赛事列表页
RACE_RESULT_PATH = "/gdgp_rcshow.asp"               # 成绩明细页

DELAY_LIST = 0.5     # 列表页间隔
DELAY_RESULT = 0.3   # 成绩页间隔
TIMEOUT = 20

REPORT_FILE = os.path.join(SCRIPT_DIR, "crawl_incremental_report.json")
CHECKPOINT_FILE = os.path.join(SCRIPT_DIR, "crawl_incremental_checkpoint.json")

HEADERS = {
    "User-Agent": "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36",
    "Accept-Language": "zh-CN,zh;q=0.9",
}

# ============================================================
# MySQL 操作（通过 CLI）
# ============================================================
def mysql_query(sql):
    """执行查询，返回 (行列表, 列名列表)"""
    cmd = [MYSQL, "-u", MYSQL_USER, f"-p{MYSQL_PASS}", MYSQL_DB, "-N", "-e", sql]
    try:
        out = subprocess.check_output(cmd, stderr=subprocess.DEVNULL, timeout=30)
        lines = out.decode("utf-8").strip().split("\n")
        rows = [line.split("\t") for line in lines if line.strip()]
        return rows, None
    except subprocess.CalledProcessError as e:
        print(f"  MySQL QUERY ERROR: {e.output.decode() if e.output else e}", flush=True)
        return [], None

def mysql_exec(sql):
    """执行写操作"""
    cmd = [MYSQL, "-u", MYSQL_USER, f"-p{MYSQL_PASS}", MYSQL_DB, "-e", sql]
    try:
        subprocess.check_output(cmd, stderr=subprocess.DEVNULL, timeout=30)
        return True
    except subprocess.CalledProcessError as e:
        print(f"  MySQL EXEC ERROR: {e.output.decode() if e.output else e}", flush=True)
        return False

def mysql_escape(s):
    """简单转义"""
    if s is None:
        return "NULL"
    return "'" + str(s).replace("\\", "\\\\").replace("'", "\\'") + "'"

# ============================================================
# HTTP 请求
# ============================================================
def fetch(url, timeout=TIMEOUT):
    """抓取页面，返回 GBK 解码的文本"""
    req = urllib.request.Request(url, headers=HEADERS)
    try:
        data = urllib.request.urlopen(req, timeout=timeout).read()
        if len(data) < 500:
            return None
        return data.decode("gbk", errors="replace")
    except Exception as e:
        print(f"    FETCH ERROR: {e}", flush=True)
        return None

# ============================================================
# 解析函数
# ============================================================
def clean(text):
    return re.sub(r'\s+', ' ', re.sub(r'&nbsp;', ' ', re.sub(r'<[^>]+>', ' ', text))).strip()

def parse_race_list(html):
    """从赛事列表页解析赛事条目"""
    races = []
    # 按 onclick="window.open('/gdgp_rcshow.asp?id=数字') 分割
    blocks = re.findall(
        r"onclick=\"window\.open\('/gdgp_rcshow\.asp\?id=(\d+)'\)\".*?"
        r"<div class=\"ss_title\">(.*?)</div>.*?"
        r"开笼时间[：:]\s*</span>(.*?)</div>.*?"
        r"<div class=\"ss_gcsz\">\s*(\d+)\s*</div>.*?"
        r"上笼羽数[：:]\s*</span>\s*(\*|\d+)",
        html, re.DOTALL
    )
    for m in blocks:
        source_id, name, release_time, returned, entry = m
        races.append({
            "source_id": source_id,
            "name": clean(name),
            "release_time": clean(release_time),
            "returned_count": int(returned) if returned.isdigit() else 0,
            "entry_count": 0 if entry == "*" else (int(entry) if entry.isdigit() else 0),
        })
    return races

def classify_race(name):
    if "决赛" in name: return "final"
    if "预赛" in name or ("关" in name and "决赛" not in name): return "pre"
    if "训放" in name or "家飞" in name or "扫描" in name: return "train"
    if "收费站" in name or "收费" in name: return "toll"
    return "other"

def extract_season(name, release_time):
    """提取年份和赛季类型"""
    year = None
    season = "other"
    t = release_time or name or ""
    m = re.search(r"(\d{4})", t)
    if m:
        year = int(m.group(1))
    m = re.search(r"(\d{4})-(\d{2})", t)
    if m:
        month = int(m.group(2))
        season = "spring" if 1 <= month <= 6 else "autumn"
    return year, season

def parse_results_page(html):
    """从成绩明细页解析成绩记录"""
    results = []
    rows = re.findall(r'<tr[^>]*>(.*?)</tr>', html, re.DOTALL | re.IGNORECASE)
    for row in rows:
        cells = re.findall(r'<td[^>]*>(.*?)</td>', row, re.DOTALL | re.IGNORECASE)
        c = [re.sub(r'<[^>]+>', '', cell).strip() for cell in cells]
        if len(c) == 7 and c[0].isdigit():
            try:
                speed_val = c[6].replace(',', '')
                results.append({
                    "rank": int(c[0]),
                    "owner_name": c[1] if c[1] else None,
                    "region": c[2] if c[2] else None,
                    "ring_number": c[3] if c[3] else None,
                    "color": c[4] if c[4] else None,
                    "arrival_time": c[5] if c[5] else None,
                    "speed": float(speed_val) if speed_val.replace('.', '').replace('-', '').isdigit() else 0.0,
                })
            except (ValueError, IndexError):
                pass
    return results

def parse_pagination(html):
    """解析页码信息：返回 (总页数, 总记录数)"""
    m = re.search(r'页码[：:]\s*\d+/(\d+).*?共\s*(\d+)\s*条', html)
    if m:
        return int(m.group(1)), int(m.group(2))
    return 1, len(parse_results_page(html))

# ============================================================
# 核心逻辑
# ============================================================
def load_existing_source_ids():
    """从 DB 加载所有已有 races.source_id"""
    rows, _ = mysql_query("SELECT source_id FROM races WHERE source_id IS NOT NULL")
    return {r[0] for r in rows if r and r[0]}

def load_lofts():
    """从 DB 加载所有公棚"""
    rows, _ = mysql_query("SELECT id, name, gp_id FROM lofts WHERE status = 1 ORDER BY id")
    lofts = []
    for r in rows:
        if len(r) >= 3 and r[2]:
            lofts.append({"db_id": int(r[0]), "name": r[1], "gp_id": r[2]})
    return lofts

def load_incomplete_races():
    """加载 result_count=0 的赛事（需要补全成绩）"""
    rows, _ = mysql_query(
        "SELECT id, loft_id, source_id, name, returned_count "
        "FROM races WHERE result_count = 0 AND source_id IS NOT NULL AND status = 1"
    )
    return [{"race_id": int(r[0]), "loft_id": int(r[1]), "source_id": r[2],
             "name": r[3], "returned_count": int(r[4]) if r[4].isdigit() else 0} for r in rows if r]

def build_insert_race_sql(loft_id, race_data):
    """构建 INSERT INTO races SQL"""
    r = race_data
    return (
        f"INSERT INTO races (loft_id, source_id, name, release_time, returned_count, "
        f"entry_count, race_category, season_year, season_type) VALUES ("
        f"{loft_id}, {mysql_escape(r['source_id'])}, {mysql_escape(r['name'])}, "
        f"{mysql_escape(r['release_time'])}, {r['returned_count']}, "
        f"{r['entry_count']}, {mysql_escape(classify_race(r['name']))}, "
        f"{r.get('season_year', 'NULL') or 'NULL'}, "
        f"{mysql_escape(r.get('season_type', 'other'))})"
    )

def build_insert_results_sql(race_id, results):
    """构建批量 INSERT INTO race_results SQL（每批最多 500 条）"""
    if not results:
        return []
    sqls = []
    base = "INSERT IGNORE INTO race_results (race_id, `rank`, owner_name, region, ring_number, color, arrival_time, speed) VALUES "
    batch = []
    for row in results:
        batch.append(
            f"({race_id}, {row['rank']}, {mysql_escape(row['owner_name'])}, "
            f"{mysql_escape(row['region'])}, {mysql_escape(row['ring_number'])}, "
            f"{mysql_escape(row['color'])}, {mysql_escape(row['arrival_time'])}, "
            f"{row['speed']})"
        )
        if len(batch) >= 500:
            sqls.append(base + ",\n".join(batch))
            batch = []
    if batch:
        sqls.append(base + ",\n".join(batch))
    return sqls

def update_race_result_count(race_id, count):
    """更新赛事的 result_count"""
    return mysql_exec(f"UPDATE races SET result_count = {count} WHERE id = {race_id}")

# ============================================================
# 主流程
# ============================================================
def main():
    parser = argparse.ArgumentParser(description="增量赛事爬虫")
    parser.add_argument("--backfill", action="store_true", help="补全 result_count=0 的赛事成绩")
    parser.add_argument("--dry-run", action="store_true", help="只对比不写入")
    parser.add_argument("--loft-id", type=str, help="只处理指定公棚 gp_id")
    args = parser.parse_args()

    print("=" * 60, flush=True)
    print(f"增量赛事爬虫 — {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}", flush=True)
    print(f"模式: {'dry-run' if args.dry_run else ('backfill' if args.backfill else 'incremental')}", flush=True)

    # 加载已有数据
    existing_ids = load_existing_source_ids()
    print(f"DB 现有赛事: {len(existing_ids)} 场", flush=True)

    report = {
        "timestamp": datetime.now().isoformat(),
        "mode": "dry-run" if args.dry_run else ("backfill" if args.backfill else "incremental"),
        "existing_races": len(existing_ids),
        "new_races": 0,
        "new_results": 0,
        "backfilled_races": 0,
        "backfilled_results": 0,
        "errors": [],
        "details": [],
    }

    # ========== 先处理补全 ==========
    if args.backfill:
        incomplete = load_incomplete_races()
        print(f"\n待补全赛事: {len(incomplete)} 场", flush=True)
        if args.dry_run:
            print("[DRY-RUN] 不执行写入", flush=True)
        for i, race in enumerate(incomplete):
            sid = race["source_id"]
            print(f"[backfill {i+1}/{len(incomplete)}] race_id={race['race_id']} src={sid} - {race['name']}", flush=True)
            html = fetch(f"{BASE_URL}{RACE_RESULT_PATH}?id={sid}&page=1&o=0")
            if not html:
                report["errors"].append(f"backfill fetch fail: src={sid}")
                continue

            total_pages, total_records = parse_pagination(html)
            if total_records == 0:
                print(f"  ⚪ 无成绩数据", flush=True)
                continue

            all_results = parse_results_page(html)
            for pg in range(2, total_pages + 1):
                time.sleep(DELAY_RESULT)
                html_pg = fetch(f"{BASE_URL}{RACE_RESULT_PATH}?id={sid}&page={pg}&o=0")
                if html_pg:
                    all_results.extend(parse_results_page(html_pg))

            print(f"  ✅ {len(all_results)} 条, {total_pages} 页", flush=True)
            if not args.dry_run:
                for sql in build_insert_results_sql(race["race_id"], all_results):
                    if not mysql_exec(sql):
                        report["errors"].append(f"insert fail: race_id={race['race_id']}")
                        break
                else:
                    update_race_result_count(race["race_id"], len(all_results))
            report["backfilled_races"] += 1
            report["backfilled_results"] += len(all_results)
            report["details"].append({
                "type": "backfill",
                "race_id": race["race_id"],
                "source_id": sid,
                "name": race["name"],
                "results": len(all_results),
            })
            time.sleep(DELAY_LIST)

    # ========== 增量新赛事抓取 ==========
    lofts = load_lofts()
    if args.loft_id:
        lofts = [l for l in lofts if l["gp_id"] == args.loft_id]
        if not lofts:
            print(f"未找到 gp_id={args.loft_id}", flush=True)
            sys.exit(1)
    print(f"\n待检查公棚: {len(lofts)} 个", flush=True)

    # 加载断点
    done_loft_ids = set()
    if os.path.exists(CHECKPOINT_FILE) and not args.loft_id:
        with open(CHECKPOINT_FILE) as f:
            cp = json.load(f)
            done_loft_ids = set(cp.get("done_loft_ids", []))
            print(f"断点续跑: 已处理 {len(done_loft_ids)} 个公棚", flush=True)

    for i, loft in enumerate(lofts):
        gp_id = loft["gp_id"]
        if gp_id in done_loft_ids:
            continue

        name = loft["name"]
        print(f"[{i+1}/{len(lofts)}] {name} (gp_id={gp_id})", flush=True)

        # 获取该公棚已有 source_id
        existing_for_loft = set()
        for sid, row_data in [(r[0], r) for r in
            mysql_query(f"SELECT source_id FROM races WHERE loft_id = {loft['db_id']} AND source_id IS NOT NULL")[0]]:
            existing_for_loft.add(sid)

        html = fetch(f"{BASE_URL}{RACE_LIST_PATH}?gp_id={gp_id}")
        if not html:
            report["errors"].append(f"list fetch fail: gp_id={gp_id} {name}")
            print(f"  ❌ 列表页获取失败", flush=True)
            done_loft_ids.add(gp_id)
            time.sleep(DELAY_LIST)
            continue

        races_on_page = parse_race_list(html)
        new_for_loft = [r for r in races_on_page if r["source_id"] not in existing_for_loft]

        if not new_for_loft:
            print(f"  ✅ 无新赛事 (页面 {len(races_on_page)} 场, DB {len(existing_for_loft)} 场)", flush=True)
        else:
            print(f"  🆕 发现 {len(new_for_loft)} 场新赛事", flush=True)

        for race in new_for_loft:
            sid = race["source_id"]
            year, season = extract_season(race.get("name", ""), race.get("release_time", ""))
            race["season_year"] = year
            race["season_type"] = season

            print(f"    [{sid}] {race['name']}", flush=True)

            # Step 1: Insert race
            if not args.dry_run:
                insert_sql = build_insert_race_sql(loft["db_id"], race)
                if not mysql_exec(insert_sql):
                    report["errors"].append(f"insert race fail: src={sid}")
                    continue
                # 获取刚插入的 race_id
                rows, _ = mysql_query(f"SELECT id FROM races WHERE source_id = '{sid}'")
                if not rows:
                    continue
                db_race_id = int(rows[0][0])
            else:
                db_race_id = 0  # dry-run

            # Step 2: Fetch results
            html_r = fetch(f"{BASE_URL}{RACE_RESULT_PATH}?id={sid}&page=1&o=0")
            if not html_r:
                report["errors"].append(f"results fetch fail: src={sid}")
                continue

            total_pages, total_records = parse_pagination(html_r)
            if total_records == 0:
                print(f"      ⚪ 无成绩数据", flush=True)
                continue

            all_results = parse_results_page(html_r)
            for pg in range(2, total_pages + 1):
                time.sleep(DELAY_RESULT)
                html_pg = fetch(f"{BASE_URL}{RACE_RESULT_PATH}?id={sid}&page={pg}&o=0")
                if html_pg:
                    all_results.extend(parse_results_page(html_pg))
                if pg % 10 == 0 or pg == total_pages:
                    print(f"      page {pg}/{total_pages} (累计 {len(all_results)})", flush=True)

            print(f"      ✅ {len(all_results)} 条, {total_pages} 页", flush=True)

            if not args.dry_run and db_race_id > 0:
                success = True
                for sql in build_insert_results_sql(db_race_id, all_results):
                    if not mysql_exec(sql):
                        report["errors"].append(f"insert results fail: race_id={db_race_id}")
                        success = False
                        break
                if success:
                    update_race_result_count(db_race_id, len(all_results))
                    existing_ids.add(sid)  # 更新内存缓存

            report["new_races"] += 1
            report["new_results"] += len(all_results)
            report["details"].append({
                "type": "new",
                "loft": name,
                "gp_id": gp_id,
                "source_id": sid,
                "name": race["name"],
                "results": len(all_results),
            })
            time.sleep(DELAY_LIST)

        done_loft_ids.add(gp_id)

        # 每个公棚后存 checkpoint
        if not args.dry_run and (i + 1) % 10 == 0:
            with open(CHECKPOINT_FILE, "w") as f:
                json.dump({"done_loft_ids": list(done_loft_ids), "report": report}, f, ensure_ascii=False, indent=2)

        time.sleep(DELAY_LIST)

    # ========== 输出报告 ==========
    if not args.dry_run:
        with open(CHECKPOINT_FILE, "w") as f:
            json.dump({"done_loft_ids": list(done_loft_ids), "report": report}, f, ensure_ascii=False, indent=2)

    with open(REPORT_FILE, "w", encoding="utf-8") as f:
        json.dump(report, f, ensure_ascii=False, indent=2)

    print("\n" + "=" * 60, flush=True)
    print("增量抓取完成", flush=True)
    print(f"  原有赛事: {report['existing_races']}", flush=True)
    print(f"  新增赛事: {report['new_races']}", flush=True)
    print(f"  新增成绩: {report['new_results']} 条", flush=True)
    print(f"  补全赛事: {report['backfilled_races']}", flush=True)
    print(f"  补全成绩: {report['backfilled_results']} 条", flush=True)
    print(f"  错误: {len(report['errors'])} 个", flush=True)
    for err in report["errors"]:
        print(f"    - {err}", flush=True)
    print(f"\n报告: {REPORT_FILE}", flush=True)

    # 清理断点（成功完成）
    if not args.dry_run and not report["errors"]:
        if os.path.exists(CHECKPOINT_FILE):
            os.remove(CHECKPOINT_FILE)

if __name__ == "__main__":
    main()
