# 1. 先 dry-run 看增量规模
python3 crawl_incremental_all.py --type all --dry-run

# 2. 赛事（产出最大，核心数据）
python3 crawl_incremental_all.py --type races

# 3. 铭鸽（分批）
python3 crawl_incremental_all.py --type pigeons --max-shops 30

# 4. 展厅（新展厅少，快速）
python3 crawl_incremental_all.py --type shops

# 5. 公棚（几乎没新的，秒完）
python3 crawl_incremental_all.py --type lofts



# 每6小时：赛事增量 + 铭鸽增量(30个展厅轮转)
0 */6 * * * /www/wwwroot/xgjia.com/scripts/cron_incremental_all.sh >> /www/wwwroot/xgjia.com/scripts/crawl_incremental.log 2>&1

# 每周日凌晨2点：全量爬取(公棚+展厅+铭鸽全部+赛事)
0 2 * * 0 /www/wwwroot/xgjia.com/scripts/cron_incremental_all.sh full >> /www/wwwroot/xgjia.com/scripts/crawl_incremental_full.log 2>&1



策略说明
频率	触发	抓什么	理由
每6小时	cron	赛事增量 + 铭鸽30个展厅	赛事更新频繁，铭鸽轮转覆盖366展厅
每周日2am	cron	全部四类全量	低频数据（公棚/展厅）周级即可
铭鸽用了 hash(date) 决定起点，每批30个展厅轮转，4天能覆盖全部366个，不会重复爬同一批。