#!/usr/bin/env python3
"""
从 MySQL 导出 race_results 为 CSV（分片），供服务器 LOAD DATA INFILE 导入。
每 500 万行一个分片，约 400MB/片。
"""
import csv, sys, os

# mysql CLI 直接流式导出
# 使用 mysql -B --quick 避免内存暴涨

SHARD_ROWS = 5_000_000  # 每片行数
PWD = "123456"
DB = "xgjia"
TABLE = "race_results"
OUTDIR = os.path.dirname(os.path.abspath(__file__)) + "/"

print(f"开始导出 {TABLE}...")

cmd = (
    f"/Applications/phpstudy/Extensions/MySQL5.7.28/bin/mysql"
    f" -u root -p{PWD} --default-character-set=utf8mb4"
    f" -D {DB} --quick --batch --skip-column-names"
    f" -e \"SELECT race_id, rank, owner_name, region, ring_number, color,"
    f" DATE_FORMAT(arrival_time, '%Y-%m-%d %H:%i:%s.%f'), speed"
    f" FROM {TABLE} ORDER BY id\""
)

import subprocess
proc = subprocess.Popen(cmd, shell=True, stdout=subprocess.PIPE, stderr=subprocess.PIPE, bufsize=0)

shard_idx = 0
row_cnt = 0
f = None
line_cnt = 0

for line in proc.stdout:
    line_cnt += 1
    if line_cnt % SHARD_ROWS == 1:
        if f:
            f.close()
            os.system(f"gzip -f {fpath}")
            print(f"  ✅ shard {shard_idx}: {row_cnt} 行 → {fpath}.gz")
        shard_idx += 1
        row_cnt = 0
        fpath = f"{OUTDIR}race_results_s{shard_idx:02d}.csv"
        f = open(fpath, "w", encoding="utf-8")
        f.write("race_id,rank,owner_name,region,ring_number,color,arrival_time,speed\n")

    # mysql --batch 输出是 TSV（tab 分隔），转为 CSV
    line_str = line.decode("utf-8").rstrip("\n").rstrip("\r")
    # 转义双引号，用引号包裹含逗号/引号的字段
    cols = line_str.split("\t")
    csv_cols = []
    for c in cols:
        if '"' in c or ',' in c or '\n' in c:
            csv_cols.append('"' + c.replace('"', '""') + '"')
        else:
            csv_cols.append(c)
    f.write(",".join(csv_cols) + "\n")
    row_cnt += 1

    if line_cnt % 500000 == 0:
        print(f"  ... {line_cnt:,} 行已处理", flush=True)

if f:
    f.close()
    os.system(f"gzip -f {fpath}")
    print(f"  ✅ shard {shard_idx}: {row_cnt} 行 → {fpath}.gz")

proc.wait()
err = proc.stderr.read().decode()
if err and "Warning" not in err:
    print(f"MySQL 错误: {err}", file=sys.stderr)

total = sum(1 for _ in range(1))  # placeholder
print(f"  ✅ 完成！共 {line_cnt:,} 行，{shard_idx} 个分片")
