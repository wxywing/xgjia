-- ============================================================
-- 修正 batch3 文章分类ID
-- 原因: 原来硬编码 category_id=8，实际应为 category_id=19（养鸽知识）
-- 执行方式: 通过 phpmyadmin 执行
-- ============================================================

-- 修正已插入文章的分类ID：category_id 从 8 改为 19（养鸽知识）
-- 按标题精确匹配，只改本次插入的3篇文章
UPDATE articles SET category_id = 19 WHERE title = '秋季赛鸽赛前调整全攻略：让赛鸽达到最佳竞技状态';
UPDATE articles SET category_id = 19 WHERE title = '幼鸽送公棚前必看：挑选健康幼鸽的10个关键指标';
UPDATE articles SET category_id = 19 WHERE title = '赛鸽公棚赛与协会赛全面对比：哪种赛制更适合你？';
