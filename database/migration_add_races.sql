-- 赛事成绩表（方案C：单表+索引）
-- 预计 2100 万条记录，InnoDB 完全可扛
-- 使用方法：浏览器访问 _migrate_races.php 执行本 SQL

CREATE TABLE IF NOT EXISTS `race_results` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `source_id` INT UNSIGNED NOT NULL COMMENT '赛事ID（races.id）',
  `gp_id` INT UNSIGNED NOT NULL COMMENT '公棚ID（lofts.gp_id）',
  `race_name` VARCHAR(200) NOT NULL COMMENT '赛事名称',
  `rank` SMALLINT UNSIGNED NOT NULL COMMENT '排名',
  `owner_name` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '鸽主',
  `region` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '地区',
  `ring_number` VARCHAR(30) NOT NULL DEFAULT '' COMMENT '足环号',
  `color` VARCHAR(50) NOT NULL DEFAULT '' COMMENT '羽色',
  `arrival_time` DATETIME NULL DEFAULT NULL COMMENT '归巢时间',
  `speed` DECIMAL(10, 3) NOT NULL DEFAULT 0.000 COMMENT '分速（米/分）',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_source_rank` (`source_id`, `rank`),
  INDEX `idx_gp_rank` (`gp_id`, `rank`),
  INDEX `idx_ring` (`ring_number`),
  INDEX `idx_owner` (`owner_name`(20))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='赛事成绩表（全量）';

-- 导入策略（在 _import_race_results.php 中实现）：
-- 1. 先建表（无索引，除了 PRIMARY KEY）
-- 2. 将 checkpoint JSON 按 5 万条分片，生成 CSV 文件
-- 3. 用 LOAD DATA INFILE 批量导入（比 INSERT 快 10-20 倍）
-- 4. 导入完成后，再执行上面的 CREATE INDEX 语句
-- 预计时间：~15 分钟（2100 万条）
