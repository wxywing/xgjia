-- ============================================================
-- Tag 系统建表 SQL
-- 创建时间: 2026-07-07
-- 说明: 文章标签系统，支持话题/品系/城市/赛季四种类型
-- ============================================================

-- 标签表
CREATE TABLE IF NOT EXISTS tags (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL COMMENT '标签名称',
    slug VARCHAR(60) NOT NULL COMMENT 'URL友好名称',
    type ENUM('topic', 'strain', 'city', 'season') DEFAULT 'topic' COMMENT '标签类型',
    article_count INT UNSIGNED DEFAULT 0 COMMENT '文章数量（缓存）',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_name (name),
    UNIQUE KEY uk_slug (slug),
    KEY idx_type (type),
    KEY idx_article_count (article_count)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文章标签表';

-- 文章-标签关联表
CREATE TABLE IF NOT EXISTS article_tags (
    article_id INT UNSIGNED NOT NULL COMMENT '文章ID',
    tag_id INT UNSIGNED NOT NULL COMMENT '标签ID',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (article_id, tag_id),
    KEY idx_tag_id (tag_id),
    CONSTRAINT fk_article_tags_article FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    CONSTRAINT fk_article_tags_tag FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文章标签关联表';

-- 初始标签数据
INSERT INTO tags (name, slug, type) VALUES
-- 话题标签
('赛前调整', 'saiqian-tiaozheng', 'topic'),
('幼鸽管理', 'younge-guanli', 'topic'),
('公棚赛', 'gongpengsai', 'topic'),
('协会赛', 'xiehuisai', 'topic'),
('血统', 'xuetong', 'topic'),
('分速', 'fensu', 'topic'),
('归巢', 'guichao', 'topic'),
('配对', 'peidui', 'topic'),
('训放', 'xunfang', 'topic'),
('疾病防治', 'jibing-fangzhi', 'topic'),
-- 品系标签
('詹森', 'zhansen', 'strain'),
('胡本', 'huben', 'strain'),
('戈马力', 'gemali', 'strain'),
('克拉克', 'kelake', 'strain'),
('桑杰士', 'sangjieshi', 'strain'),
-- 赛季标签
('2026春赛', '2026-chunsai', 'season'),
('2026秋赛', '2026-qiusai', 'season'),
('2025春赛', '2025-chunsai', 'season')
ON DUPLICATE KEY UPDATE name=VALUES(name);
