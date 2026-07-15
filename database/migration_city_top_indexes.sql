-- ============================================================
-- 迁移：race_results 复合索引 — 城市TOP页查询优化
-- 
-- 根因：getCityTopSpeedPigeons (13s) / getCityTopOwners (13s) / getCityTopLofts (45s)
-- 三条查询都 JOIN race_results，ORDER BY/GROUP BY 需扫描全量匹配行才能完成
--
-- 复合索引让 MySQL 在 JOIN 时直接用 race_id 定位到相关行，而非全表扫描
--
-- 执行方式：phpMyAdmin → race_results 表 → SQL 标签页 → 逐条粘贴执行
-- 预计耗时：12.7M 行，ALTER 约 3-8 分钟（低峰期执行，不影响读）
-- ============================================================

-- ① 分速榜：idx_race_id_speed 让 ORDER BY speed DESC 利用索引覆盖
ALTER TABLE `race_results` ADD INDEX `idx_race_id_speed` (`race_id`, `speed`);

-- ② 鸽主榜：idx_race_id_owner 让 JOIN + GROUP BY owner_name 更高效
ALTER TABLE `race_results` ADD INDEX `idx_race_id_owner` (`race_id`, `owner_name`);

-- ③ 公棚榜：idx_race_id_rank 让 rank 条件直接走索引
ALTER TABLE `race_results` ADD INDEX `idx_race_id_rank_full` (`race_id`, `rank`);
