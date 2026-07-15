-- =====================================================
-- 信鸽之家 - 数据库结构
-- 创建日期: 2026-05-20
-- =====================================================

-- 设置字符集
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================
-- 1. 用户表
-- =====================================================
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(50) NOT NULL COMMENT '用户名',
    `password` VARCHAR(255) NOT NULL COMMENT '密码(加密)',
    `email` VARCHAR(100) NOT NULL COMMENT '邮箱',
    `nickname` VARCHAR(50) COMMENT '昵称',
    `avatar` VARCHAR(255) DEFAULT '/public/assets/images/default-avatar.png' COMMENT '头像',
    `phone` VARCHAR(20) COMMENT '手机号',
    `member_level` TINYINT DEFAULT 0 COMMENT '会员等级: 0=免费会员, 1=VIP会员',
    `member_expire_at` DATETIME COMMENT '会员到期时间',
    `balance` DECIMAL(10,2) DEFAULT 0.00 COMMENT '账户余额',
    `role` VARCHAR(20) DEFAULT 'user' COMMENT '角色: admin=管理员, user=普通用户',
    `status` TINYINT DEFAULT 1 COMMENT '状态: 0=禁用, 1=正常',
    `last_login_at` DATETIME COMMENT '最后登录时间',
    `last_login_ip` VARCHAR(50) COMMENT '最后登录IP',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_username` (`username`),
    UNIQUE KEY `uk_email` (`email`),
    KEY `idx_member_level` (`member_level`),
    KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户表';

-- =====================================================
-- 2. 分类表
-- =====================================================
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `parent_id` INT UNSIGNED DEFAULT 0 COMMENT '父分类ID',
    `slug` VARCHAR(50) NOT NULL COMMENT 'URL别名(拼音)',
    `name` VARCHAR(50) NOT NULL COMMENT '分类名称',
    `type` TINYINT NOT NULL COMMENT '类型: 1=文章分类, 2=铭鸽分类, 3=分类信息, 4=公棚分类',
    `icon` VARCHAR(255) COMMENT '分类图标',
    `sort` INT DEFAULT 0 COMMENT '排序',
    `status` TINYINT DEFAULT 1 COMMENT '状态: 0=禁用, 1=启用',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_parent_id` (`parent_id`),
    KEY `idx_type` (`type`),
    KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='分类表';

-- =====================================================
-- 3. 文章表
-- =====================================================
DROP TABLE IF EXISTS `articles`;
CREATE TABLE `articles` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL COMMENT '发布者ID',
    `category_id` INT UNSIGNED COMMENT '分类ID',
    `title` VARCHAR(255) NOT NULL COMMENT '标题',
    `summary` VARCHAR(500) COMMENT '摘要',
    `content` LONGTEXT COMMENT '内容',
    `cover` VARCHAR(255) COMMENT '封面图',
    `source` VARCHAR(100) COMMENT '来源',
    `author` VARCHAR(50) COMMENT '作者',
    `views` INT UNSIGNED DEFAULT 0 COMMENT '浏览数',
    `likes` INT UNSIGNED DEFAULT 0 COMMENT '点赞数',
    `comments` INT UNSIGNED DEFAULT 0 COMMENT '评论数',
    `is_top` TINYINT DEFAULT 0 COMMENT '是否置顶: 0=否, 1=是',
    `is_recommend` TINYINT DEFAULT 0 COMMENT '是否推荐: 0=否, 1=是',
    `status` TINYINT DEFAULT 1 COMMENT '状态: 0=草稿, 1=已发布, 2=已下架',
    `published_at` DATETIME COMMENT '发布时间',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_category_id` (`category_id`),
    KEY `idx_status` (`status`),
    KEY `idx_is_top` (`is_top`),
    KEY `idx_is_recommend` (`is_recommend`),
    KEY `idx_published_at` (`published_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文章表';

