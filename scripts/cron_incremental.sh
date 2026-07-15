#!/bin/bash
# 增量爬虫 crontab 调用脚本
# 用法: */6 * * * * /path/to/scripts/cron_incremental.sh >> /path/to/scripts/crawl_incremental.log 2>&1
#
# 建议频率: 每6小时一次（公棚赛事更新不快，6h足够）
# 双保险: 同时做增量新赛事 + 补全空成绩赛事

set -e
cd "$(dirname "$0")"

# 激活虚拟环境（如有）
# source ../venv/bin/activate

echo "=== $(date '+%Y-%m-%d %H:%M:%S') 开始增量爬虫 ==="

# 模式1: 增量新赛事
python3 crawl_incremental.py

# 模式2: 补全不完整赛事（result_count=0 的）
python3 crawl_incremental.py --backfill

echo "=== $(date '+%Y-%m-%d %H:%M:%S') 增量爬虫完成 ==="
echo ""
