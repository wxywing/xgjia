-- ============================================================
-- 批量设置 batch3 3篇文章的封面图
-- 封面SVG已存在于: /public/images/articles/article_b3_XX.svg
-- 按标题精确匹配更新 cover 字段
-- ============================================================

UPDATE articles SET cover = '/public/images/articles/article_b3_01.svg' WHERE title = '秋季赛鸽赛前调整全攻略：让赛鸽达到最佳竞技状态';
UPDATE articles SET cover = '/public/images/articles/article_b3_02.svg' WHERE title = '幼鸽送公棚前必看：挑选健康幼鸽的10个关键指标';
UPDATE articles SET cover = '/public/images/articles/article_b3_03.svg' WHERE title = '赛鸽公棚赛与协会赛全面对比：哪种赛制更适合你？';
