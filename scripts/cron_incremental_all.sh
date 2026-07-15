#!/bin/bash
# 统一增量爬虫 crontab 调用脚本
#
# 建议 crontab 配置:
#   0 */6 * * * /path/to/scripts/cron_incremental_all.sh >> /path/to/scripts/crawl_incremental_all.log 2>&1
#   0 2 * * 0 /path/to/scripts/cron_incremental_all.sh full >> /path/to/scripts/crawl_incremental_all_full.log 2>&1
#
# 策略:
#   - 每6小时: 赛事增量 (races) + 铭鸽增量 (pigeons, 30展厅)
#   - 每周日凌晨2点: 全量 (lofts + shops + pigeons所有 + races)

# 1. 先 dry-run 看增量规模
#python3 crawl_incremental_all.py --type all --dry-run

# 2. 赛事（产出最大，核心数据）
#python3 crawl_incremental_all.py --type races

# 3. 铭鸽（分批）
#python3 crawl_incremental_all.py --type pigeons --max-shops 30

# 4. 展厅（新展厅少，快速）
#python3 crawl_incremental_all.py --type shops

# 5. 公棚（几乎没新的，秒完）
#python3 crawl_incremental_all.py --type lofts

set -e
cd "$(dirname "$0")"

MODE="${1:-quick}"

echo "=== $(date '+%Y-%m-%d %H:%M:%S') 统一增量爬虫开始 (mode=$MODE) ==="

if [ "$MODE" = "full" ]; then
    # 全量模式 — 所有类型全部处理
    python3 crawl_incremental_all.py --type all
else
    # 快速模式 — 赛事 + 铭鸽（限30展厅，轮转覆盖）
    python3 crawl_incremental_all.py --type races
    python3 crawl_incremental_all.py --type pigeons --max-shops 30
fi

echo "=== $(date '+%Y-%m-%d %H:%M:%S') 统一增量爬虫完成 ==="
echo ""
