-- ============================================================
-- 迁移：race_results 添加性能索引（赛季总结页 + 公棚深度分析页优化）
-- 用途：
--   idx_speed_only     → getSeasonTopFastest() / getLoftSeasonComparison() 按 speed 排序
--   idx_race_id        → getSeasonSummary / getSeasonTopLofts / getLoftSeasonComparison / getLoftTopOwners 的 JOIN race_results
--   idx_race_id_rank   → getLoftChampions / getLoftTopOwners 的 WHERE race_id IN (...) AND rank=1
-- 执行方式：phpMyAdmin → race_results 表 → SQL 标签页 → 逐条粘贴执行
-- 注意：如果提示 "Duplicate key name"，说明该索引已存在，跳过即可
-- ============================================================

ALTER TABLE `race_results` ADD INDEX `idx_speed_only` (`speed`);
ALTER TABLE `race_results` ADD INDEX `idx_race_id` (`race_id`);
ALTER TABLE `race_results` ADD INDEX `idx_race_id_rank` (`race_id`, `rank`);