-- =====================================================
-- 4. 铭鸽展示表
-- =====================================================
DROP TABLE IF EXISTS `pigeons`;
CREATE TABLE `pigeons` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL COMMENT '发布者ID',
    `category_id` INT UNSIGNED COMMENT '分类ID',
    `name` VARCHAR(100) NOT NULL COMMENT '鸽子名称',
    `ring_number` VARCHAR(50) COMMENT '足环号',
    `bloodline` VARCHAR(100) COMMENT '血统',
    `gender` TINYINT COMMENT '性别: 0=未知, 1=雄, 2=雌',
    `birth_date` DATE COMMENT '出生日期',
    `color` VARCHAR(50) COMMENT '羽色',
    `eye_color` VARCHAR(50) COMMENT '眼砂',
    `description` TEXT COMMENT '描述',
    `images` TEXT COMMENT '图片(JSON数组)',
    `video` VARCHAR(255) COMMENT '视频链接',
    `achievements` TEXT COMMENT '比赛成绩(JSON)',
    `pedigree` TEXT COMMENT '血统书(JSON)',
    `views` INT UNSIGNED DEFAULT 0 COMMENT '浏览数',
    `likes` INT UNSIGNED DEFAULT 0 COMMENT '点赞数',
    `comments` INT UNSIGNED DEFAULT 0 COMMENT '评论数',
    `is_top` TINYINT DEFAULT 0 COMMENT '是否置顶',
    `is_recommend` TINYINT DEFAULT 0 COMMENT '是否推荐',
    `status` TINYINT DEFAULT 0 COMMENT '状态: 0=待审核, 1=已通过, 2=已拒绝, 3=已下架',
    `reject_reason` VARCHAR(255) COMMENT '拒绝原因',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_category_id` (`category_id`),
    KEY `idx_ring_number` (`ring_number`),
    KEY `idx_bloodline` (`bloodline`),
    KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='铭鸽展示表';

-- =====================================================
-- 5. 分类信息表
-- =====================================================
DROP TABLE IF EXISTS `listings`;
CREATE TABLE `listings` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL COMMENT '发布者ID',
    `type` TINYINT NOT NULL COMMENT '类型: 1=鸽舍转让, 2=配对信息, 3=求购/转让',
    `title` VARCHAR(255) NOT NULL COMMENT '标题',
    `description` TEXT COMMENT '描述',
    `images` TEXT COMMENT '图片(JSON数组)',
    `contact_name` VARCHAR(50) COMMENT '联系人',
    `contact_phone` VARCHAR(20) COMMENT '联系电话',
    `contact_wechat` VARCHAR(50) COMMENT '微信号',
    `price` DECIMAL(10,2) COMMENT '价格',
    `negotiable` TINYINT DEFAULT 0 COMMENT '价格面议: 0=否, 1=是',
    `location` VARCHAR(100) COMMENT '地区',
    `views` INT UNSIGNED DEFAULT 0 COMMENT '浏览数',
    `likes` INT UNSIGNED DEFAULT 0 COMMENT '点赞数',
    `status` TINYINT DEFAULT 0 COMMENT '状态: 0=待审核, 1=已通过, 2=已拒绝, 3=已下架, 4=已成交',
    `expire_at` DATETIME COMMENT '过期时间',
    `reject_reason` VARCHAR(255) COMMENT '拒绝原因',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_type` (`type`),
    KEY `idx_status` (`status`),
    KEY `idx_location` (`location`),
    KEY `idx_expire_at` (`expire_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='分类信息表';

-- =====================================================
-- 6. 广告表
-- =====================================================
DROP TABLE IF EXISTS `advertisements`;
CREATE TABLE `advertisements` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `position` VARCHAR(50) NOT NULL COMMENT '广告位置: home_banner, article_bottom, sidebar',
    `title` VARCHAR(100) COMMENT '广告标题',
    `image` VARCHAR(255) COMMENT '图片地址',
    `link` VARCHAR(255) COMMENT '跳转链接',
    `sort` INT DEFAULT 0 COMMENT '排序',
    `clicks` INT UNSIGNED DEFAULT 0 COMMENT '点击次数',
    `start_at` DATETIME COMMENT '开始时间',
    `end_at` DATETIME COMMENT '结束时间',
    `status` TINYINT DEFAULT 1 COMMENT '状态: 0=禁用, 1=启用',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_position` (`position`),
    KEY `idx_status` (`status`),
    KEY `idx_start_at` (`start_at`),
    KEY `idx_end_at` (`end_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='广告表';

-- =====================================================
-- 7. 系统设置表
-- =====================================================
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `key` VARCHAR(50) NOT NULL COMMENT '设置键',
    `value` TEXT COMMENT '设置值',
    `description` VARCHAR(255) COMMENT '说明',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统设置表';

-- =====================================================
-- 8. 评论表
-- =====================================================
DROP TABLE IF EXISTS `comments`;
CREATE TABLE `comments` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL COMMENT '用户ID',
    `target_type` TINYINT NOT NULL COMMENT '目标类型: 1=文章, 2=铭鸽, 3=分类信息',
    `target_id` INT UNSIGNED NOT NULL COMMENT '目标ID',
    `content` VARCHAR(500) NOT NULL COMMENT '评论内容',
    `parent_id` INT UNSIGNED DEFAULT 0 COMMENT '父评论ID',
    `likes` INT UNSIGNED DEFAULT 0 COMMENT '点赞数',
    `status` TINYINT DEFAULT 1 COMMENT '状态: 0=隐藏, 1=显示',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_target` (`target_type`, `target_id`),
    KEY `idx_parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='评论表';

-- =====================================================
-- 9. 点赞表
-- =====================================================
DROP TABLE IF EXISTS `likes`;
CREATE TABLE `likes` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL COMMENT '用户ID',
    `target_type` TINYINT NOT NULL COMMENT '目标类型: 1=文章, 2=铭鸽, 3=分类信息, 4=评论',
    `target_id` INT UNSIGNED NOT NULL COMMENT '目标ID',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_user_target` (`user_id`, `target_type`, `target_id`),
    KEY `idx_target` (`target_type`, `target_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='点赞表';

-- =====================================================
-- 10. 会员订单表
-- =====================================================
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_no` VARCHAR(50) NOT NULL COMMENT '订单号',
    `user_id` INT UNSIGNED NOT NULL COMMENT '用户ID',
    `product_type` TINYINT NOT NULL COMMENT '产品类型: 1=会员',
    `product_name` VARCHAR(100) COMMENT '产品名称',
    `amount` DECIMAL(10,2) NOT NULL COMMENT '金额',
    `months` INT COMMENT '会员月数',
    `status` TINYINT DEFAULT 0 COMMENT '状态: 0=待支付, 1=已支付, 2=已取消',
    `payment_method` VARCHAR(20) COMMENT '支付方式',
    `payment_at` DATETIME COMMENT '支付时间',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_order_no` (`order_no`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='订单表';

-- =====================================================
-- 11. 赛事表
-- =====================================================
DROP TABLE IF EXISTS `races`;
CREATE TABLE `races` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL COMMENT '赛事名称',
    `category_id` INT UNSIGNED COMMENT '分类ID',
    `race_type` VARCHAR(50) COMMENT '赛事类型: 国家级, 省级, 市级, 区域级',
    `race_date` DATE NOT NULL COMMENT '比赛日期',
    `end_date` DATE COMMENT '结束日期',
    `location` VARCHAR(100) COMMENT '举办地点',
    `organizer` VARCHAR(100) COMMENT '主办方',
    `distance` INT UNSIGNED COMMENT '比赛距离(公里)',
    `prize_pool` DECIMAL(12,2) COMMENT '奖金池',
    `registration_deadline` DATE COMMENT '报名截止日期',
    `description` TEXT COMMENT '赛事描述',
    `rules` TEXT COMMENT '比赛规则',
    `prize` TEXT COMMENT '奖项设置',
    `views` INT UNSIGNED DEFAULT 0 COMMENT '浏览数',
    `status` TINYINT DEFAULT 1 COMMENT '状态: 0=取消, 1=报名中, 2=进行中, 3=已结束',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_race_date` (`race_date`),
    KEY `idx_status` (`status`),
    KEY `idx_category_id` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='赛事表';

-- =====================================================
-- 12. 动态表（鸽友圈）
-- =====================================================
DROP TABLE IF EXISTS `dynamics`;
CREATE TABLE `dynamics` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL COMMENT '用户ID',
    `content` VARCHAR(1000) NOT NULL COMMENT '内容',
    `images` TEXT COMMENT '图片(JSON数组)',
    `likes` INT UNSIGNED DEFAULT 0 COMMENT '点赞数',
    `views` INT UNSIGNED DEFAULT 0 COMMENT '点赞数',
    `comments` INT UNSIGNED DEFAULT 0 COMMENT '评论数',
    `status` TINYINT DEFAULT 1 COMMENT '状态: 0=隐藏, 1=显示',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_status` (`status`),
    KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='动态表';

-- =====================================================
-- 13. 关注表
-- =====================================================
DROP TABLE IF EXISTS `follows`;
CREATE TABLE `follows` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `follower_id` INT UNSIGNED NOT NULL COMMENT '关注者ID',
    `following_id` INT UNSIGNED NOT NULL COMMENT '被关注者ID',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_follow` (`follower_id`, `following_id`),
    KEY `idx_follower_id` (`follower_id`),
    KEY `idx_following_id` (`following_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='关注表';

-- =====================================================
-- 14. 公棚表
-- =====================================================
DROP TABLE IF EXISTS `lofts`;
CREATE TABLE `lofts` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL COMMENT '创建者/管理员ID',
    `name` VARCHAR(100) NOT NULL COMMENT '公棚名称',
    `province` VARCHAR(30) COMMENT '省份',
    `city` VARCHAR(30) COMMENT '城市',
    `address` VARCHAR(255) COMMENT '详细地址',
    `contact_name` VARCHAR(50) COMMENT '联系人',
    `contact_phone` VARCHAR(20) COMMENT '联系电话',
    `logo` VARCHAR(255) COMMENT '公棚Logo图片路径',
    `photos` TEXT COMMENT '公棚照片(JSON数组)',
    `description` TEXT COMMENT '公棚简介',
    `capacity` INT UNSIGNED DEFAULT 0 COMMENT '收鸽容量(羽)',
    `current_count` INT UNSIGNED DEFAULT 0 COMMENT '当前收鸽数(羽)',
    `entry_fee` DECIMAL(10,2) DEFAULT 0 COMMENT '参赛费(元/羽)',
    `management_fee` DECIMAL(10,2) DEFAULT 0 COMMENT '饲养管理费(元/羽)',
    `prize_pool` DECIMAL(12,2) DEFAULT 0 COMMENT '奖金池(元)',
    `prize_detail` TEXT COMMENT '奖金分配明细(JSON)',
    `race_distance` INT UNSIGNED COMMENT '比赛距离(公里)',
    `race_type` VARCHAR(50) COMMENT '比赛类型: 春棚/秋棚/特比',
    `collect_start` DATE COMMENT '收鸽开始日期',
    `collect_end` DATE COMMENT '收鸽截止日期',
    `training_start` DATE COMMENT '训放开始日期',
    `race_date` DATE COMMENT '比赛日期',
    `rules` TEXT COMMENT '参赛规程',
    `facilities` TEXT COMMENT '公棚设施介绍',
    `rating` DECIMAL(3,2) DEFAULT 0 COMMENT '综合评分(0-10)',
    `rating_count` INT UNSIGNED DEFAULT 0 COMMENT '评分人数',
    `views` INT UNSIGNED DEFAULT 0 COMMENT '浏览数',
    `is_certified` TINYINT DEFAULT 0 COMMENT '是否认证公棚: 0=未认证, 1=已认证',
    `is_hot` TINYINT DEFAULT 0 COMMENT '是否热门公棚',
    `status` TINYINT DEFAULT 0 COMMENT '状态: 0=待审核, 1=正常, 2=已关闭, 3=已取消',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_province` (`province`),
    KEY `idx_city` (`city`),
    KEY `idx_race_type` (`race_type`),
    KEY `idx_status` (`status`),
    KEY `idx_is_certified` (`is_certified`),
    KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='公棚表';

-- =====================================================
-- 15. 公棚参赛记录表
-- =====================================================
DROP TABLE IF EXISTS `loft_entries`;
CREATE TABLE `loft_entries` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `loft_id` INT UNSIGNED NOT NULL COMMENT '公棚ID',
    `user_id` INT UNSIGNED NOT NULL COMMENT '交鸽鸽友ID',
    `pigeon_ring` VARCHAR(50) NOT NULL COMMENT '足环号',
    `pigeon_name` VARCHAR(50) COMMENT '鸽子名称',
    `pigeon_color` VARCHAR(30) COMMENT '羽色',
    `entry_fee_paid` TINYINT DEFAULT 0 COMMENT '参赛费是否已付: 0=未付, 1=已付',
    `management_fee_paid` TINYINT DEFAULT 0 COMMENT '管理费是否已付: 0=未付, 1=已付',
    `status` TINYINT DEFAULT 1 COMMENT '状态: 0=已退出, 1=已入棚, 2=训放中, 3=已参赛, 4=已归巢, 5=未归巢',
    `result_rank` INT UNSIGNED COMMENT '比赛名次',
    `result_speed` DECIMAL(10,2) COMMENT '飞行速度(米/分)',
    `prize_amount` DECIMAL(10,2) COMMENT '获奖金额',
    `remark` VARCHAR(255) COMMENT '备注',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_loft_id` (`loft_id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_pigeon_ring` (`pigeon_ring`),
    KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='公棚参赛记录表';

-- =====================================================
-- 16. 公棚评价表
-- =====================================================
DROP TABLE IF EXISTS `loft_reviews`;
CREATE TABLE `loft_reviews` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `loft_id` INT UNSIGNED NOT NULL COMMENT '公棚ID',
    `user_id` INT UNSIGNED NOT NULL COMMENT '评价用户ID',
    `rating` TINYINT NOT NULL COMMENT '评分(1-10)',
    `content` VARCHAR(500) COMMENT '评价内容',
    `is_anonymous` TINYINT DEFAULT 0 COMMENT '是否匿名评价',
    `status` TINYINT DEFAULT 1 COMMENT '状态: 0=隐藏, 1=显示',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_loft_id` (`loft_id`),
    KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='公棚评价表';

-- =====================================================
-- 初始化数据
-- =====================================================

-- 插入默认分类
INSERT INTO `categories` (`parent_id`, `slug`, `name`, `type`, `sort`, `status`) VALUES
-- 文章分类 (type=1)
(0, 'saishi', '赛事新闻', 1, 1, 1),
(0, 'yangge', '养鸽知识', 1, 2, 1),
(0, 'hangye', '行业动态', 1, 3, 1),
(0, 'gebing', '鸽病防治', 1, 4, 1),
-- 铭鸽分类 (type=2)
(0, 'xinge', '信鸽', 2, 1, 1),
(0, 'saige', '赛鸽', 2, 2, 1),
(0, 'zhongge', '种鸽', 2, 3, 1),
(0, 'youge', '幼鸽', 2, 4, 1),
-- 公棚分类 (type=4)
(0, 'chunpeng', '春棚', 4, 1, 1),
(0, 'qiupeng', '秋棚', 4, 2, 1),
(0, 'tebi', '特比环棚', 4, 3, 1),
(0, 'duoguan', '多关赛棚', 4, 4, 1);

-- 插入默认系统设置
INSERT INTO `settings` (`key`, `value`, `description`) VALUES
('site_name', '信鸽之家', '网站名称'),
('site_description', '专业的信鸽信息交流平台', '网站描述'),
('require_audit', '1', '发布内容是否需要审核: 0=否, 1=是'),
('audit_pigeon', '1', '铭鸽发布是否需要审核'),
('audit_listing', '1', '分类信息发布是否需要审核'),
('member_price_monthly', '29.9', '会员月费'),
('member_price_yearly', '299', '会员年费'),
('contact_phone', '', '客服电话'),
('contact_wechat', '', '客服微信');

-- 插入测试管理员
INSERT INTO `users` (`username`, `password`, `email`, `nickname`, `member_level`, `status`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@xgjia.com', '管理员', 1, 1);

-- =====================================================
SET FOREIGN_KEY_CHECKS = 1;
