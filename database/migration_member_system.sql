-- 信鸽之家 - 会员系统数据库迁移
-- 执行时间: 2026-05-22
-- 说明: 扩展会员等级系统，支持多级会员和发布配额

SET NAMES utf8mb4;

-- 1. 扩展 users 表 member_level 字段
ALTER TABLE users MODIFY COLUMN member_level TINYINT DEFAULT 0 COMMENT '会员等级: 0=免费, 1=铜牌, 2=银牌, 3=金牌';

-- 2. 新增会员套餐表
DROP TABLE IF EXISTS member_plans;
CREATE TABLE member_plans (
    id TINYINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(20) NOT NULL COMMENT '套餐名称',
    slug VARCHAR(20) NOT NULL COMMENT '标识',
    level TINYINT NOT NULL COMMENT '等级值',
    monthly_price DECIMAL(10,2) NOT NULL DEFAULT 0 COMMENT '月费',
    yearly_price DECIMAL(10,2) NOT NULL DEFAULT 0 COMMENT '年费',
    article_limit INT DEFAULT 0 COMMENT '每月文章数(0=不限)',
    pigeon_limit INT DEFAULT 0 COMMENT '铭鸽数量(0=不限)',
    listing_limit INT DEFAULT 0 COMMENT '分类信息数/月',
    dynamic_daily_limit INT DEFAULT 3 COMMENT '动态数/天',
    can_view_contact TINYINT DEFAULT 0 COMMENT '能否查看联系方式',
    can_top_content TINYINT DEFAULT 0 COMMENT '能否置顶内容',
    top_monthly INT DEFAULT 0 COMMENT '置顶次数/月',
    can_recommend TINYINT DEFAULT 0 COMMENT '能否推荐',
    recommend_weekly INT DEFAULT 0 COMMENT '推荐次数/周',
    no_ads TINYINT DEFAULT 0 COMMENT '是否去广告',
    can_certify TINYINT DEFAULT 0 COMMENT '能否认证',
    priority_audit TINYINT DEFAULT 0 COMMENT '优先审核',
    sort_order INT DEFAULT 0 COMMENT '排序',
    status TINYINT DEFAULT 1 COMMENT '状态: 0=禁用, 1=启用',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_level (level),
    UNIQUE KEY uk_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='会员套餐表';

-- 插入4个套餐配置
INSERT INTO member_plans (name, slug, level, monthly_price, yearly_price, article_limit, pigeon_limit, listing_limit, dynamic_daily_limit, can_view_contact, can_top_content, top_monthly, can_recommend, recommend_weekly, no_ads, can_certify, priority_audit, sort_order, status) VALUES
('免费会员', 'free', 0, 0, 0, 0, 0, 3, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1,1),
('铜牌会员', 'bronze', 1, 29.90, 299, 5, 5, 5, 10, 1, 0, 0, 0, 0, 0, 0, 2, 1,1),
('银牌会员', 'silver', 2, 59.90, 599, 20, 30, 20, 999, 1, 1, 2, 0, 0, 0, 1, 1, 3, 1),
('金牌会员', 'gold', 3, 99.90, 999, 0, 0, 0, 9999, 1, 1, 5, 1, 1, 1, 1, 1, 4, 1);

-- 3. 新增发布额度统计表
DROP TABLE IF EXISTS publish_stats;
CREATE TABLE publish_stats (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL COMMENT '用户ID',
    stat_month VARCHAR(7) NOT NULL COMMENT '统计月份(如2026-05)',
    article_count INT DEFAULT 0 COMMENT '本月已发文章数',
    pigeon_count INT DEFAULT 0 COMMENT '本月已发铭鸽数',
    listing_count INT DEFAULT 0 COMMENT '本月已发分类信息数',
    dynamic_count INT DEFAULT 0 COMMENT '今日已发动态数',
    top_used INT DEFAULT 0 COMMENT '本月已用置顶次数',
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_user_month (user_id, stat_month),
    KEY idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='发布额度统计表';

-- 4. 扩展 orders 表支持多种产品类型
ALTER TABLE orders MODIFY COLUMN product_type TINYINT NOT NULL COMMENT '产品类型: 1=会员, 2=内容置顶, 3=推荐位, 4=认证服务';
ALTER TABLE orders ADD COLUMN target_id INT UNSIGNED DEFAULT NULL COMMENT '关联目标ID' AFTER product_name;
ALTER TABLE orders ADD COLUMN duration_days INT DEFAULT 1 COMMENT '持续天数' AFTER target_id;

-- 5. 新增内容置顶/推荐记录表
DROP TABLE IF EXISTS promotions;
CREATE TABLE promotions (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL COMMENT '用户ID',
    target_type VARCHAR(20) NOT NULL COMMENT '目标类型: article/pigeon/listing',
    target_id INT UNSIGNED NOT NULL COMMENT '目标ID',
    promotion_type TINYINT NOT NULL COMMENT '推广类型: 1=置顶, 2=推荐',
    start_at DATETIME NOT NULL COMMENT '开始时间',
    end_at DATETIME NOT NULL COMMENT '结束时间',
    status TINYINT DEFAULT 1 COMMENT '状态: 0=未开始, 1=进行中, 2=已结束',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_target (target_type, target_id),
    KEY idx_end_at (end_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='内容推广记录表';

-- 6. 新增会员升级记录表
DROP TABLE IF EXISTS member_orders;
CREATE TABLE member_orders (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL COMMENT '用户ID',
    order_no VARCHAR(50) NOT NULL COMMENT '订单号',
    from_level TINYINT NOT NULL COMMENT '原等级',
    to_level TINYINT NOT NULL COMMENT '新等级',
    plan_type TINYINT NOT NULL COMMENT '套餐类型: 1=月费, 2=年费',
    months INT NOT NULL COMMENT '月数',
    amount DECIMAL(10,2) NOT NULL COMMENT '金额',
    payment_method VARCHAR(20) COMMENT '支付方式',
    payment_no VARCHAR(100) COMMENT '支付流水号',
    status TINYINT DEFAULT 0 COMMENT '状态: 0=待支付, 1=已支付, 2=已取消, 3=已退款',
    paid_at DATETIME COMMENT '支付时间',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_order_no (order_no),
    KEY idx_user_id (user_id),
    KEY idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='会员订单表';

SELECT '会员系统数据库迁移完成!' AS result;