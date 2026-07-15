-- =====================================================
-- 公棚表（新增）
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
-- 公棚参赛记录表（鸽友交鸽记录）
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
-- 公棚评分/评价表
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
-- 分类表扩展：新增公棚分类类型(4=公棚分类)
-- =====================================================
ALTER TABLE `categories` MODIFY COLUMN `type` TINYINT NOT NULL COMMENT '类型: 1=文章分类, 2=铭鸽分类, 3=分类信息, 4=公棚分类';

-- =====================================================
-- 测试数据
-- =====================================================

-- 公棚分类
INSERT INTO `categories` (`parent_id`, `name`, `type`, `sort`, `status`) VALUES
(0, '春棚', 4, 1, 1),
(0, '秋棚', 4, 2, 1),
(0, '特比环棚', 4, 3, 1),
(0, '多关赛棚', 4, 4, 1);

-- 公棚测试数据
INSERT INTO `lofts` (`user_id`, `name`, `province`, `city`, `address`, `contact_name`, `contact_phone`, `logo`, `description`, `capacity`, `current_count`, `entry_fee`, `management_fee`, `prize_pool`, `race_distance`, `race_type`, `collect_start`, `collect_end`, `training_start`, `race_date`, `rules`, `rating`, `rating_count`, `views`, `is_certified`, `is_hot`, `status`) VALUES
(1, '北京长城公棚', '北京', '北京', '北京市顺义区马坡镇', '王建国', '010-12345678', '/public/assets/images/loft-default.jpg', '北京长城公棚成立于2005年，是华北地区知名秋棚，设施完善，管理严格，历年归巢率名列前茅。', 3000, 1850, 500.00, 200.00, 100000.00, 500, '秋棚', '2026-03-01', '2026-05-31', '2026-08-01', '2026-10-15', '参赛规程详见公棚官网，严格执行中鸽协相关规定。', 8.5, 120, 2500, 1, 1, 1),
(1, '上海东方公棚', '上海', '上海', '上海市浦东新区曹路镇', '李明华', '021-98765432', '/public/assets/images/loft-default.jpg', '上海东方公棚是华东地区最大的春棚之一，拥有现代化鸽舍和专业训放团队。', 5000, 3200, 800.00, 300.00, 200000.00, 350, '春棚', '2026-10-01', '2026-12-31', '2027-02-01', '2027-05-01', '参赛规程严格执行，确保公平公正。', 9.2, 200, 5800, 1, 1, 1),
(2, '广州南方公棚', '广东', '广州', '广州市白云区太和镇', '张伟', '020-87654321', '/public/assets/images/loft-default.jpg', '广州南方公棚专注华南地区信鸽赛事，适合南方气候条件训养。', 2000, 1200, 300.00, 150.00, 50000.00, 300, '秋棚', '2026-03-01', '2026-06-30', '2026-09-01', '2026-11-20', '规程规范，收费合理。', 7.8, 80, 1200, 0, 0, 1),
(1, '成都天府公棚', '四川', '成都', '成都市新都区大丰街道', '陈志远', '028-76543210', '/public/assets/images/loft-default.jpg', '成都天府公棚西南地区知名特比环棚，历年比赛成绩优异。', 2500, 1500, 600.00, 250.00, 150000.00, 400, '特比环棚', '2026-04-01', '2026-07-31', '2026-09-15', '2026-11-01', '特比环规程，严格执行。', 8.0, 95, 1800, 1, 0, 1),
(3, '武汉长江公棚', '湖北', '武汉', '武汉市洪山区花山街道', '刘国强', '027-54321678', '/public/assets/images/loft-default.jpg', '武汉长江公棚地处华中，交通便利，多关赛经验丰富。', 3000, 800, 400.00, 180.00, 80000.00, 450, '多关赛棚', '2026-02-01', '2026-05-31', '2026-08-01', '2026-10-30', '多关赛规程，五关赛制。', 7.5, 50, 600, 0, 0, 1);

-- 公棚参赛记录测试数据
INSERT INTO `loft_entries` (`loft_id`, `user_id`, `pigeon_ring`, `pigeon_name`, `pigeon_color`, `entry_fee_paid`, `management_fee_paid`, `status`, `remark`) VALUES
(1, 2, '2026-01-123456', '灰雄001', '灰', 1, 1, 1, '状态良好'),
(1, 2, '2026-01-123457', '雨点雄002', '雨点', 1, 1, 1, ''),
(1, 3, '2026-01-234567', '灰雌001', '灰', 1, 1, 2, '训放中'),
(2, 2, '2026-02-345678', '红轮雄001', '红轮', 1, 1, 1, ''),
(2, 3, '2026-02-345679', '白条雌001', '白条', 1, 0, 1, '管理费未付');