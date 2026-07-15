-- 修复 xgjia-website 数据库 schema
-- 添加 type 列到 articles 表和 categories 表

-- 1. 添加 type 列到 articles 表
ALTER TABLE articles ADD COLUMN type TINYINT(1) NOT NULL DEFAULT 1 COMMENT '类型：1=文章，2=铭鸽' AFTER status;

-- 2. 添加 type 列到 categories 表
ALTER TABLE categories ADD COLUMN type VARCHAR(20) NOT NULL DEFAULT 'article' COMMENT '类型：article=文章，product=商品，pigeon=铭鸽' AFTER parent_id;

-- 3. 更新现有数据为默认值
UPDATE articles SET type = 1 WHERE type IS NULL;
UPDATE categories SET type = 'article' WHERE type IS NULL;

-- 4. 创建索引（可选，提升查询性能）
CREATE INDEX idx_articles_type ON articles(type);
CREATE INDEX idx_categories_type ON categories(type);
