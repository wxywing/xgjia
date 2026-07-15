-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2026-07-15 17:48:37
-- 服务器版本： 5.7.28
-- PHP 版本： 7.3.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `xgjia`
--

-- --------------------------------------------------------

--
-- 表的结构 `advertisements`
--

CREATE TABLE `advertisements` (
  `id` int(10) UNSIGNED NOT NULL,
  `position` varchar(50) NOT NULL COMMENT '广告位置: home_banner, article_bottom, sidebar',
  `title` varchar(100) DEFAULT NULL COMMENT '广告标题',
  `image` varchar(255) DEFAULT NULL COMMENT '图片地址',
  `link` varchar(255) DEFAULT NULL COMMENT '跳转链接',
  `sort` int(11) DEFAULT '0' COMMENT '排序',
  `clicks` int(10) UNSIGNED DEFAULT '0' COMMENT '点击次数',
  `start_at` datetime DEFAULT NULL COMMENT '开始时间',
  `end_at` datetime DEFAULT NULL COMMENT '结束时间',
  `status` tinyint(4) DEFAULT '1' COMMENT '状态: 0=禁用, 1=启用',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='广告表';

-- --------------------------------------------------------

--
-- 表的结构 `articles`
--

CREATE TABLE `articles` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL COMMENT '发布者ID',
  `category_id` int(10) UNSIGNED DEFAULT NULL COMMENT '分类ID',
  `title` varchar(255) NOT NULL COMMENT '标题',
  `summary` varchar(500) DEFAULT NULL COMMENT '摘要',
  `content` longtext COMMENT '内容',
  `cover` varchar(255) DEFAULT NULL COMMENT '封面图',
  `source` varchar(100) DEFAULT NULL COMMENT '来源',
  `author` varchar(50) DEFAULT NULL COMMENT '作者',
  `views` int(10) UNSIGNED DEFAULT '0' COMMENT '浏览数',
  `likes` int(10) UNSIGNED DEFAULT '0' COMMENT '点赞数',
  `comments` int(10) UNSIGNED DEFAULT '0' COMMENT '评论数',
  `is_top` tinyint(4) DEFAULT '0' COMMENT '是否置顶: 0=否, 1=是',
  `is_recommend` tinyint(4) DEFAULT '0' COMMENT '是否推荐: 0=否, 1=是',
  `status` tinyint(4) DEFAULT '1' COMMENT '状态: 0=草稿, 1=已发布, 2=已下架',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '类型：1=文章，2=铭鸽',
  `published_at` datetime DEFAULT NULL COMMENT '发布时间',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文章表';

-- --------------------------------------------------------

--
-- 表的结构 `article_tags`
--

CREATE TABLE `article_tags` (
  `article_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `categories`
--

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `parent_id` int(10) UNSIGNED DEFAULT '0' COMMENT '父分类ID',
  `slug` varchar(50) NOT NULL COMMENT 'URL别名(拼音)',
  `name` varchar(50) NOT NULL COMMENT '分类名称',
  `type` tinyint(4) NOT NULL COMMENT '类型: 1=文章分类, 2=铭鸽分类, 3=分类信息, 4=公棚分类',
  `icon` varchar(255) DEFAULT NULL COMMENT '分类图标',
  `sort` int(11) DEFAULT '0' COMMENT '排序',
  `status` tinyint(4) DEFAULT '1' COMMENT '状态: 0=禁用, 1=启用',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='分类表';

-- --------------------------------------------------------

--
-- 表的结构 `city_loft_rankings`
--

CREATE TABLE `city_loft_rankings` (
  `id` int(11) NOT NULL,
  `city` varchar(64) NOT NULL COMMENT '城市名',
  `rank_pos` tinyint(4) NOT NULL COMMENT '排名 1-10',
  `loft_id` int(11) DEFAULT '0' COMMENT '公棚ID',
  `loft_name` varchar(255) DEFAULT '' COMMENT '公棚名称',
  `race_count` int(11) DEFAULT '0' COMMENT '参赛场次',
  `total_entries` int(11) DEFAULT '0' COMMENT '总参赛羽数',
  `top100_count` int(11) DEFAULT '0' COMMENT '入赏羽数（≤100名）',
  `avg_speed` decimal(10,1) DEFAULT '0.0' COMMENT '平均分速',
  `max_speed` decimal(10,1) DEFAULT '0.0' COMMENT '最高分速',
  `computed_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '计算时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='城市公棚 TOP10 预计算表';

-- --------------------------------------------------------

--
-- 表的结构 `city_owner_rankings`
--

CREATE TABLE `city_owner_rankings` (
  `id` int(11) NOT NULL,
  `city` varchar(64) NOT NULL COMMENT '城市名',
  `rank_pos` tinyint(4) NOT NULL COMMENT '排名 1-10',
  `owner_name` varchar(128) DEFAULT '' COMMENT '鸽主姓名',
  `entry_count` int(11) DEFAULT '0' COMMENT '参赛羽数',
  `top100_count` int(11) DEFAULT '0' COMMENT '入赏次数（≤100名）',
  `best_speed` decimal(10,1) DEFAULT '0.0' COMMENT '最高分速',
  `avg_speed` decimal(10,1) DEFAULT '0.0' COMMENT '平均分速',
  `computed_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '计算时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='城市鸽主 TOP10 预计算表';

-- --------------------------------------------------------

--
-- 表的结构 `city_pigeon_rankings`
--

CREATE TABLE `city_pigeon_rankings` (
  `id` int(11) NOT NULL,
  `city` varchar(64) NOT NULL COMMENT '城市名',
  `rank_pos` tinyint(4) NOT NULL COMMENT '排名 1-10',
  `ring_number` varchar(64) DEFAULT '' COMMENT '足环号',
  `owner_name` varchar(128) DEFAULT '' COMMENT '鸽主姓名',
  `speed` decimal(10,1) DEFAULT '0.0' COMMENT '分速 m/min',
  `rank` int(11) DEFAULT '0' COMMENT '该场比赛排名',
  `race_name` varchar(255) DEFAULT '' COMMENT '赛事名称',
  `release_time` datetime DEFAULT NULL COMMENT '开笼时间',
  `distance_km` decimal(8,1) DEFAULT '0.0' COMMENT '空距 km',
  `loft_name` varchar(255) DEFAULT '' COMMENT '所属公棚',
  `loft_id` int(11) DEFAULT '0' COMMENT '公棚ID',
  `computed_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '计算时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='城市速度鸽 TOP10 预计算表';

-- --------------------------------------------------------

--
-- 表的结构 `claim_requests`
--

CREATE TABLE `claim_requests` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL COMMENT '申请人ID',
  `target_type` varchar(20) NOT NULL COMMENT '认领目标类型: shop/loft',
  `target_id` int(10) UNSIGNED NOT NULL COMMENT '认领目标ID',
  `real_name` varchar(50) NOT NULL COMMENT '真实姓名',
  `phone` varchar(20) NOT NULL COMMENT '联系电话',
  `wechat` varchar(50) DEFAULT NULL COMMENT '微信号',
  `evidence` text COMMENT '证明材料',
  `reason` varchar(500) NOT NULL COMMENT '申请理由',
  `status` tinyint(4) DEFAULT '0' COMMENT '状态: 0=待审核, 1=已通过, 2=已拒绝, 3=已取消',
  `admin_note` varchar(500) DEFAULT NULL COMMENT '管理员备注',
  `reviewed_at` datetime DEFAULT NULL COMMENT '审核时间',
  `reviewed_by` int(10) UNSIGNED DEFAULT NULL COMMENT '审核人ID',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商家认领申请表';

-- --------------------------------------------------------

--
-- 表的结构 `comments`
--

CREATE TABLE `comments` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL COMMENT '用户ID',
  `target_type` tinyint(4) NOT NULL COMMENT '目标类型: 1=文章, 2=铭鸽, 3=分类信息',
  `target_id` int(10) UNSIGNED NOT NULL COMMENT '目标ID',
  `content` varchar(500) NOT NULL COMMENT '评论内容',
  `parent_id` int(10) UNSIGNED DEFAULT '0' COMMENT '父评论ID',
  `likes` int(10) UNSIGNED DEFAULT '0' COMMENT '点赞数',
  `status` tinyint(4) DEFAULT '1' COMMENT '状态: 0=隐藏, 1=显示',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='评论表';

-- --------------------------------------------------------

--
-- 表的结构 `dynamics`
--

CREATE TABLE `dynamics` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL COMMENT '用户ID',
  `content` varchar(1000) NOT NULL COMMENT '内容',
  `images` text COMMENT '图片(JSON数组)',
  `likes` int(10) UNSIGNED DEFAULT '0' COMMENT '点赞数',
  `views` int(10) UNSIGNED DEFAULT '0' COMMENT '点赞数',
  `comments` int(10) UNSIGNED DEFAULT '0' COMMENT '评论数',
  `status` tinyint(4) DEFAULT '1' COMMENT '状态: 0=隐藏, 1=显示',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='动态表';

-- --------------------------------------------------------

--
-- 表的结构 `follows`
--

CREATE TABLE `follows` (
  `id` int(10) UNSIGNED NOT NULL,
  `follower_id` int(10) UNSIGNED NOT NULL COMMENT '关注者ID',
  `following_id` int(10) UNSIGNED NOT NULL COMMENT '被关注者ID',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='关注表';

-- --------------------------------------------------------

--
-- 表的结构 `likes`
--

CREATE TABLE `likes` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL COMMENT '用户ID',
  `target_type` tinyint(4) NOT NULL COMMENT '目标类型: 1=文章, 2=铭鸽, 3=分类信息, 4=评论',
  `target_id` int(10) UNSIGNED NOT NULL COMMENT '目标ID',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='点赞表';

-- --------------------------------------------------------

--
-- 表的结构 `listings`
--

CREATE TABLE `listings` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL COMMENT '发布者ID',
  `type` tinyint(4) NOT NULL COMMENT '类型: 1=鸽舍转让, 2=配对信息, 3=求购/转让',
  `title` varchar(255) NOT NULL COMMENT '标题',
  `description` text COMMENT '描述',
  `images` text COMMENT '图片(JSON数组)',
  `contact_name` varchar(50) DEFAULT NULL COMMENT '联系人',
  `contact_phone` varchar(20) DEFAULT NULL COMMENT '联系电话',
  `contact_wechat` varchar(50) DEFAULT NULL COMMENT '微信号',
  `price` decimal(10,2) DEFAULT NULL COMMENT '价格',
  `negotiable` tinyint(4) DEFAULT '0' COMMENT '价格面议: 0=否, 1=是',
  `location` varchar(100) DEFAULT NULL COMMENT '地区',
  `views` int(10) UNSIGNED DEFAULT '0' COMMENT '浏览数',
  `likes` int(10) UNSIGNED DEFAULT '0' COMMENT '点赞数',
  `status` tinyint(4) DEFAULT '0' COMMENT '状态: 0=待审核, 1=已通过, 2=已拒绝, 3=已下架, 4=已成交',
  `expire_at` datetime DEFAULT NULL COMMENT '过期时间',
  `reject_reason` varchar(255) DEFAULT NULL COMMENT '拒绝原因',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='分类信息表';

-- --------------------------------------------------------

--
-- 表的结构 `lofts`
--

CREATE TABLE `lofts` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL COMMENT '创建者/管理员ID',
  `source_id` varchar(20) DEFAULT NULL COMMENT '来源ID(中信网gp_id)',
  `name` varchar(100) NOT NULL COMMENT '公棚名称',
  `gp_id` varchar(20) DEFAULT NULL COMMENT '中信网公棚ID',
  `province` varchar(30) DEFAULT NULL COMMENT '省份',
  `city` varchar(30) DEFAULT NULL COMMENT '城市',
  `address` varchar(255) DEFAULT NULL COMMENT '详细地址',
  `contact_name` varchar(50) DEFAULT NULL COMMENT '联系人',
  `contact_phone` varchar(20) DEFAULT NULL COMMENT '联系电话',
  `logo` varchar(255) DEFAULT NULL COMMENT '公棚Logo图片路径',
  `photos` text COMMENT '公棚照片(JSON数组)',
  `description` text COMMENT '公棚简介',
  `capacity` int(10) UNSIGNED DEFAULT '0' COMMENT '收鸽容量(羽)',
  `current_count` int(10) UNSIGNED DEFAULT '0' COMMENT '当前收鸽数(羽)',
  `entry_fee` decimal(10,2) DEFAULT '0.00' COMMENT '参赛费(元/羽)',
  `management_fee` decimal(10,2) DEFAULT '0.00' COMMENT '饲养管理费(元/羽)',
  `prize_pool` decimal(12,2) DEFAULT '0.00' COMMENT '奖金池(元)',
  `prize_detail` text COMMENT '奖金分配明细(JSON)',
  `race_distance` int(10) UNSIGNED DEFAULT NULL COMMENT '比赛距离(公里)',
  `race_type` varchar(50) DEFAULT NULL COMMENT '比赛类型: 春棚/秋棚/特比',
  `collect_start` date DEFAULT NULL COMMENT '收鸽开始日期',
  `collect_end` date DEFAULT NULL COMMENT '收鸽截止日期',
  `training_start` date DEFAULT NULL COMMENT '训放开始日期',
  `race_date` date DEFAULT NULL COMMENT '比赛日期',
  `rules` text COMMENT '参赛规程',
  `facilities` text COMMENT '公棚设施介绍',
  `rating` tinyint(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '综合评分(0-10)',
  `rating_count` int(10) UNSIGNED DEFAULT '0' COMMENT '评分人数',
  `views` int(10) UNSIGNED DEFAULT '0' COMMENT '浏览数',
  `is_certified` tinyint(4) DEFAULT '0' COMMENT '是否认证公棚: 0=未认证, 1=已认证',
  `is_hot` tinyint(4) DEFAULT '0' COMMENT '是否热门公棚',
  `status` tinyint(4) DEFAULT '0' COMMENT '状态: 0=待审核, 1=正常, 2=已关闭, 3=已取消',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `source_url` varchar(500) DEFAULT NULL COMMENT '中信网原链接',
  `wechat` varchar(100) DEFAULT NULL COMMENT '微信公众号',
  `website` varchar(255) DEFAULT NULL COMMENT '官方网站',
  `lat` decimal(10,6) DEFAULT NULL COMMENT '纬度',
  `lng` decimal(10,6) DEFAULT NULL COMMENT '经度'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='公棚表';

-- --------------------------------------------------------

--
-- 表的结构 `loft_entries`
--

CREATE TABLE `loft_entries` (
  `id` int(10) UNSIGNED NOT NULL,
  `loft_id` int(10) UNSIGNED NOT NULL COMMENT '公棚ID',
  `user_id` int(10) UNSIGNED NOT NULL COMMENT '交鸽鸽友ID',
  `pigeon_ring` varchar(50) NOT NULL COMMENT '足环号',
  `pigeon_name` varchar(50) DEFAULT NULL COMMENT '鸽子名称',
  `pigeon_color` varchar(30) DEFAULT NULL COMMENT '羽色',
  `entry_fee_paid` tinyint(4) DEFAULT '0' COMMENT '参赛费是否已付: 0=未付, 1=已付',
  `management_fee_paid` tinyint(4) DEFAULT '0' COMMENT '管理费是否已付: 0=未付, 1=已付',
  `status` tinyint(4) DEFAULT '1' COMMENT '状态: 0=已退出, 1=已入棚, 2=训放中, 3=已参赛, 4=已归巢, 5=未归巢',
  `result_rank` int(10) UNSIGNED DEFAULT NULL COMMENT '比赛名次',
  `result_speed` decimal(10,2) DEFAULT NULL COMMENT '飞行速度(米/分)',
  `prize_amount` decimal(10,2) DEFAULT NULL COMMENT '获奖金额',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='公棚参赛记录表';

-- --------------------------------------------------------

--
-- 表的结构 `loft_news`
--

CREATE TABLE `loft_news` (
  `id` int(10) UNSIGNED NOT NULL,
  `loft_id` int(10) UNSIGNED NOT NULL COMMENT '公棚ID',
  `title` varchar(255) NOT NULL COMMENT '标题',
  `url` varchar(500) DEFAULT NULL COMMENT '原始链接',
  `summary` text COMMENT '摘要',
  `published_at` date DEFAULT NULL COMMENT '发布日期',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='公棚动态/公告表';

-- --------------------------------------------------------

--
-- 表的结构 `loft_photos`
--

CREATE TABLE `loft_photos` (
  `id` int(10) UNSIGNED NOT NULL,
  `loft_id` int(10) UNSIGNED NOT NULL COMMENT '公棚ID',
  `photo_url` varchar(500) NOT NULL COMMENT '图片URL',
  `category` varchar(30) DEFAULT 'other' COMMENT '分类: loft/pigeon/award/training/other',
  `sort_order` int(11) DEFAULT '0' COMMENT '排序',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='公棚相册表';

-- --------------------------------------------------------

--
-- 表的结构 `loft_reviews`
--

CREATE TABLE `loft_reviews` (
  `id` int(10) UNSIGNED NOT NULL,
  `loft_id` int(10) UNSIGNED NOT NULL COMMENT '公棚ID',
  `user_id` int(10) UNSIGNED NOT NULL COMMENT '评价用户ID',
  `rating` tinyint(4) NOT NULL COMMENT '评分(1-10)',
  `content` varchar(500) DEFAULT NULL COMMENT '评价内容',
  `is_anonymous` tinyint(4) DEFAULT '0' COMMENT '是否匿名评价',
  `status` tinyint(4) DEFAULT '1' COMMENT '状态: 0=隐藏, 1=显示',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='公棚评价表';

-- --------------------------------------------------------

--
-- 表的结构 `member_orders`
--

CREATE TABLE `member_orders` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL COMMENT '用户ID',
  `order_no` varchar(50) NOT NULL COMMENT '订单号',
  `from_level` tinyint(4) NOT NULL COMMENT '原等级',
  `to_level` tinyint(4) NOT NULL COMMENT '新等级',
  `plan_type` tinyint(4) NOT NULL COMMENT '套餐类型: 1=月费, 2=年费',
  `months` int(11) NOT NULL COMMENT '月数',
  `product_type` varchar(20) NOT NULL DEFAULT 'membership' COMMENT '产品类型: membership/report',
  `product_ref` varchar(200) DEFAULT NULL COMMENT '产品引用: 足环号等',
  `amount` decimal(10,2) NOT NULL COMMENT '金额',
  `payment_method` varchar(20) DEFAULT NULL COMMENT '支付方式',
  `payment_no` varchar(100) DEFAULT NULL COMMENT '支付流水号',
  `status` tinyint(4) DEFAULT '0' COMMENT '状态: 0=待支付, 1=已支付, 2=已取消, 3=已退款',
  `paid_at` datetime DEFAULT NULL COMMENT '支付时间',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='会员订单表';

-- --------------------------------------------------------

--
-- 表的结构 `member_plans`
--

CREATE TABLE `member_plans` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(20) NOT NULL COMMENT '套餐名称',
  `slug` varchar(20) NOT NULL COMMENT '标识',
  `level` tinyint(4) NOT NULL COMMENT '等级值',
  `monthly_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '月费',
  `yearly_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '年费',
  `article_limit` int(11) DEFAULT '0' COMMENT '每月文章数(0=不限)',
  `pigeon_limit` int(11) DEFAULT '0' COMMENT '铭鸽数量(0=不限)',
  `listing_limit` int(11) DEFAULT '0' COMMENT '分类信息数/月',
  `dynamic_daily_limit` int(11) DEFAULT '3' COMMENT '动态数/天',
  `can_view_contact` tinyint(4) DEFAULT '0' COMMENT '能否查看联系方式',
  `can_top_content` tinyint(4) DEFAULT '0' COMMENT '能否置顶内容',
  `top_monthly` int(11) DEFAULT '0' COMMENT '置顶次数/月',
  `can_recommend` tinyint(4) DEFAULT '0' COMMENT '能否推荐',
  `recommend_weekly` int(11) DEFAULT '0' COMMENT '推荐次数/周',
  `no_ads` tinyint(4) DEFAULT '0' COMMENT '是否去广告',
  `can_certify` tinyint(4) DEFAULT '0' COMMENT '能否认证',
  `priority_audit` tinyint(4) DEFAULT '0' COMMENT '优先审核',
  `sort_order` int(11) DEFAULT '0' COMMENT '排序',
  `status` tinyint(4) DEFAULT '1' COMMENT '状态: 0=禁用, 1=启用',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='会员套餐表';

-- --------------------------------------------------------

--
-- 表的结构 `member_product_unlocks`
--

CREATE TABLE `member_product_unlocks` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_type` varchar(20) NOT NULL COMMENT 'certificate|compare',
  `product_ref` varchar(100) NOT NULL COMMENT 'cert_id æˆ– loft_ids',
  `order_id` int(11) NOT NULL DEFAULT '0',
  `paid_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `member_race_unlocks`
--

CREATE TABLE `member_race_unlocks` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL COMMENT '用户ID',
  `ring` varchar(100) NOT NULL COMMENT '足环号',
  `order_id` int(10) UNSIGNED DEFAULT NULL COMMENT '关联订单ID',
  `paid_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '支付时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='足环号报告解锁记录';

-- --------------------------------------------------------

--
-- 表的结构 `orders`
--

CREATE TABLE `orders` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_no` varchar(50) NOT NULL COMMENT '订单号',
  `user_id` int(10) UNSIGNED NOT NULL COMMENT '用户ID',
  `product_type` tinyint(4) NOT NULL COMMENT '产品类型: 1=会员, 2=内容置顶, 3=推荐位, 4=认证服务',
  `product_name` varchar(100) DEFAULT NULL COMMENT '产品名称',
  `target_id` int(10) UNSIGNED DEFAULT NULL COMMENT '关联目标ID',
  `duration_days` int(11) DEFAULT '1' COMMENT '持续天数',
  `amount` decimal(10,2) NOT NULL COMMENT '金额',
  `months` int(11) DEFAULT NULL COMMENT '会员月数',
  `status` tinyint(4) DEFAULT '0' COMMENT '状态: 0=待支付, 1=已支付, 2=已取消',
  `payment_method` varchar(20) DEFAULT NULL COMMENT '支付方式',
  `payment_at` datetime DEFAULT NULL COMMENT '支付时间',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='订单表';

-- --------------------------------------------------------

--
-- 表的结构 `payments`
--

CREATE TABLE `payments` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_no` varchar(64) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `plan_id` int(10) UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `pay_type` tinyint(4) DEFAULT '0' COMMENT '0微信 1支付宝',
  `status` tinyint(4) DEFAULT '0' COMMENT '0待支付 1已支付 2已退款 3已过期',
  `trade_no` varchar(128) DEFAULT NULL,
  `pay_time` datetime DEFAULT NULL,
  `expire_time` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='支付订单表';

-- --------------------------------------------------------

--
-- 表的结构 `pigeons`
--

CREATE TABLE `pigeons` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL COMMENT '发布者ID',
  `shop_id` int(10) UNSIGNED DEFAULT NULL COMMENT '所属展厅ID',
  `source_id` varchar(20) DEFAULT NULL COMMENT '来源ID',
  `category_id` int(10) UNSIGNED DEFAULT NULL COMMENT '分类ID',
  `name` varchar(100) NOT NULL COMMENT '鸽子名称',
  `ring_number` varchar(50) DEFAULT NULL COMMENT '足环号',
  `bloodline` varchar(100) DEFAULT NULL COMMENT '血统',
  `strain_id` int(10) UNSIGNED DEFAULT NULL COMMENT '品系ID',
  `gender` tinyint(4) DEFAULT NULL COMMENT '性别: 0=未知, 1=雄, 2=雌',
  `birth_date` date DEFAULT NULL COMMENT '出生日期',
  `color` varchar(50) DEFAULT NULL COMMENT '羽色',
  `eye_color` varchar(50) DEFAULT NULL COMMENT '眼砂',
  `description` text COMMENT '描述',
  `images` longtext,
  `video` varchar(255) DEFAULT NULL COMMENT '视频链接',
  `achievements` text COMMENT '比赛成绩(JSON)',
  `pedigree` text COMMENT '血统书(JSON)',
  `views` int(10) UNSIGNED DEFAULT '0' COMMENT '浏览数',
  `likes` int(10) UNSIGNED DEFAULT '0' COMMENT '点赞数',
  `comments` int(10) UNSIGNED DEFAULT '0' COMMENT '评论数',
  `is_top` tinyint(4) DEFAULT '0' COMMENT '是否置顶',
  `is_recommend` tinyint(4) DEFAULT '0' COMMENT '是否推荐',
  `status` tinyint(4) DEFAULT '0' COMMENT '状态: 0=待审核, 1=已通过, 2=已拒绝, 3=已下架',
  `reject_reason` varchar(255) DEFAULT NULL COMMENT '拒绝原因',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='铭鸽展示表';

-- --------------------------------------------------------

--
-- 表的结构 `pigeon_pairings`
--

CREATE TABLE `pigeon_pairings` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `male_id` int(10) UNSIGNED NOT NULL,
  `female_id` int(10) UNSIGNED NOT NULL,
  `notes` text,
  `status` tinyint(4) DEFAULT '0' COMMENT '0计划 1已配 2有后代',
  `children_ids` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='配对记录表';

-- --------------------------------------------------------

--
-- 表的结构 `pigeon_parents`
--

CREATE TABLE `pigeon_parents` (
  `id` int(10) UNSIGNED NOT NULL,
  `pigeon_id` int(10) UNSIGNED NOT NULL,
  `father_id` int(10) UNSIGNED DEFAULT NULL,
  `mother_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='铭鸽血统关系表';

-- --------------------------------------------------------

--
-- 表的结构 `pigeon_strains`
--

CREATE TABLE `pigeon_strains` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL COMMENT '品系名称',
  `slug` varchar(50) NOT NULL COMMENT 'URL友好名',
  `pigeon_count` int(10) UNSIGNED DEFAULT '0' COMMENT '关联铭鸽数量',
  `description` text COMMENT '品系简介',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='血统品系表';

-- --------------------------------------------------------

--
-- 表的结构 `promotions`
--

CREATE TABLE `promotions` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL COMMENT '用户ID',
  `target_type` varchar(20) NOT NULL COMMENT '目标类型: article/pigeon/listing',
  `target_id` int(10) UNSIGNED NOT NULL COMMENT '目标ID',
  `promotion_type` tinyint(4) NOT NULL COMMENT '推广类型: 1=置顶, 2=推荐',
  `start_at` datetime NOT NULL COMMENT '开始时间',
  `end_at` datetime NOT NULL COMMENT '结束时间',
  `status` tinyint(4) DEFAULT '1' COMMENT '状态: 0=未开始, 1=进行中, 2=已结束',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='内容推广记录表';

-- --------------------------------------------------------

--
-- 表的结构 `publish_stats`
--

CREATE TABLE `publish_stats` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL COMMENT '用户ID',
  `stat_month` varchar(7) NOT NULL COMMENT '统计月份(如2026-05)',
  `article_count` int(11) DEFAULT '0' COMMENT '本月已发文章数',
  `pigeon_count` int(11) DEFAULT '0' COMMENT '本月已发铭鸽数',
  `listing_count` int(11) DEFAULT '0' COMMENT '本月已发分类信息数',
  `dynamic_count` int(11) DEFAULT '0' COMMENT '今日已发动态数',
  `top_used` int(11) DEFAULT '0' COMMENT '本月已用置顶次数',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='发布额度统计表';

-- --------------------------------------------------------

--
-- 表的结构 `races`
--

CREATE TABLE `races` (
  `id` int(10) UNSIGNED NOT NULL,
  `loft_id` int(10) UNSIGNED NOT NULL,
  `source_id` varchar(20) DEFAULT NULL COMMENT '中信网赛事ID',
  `name` varchar(200) NOT NULL,
  `release_location` varchar(200) DEFAULT NULL,
  `distance_km` decimal(8,2) DEFAULT NULL,
  `participant_count` int(11) DEFAULT NULL,
  `result_count` int(11) DEFAULT '0',
  `entry_count` int(10) UNSIGNED DEFAULT '0',
  `returned_count` int(10) UNSIGNED DEFAULT '0',
  `return_rate` decimal(5,2) DEFAULT NULL,
  `release_time` datetime DEFAULT NULL,
  `race_category` enum('final','pre','train','toll','other') DEFAULT 'other',
  `season_year` year(4) DEFAULT NULL,
  `season_type` enum('spring','autumn','other') DEFAULT 'other',
  `status` tinyint(4) DEFAULT '1',
  `data_source` enum('crawl','manual','sigoran') DEFAULT 'crawl',
  `source_url` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `city` varchar(50) DEFAULT NULL,
  `province` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='公棚赛事表';

-- --------------------------------------------------------

--
-- 表的结构 `race_results`
--

CREATE TABLE `race_results` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `race_id` int(10) UNSIGNED NOT NULL,
  `rank` int(10) UNSIGNED NOT NULL,
  `owner_name` varchar(100) DEFAULT NULL,
  `region` varchar(50) DEFAULT NULL,
  `ring_number` varchar(30) DEFAULT NULL,
  `color` varchar(20) DEFAULT NULL,
  `arrival_time` datetime(3) DEFAULT NULL,
  `speed` double DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='公棚赛事成绩明细';

-- --------------------------------------------------------

--
-- 表的结构 `settings`
--

CREATE TABLE `settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `key` varchar(50) NOT NULL COMMENT '设置键',
  `value` text COMMENT '设置值',
  `description` varchar(255) DEFAULT NULL COMMENT '说明',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统设置表';

-- --------------------------------------------------------

--
-- 表的结构 `shops`
--

CREATE TABLE `shops` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT '0' COMMENT '认领用户ID，0=未认领',
  `source_id` varchar(20) DEFAULT NULL COMMENT '来源ID(中信网shop_id)',
  `name` varchar(100) NOT NULL COMMENT '展厅名称',
  `avatar` varchar(255) DEFAULT NULL COMMENT '展厅头像/Logo',
  `province` varchar(30) DEFAULT NULL COMMENT '省份',
  `city` varchar(30) DEFAULT NULL COMMENT '城市',
  `address` varchar(255) DEFAULT NULL COMMENT '详细地址',
  `contact_name` varchar(50) DEFAULT NULL COMMENT '联系人',
  `contact_phone` varchar(20) DEFAULT NULL COMMENT '联系电话',
  `description` text COMMENT '展厅简介',
  `website` varchar(255) DEFAULT NULL COMMENT '独立域名/网址',
  `model` varchar(10) DEFAULT NULL COMMENT '模板编号',
  `views` int(10) UNSIGNED DEFAULT '0' COMMENT '浏览量',
  `pigeon_count` int(10) UNSIGNED DEFAULT '0' COMMENT '展品数量',
  `is_certified` tinyint(4) DEFAULT '0' COMMENT '是否认证',
  `is_hot` tinyint(4) DEFAULT '0' COMMENT '是否热门',
  `status` tinyint(4) DEFAULT '0' COMMENT '状态: 0=待审核, 1=正常, 2=已关闭',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='铭鸽展厅表';

-- --------------------------------------------------------

--
-- 表的结构 `shop_categories`
--

CREATE TABLE `shop_categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `shop_id` int(10) UNSIGNED NOT NULL COMMENT '展厅ID',
  `source_id` varchar(20) DEFAULT NULL COMMENT '来源分类ID',
  `name` varchar(100) NOT NULL COMMENT '分类名称',
  `pigeon_count` int(10) UNSIGNED DEFAULT '0' COMMENT '该分类下展品数',
  `sort` int(11) DEFAULT '0' COMMENT '排序',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='展厅血系分类表';

-- --------------------------------------------------------

--
-- 表的结构 `tags`
--

CREATE TABLE `tags` (
  `id` int(11) NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('topic','strain','city','season') COLLATE utf8mb4_unicode_ci DEFAULT 'topic',
  `article_count` int(10) UNSIGNED DEFAULT '0' COMMENT '文章数量缓存',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `top100_rankings`
--

CREATE TABLE `top100_rankings` (
  `id` int(10) UNSIGNED NOT NULL,
  `rank_pos` tinyint(3) UNSIGNED NOT NULL COMMENT '排名 1~100',
  `ring_number` varchar(50) NOT NULL DEFAULT '' COMMENT '足环号',
  `owner_name` varchar(100) NOT NULL DEFAULT '' COMMENT '鸽主',
  `speed` decimal(15,4) NOT NULL COMMENT '分速（米/分）',
  `race_name` varchar(200) NOT NULL DEFAULT '' COMMENT '赛事名称',
  `distance_km` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '空距（公里）',
  `release_time` datetime DEFAULT NULL COMMENT '开笼时间',
  `race_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '赛事ID',
  `loft_name` varchar(200) NOT NULL DEFAULT '' COMMENT '公棚名',
  `loft_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '公棚ID',
  `stats_birds` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '参赛鸽数',
  `stats_owners` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '参赛鸽主',
  `stats_lofts` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '参赛公棚',
  `stats_max_spd` decimal(15,4) NOT NULL DEFAULT '0.0000' COMMENT '最高分速',
  `stats_avg_spd` decimal(15,4) NOT NULL DEFAULT '0.0000' COMMENT '平均分速',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL COMMENT '用户名',
  `password` varchar(255) NOT NULL COMMENT '密码(加密)',
  `email` varchar(100) DEFAULT NULL COMMENT '邮箱',
  `nickname` varchar(50) DEFAULT NULL COMMENT '昵称',
  `avatar` varchar(255) DEFAULT '/public/assets/images/default-avatar.png' COMMENT '头像',
  `phone` varchar(20) DEFAULT NULL COMMENT '手机号',
  `member_level` tinyint(4) DEFAULT '0' COMMENT '会员等级: 0=免费, 1=铜牌, 2=银牌, 3=金牌',
  `member_expire_at` datetime DEFAULT NULL COMMENT '会员到期时间',
  `balance` decimal(10,2) DEFAULT '0.00' COMMENT '账户余额',
  `status` tinyint(4) DEFAULT '1' COMMENT '状态: 0=禁用, 1=正常',
  `role` varchar(20) DEFAULT 'user' COMMENT '角色: admin=管理员, user=普通用户',
  `last_login_at` datetime DEFAULT NULL COMMENT '最后登录时间',
  `last_login_ip` varchar(50) DEFAULT NULL COMMENT '最后登录IP',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户表';

--
-- 转储表的索引
--

--
-- 表的索引 `advertisements`
--
ALTER TABLE `advertisements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_position` (`position`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_start_at` (`start_at`),
  ADD KEY `idx_end_at` (`end_at`);

--
-- 表的索引 `articles`
--
ALTER TABLE `articles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_category_id` (`category_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_is_top` (`is_top`),
  ADD KEY `idx_is_recommend` (`is_recommend`),
  ADD KEY `idx_published_at` (`published_at`);

--
-- 表的索引 `article_tags`
--
ALTER TABLE `article_tags`
  ADD PRIMARY KEY (`article_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- 表的索引 `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_slug` (`slug`),
  ADD KEY `idx_parent_id` (`parent_id`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_status` (`status`);

--
-- 表的索引 `city_loft_rankings`
--
ALTER TABLE `city_loft_rankings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_city_loft_rank` (`city`,`rank_pos`),
  ADD KEY `idx_city` (`city`),
  ADD KEY `idx_computed_at` (`computed_at`);

--
-- 表的索引 `city_owner_rankings`
--
ALTER TABLE `city_owner_rankings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_city_owner_rank` (`city`,`rank_pos`),
  ADD KEY `idx_city` (`city`),
  ADD KEY `idx_computed_at` (`computed_at`);

--
-- 表的索引 `city_pigeon_rankings`
--
ALTER TABLE `city_pigeon_rankings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_city_pigeon_rank` (`city`,`rank_pos`),
  ADD KEY `idx_city` (`city`),
  ADD KEY `idx_computed_at` (`computed_at`);

--
-- 表的索引 `claim_requests`
--
ALTER TABLE `claim_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_target` (`target_type`,`target_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created` (`created_at`);

--
-- 表的索引 `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_target` (`target_type`,`target_id`),
  ADD KEY `idx_parent_id` (`parent_id`);

--
-- 表的索引 `dynamics`
--
ALTER TABLE `dynamics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- 表的索引 `follows`
--
ALTER TABLE `follows`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_follow` (`follower_id`,`following_id`),
  ADD KEY `idx_follower_id` (`follower_id`),
  ADD KEY `idx_following_id` (`following_id`);

--
-- 表的索引 `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_user_target` (`user_id`,`target_type`,`target_id`),
  ADD KEY `idx_target` (`target_type`,`target_id`);

--
-- 表的索引 `listings`
--
ALTER TABLE `listings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_location` (`location`),
  ADD KEY `idx_expire_at` (`expire_at`);

--
-- 表的索引 `lofts`
--
ALTER TABLE `lofts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_province` (`province`),
  ADD KEY `idx_city` (`city`),
  ADD KEY `idx_race_type` (`race_type`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_is_certified` (`is_certified`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_source_id` (`source_id`),
  ADD KEY `idx_gp_id` (`gp_id`);

--
-- 表的索引 `loft_entries`
--
ALTER TABLE `loft_entries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_loft_id` (`loft_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_pigeon_ring` (`pigeon_ring`),
  ADD KEY `idx_status` (`status`);

--
-- 表的索引 `loft_news`
--
ALTER TABLE `loft_news`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_loft_id` (`loft_id`),
  ADD KEY `idx_published` (`published_at`);

--
-- 表的索引 `loft_photos`
--
ALTER TABLE `loft_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_loft_id` (`loft_id`),
  ADD KEY `idx_category` (`category`);

--
-- 表的索引 `loft_reviews`
--
ALTER TABLE `loft_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_loft_id` (`loft_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- 表的索引 `member_orders`
--
ALTER TABLE `member_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_order_no` (`order_no`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`);

--
-- 表的索引 `member_plans`
--
ALTER TABLE `member_plans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_level` (`level`),
  ADD UNIQUE KEY `uk_slug` (`slug`);

--
-- 表的索引 `member_product_unlocks`
--
ALTER TABLE `member_product_unlocks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_user_product` (`user_id`,`product_type`,`product_ref`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- 表的索引 `member_race_unlocks`
--
ALTER TABLE `member_race_unlocks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_user_ring` (`user_id`,`ring`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- 表的索引 `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_order_no` (`order_no`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`);

--
-- 表的索引 `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_order` (`order_no`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_status` (`status`);

--
-- 表的索引 `pigeons`
--
ALTER TABLE `pigeons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_category_id` (`category_id`),
  ADD KEY `idx_ring_number` (`ring_number`),
  ADD KEY `idx_bloodline` (`bloodline`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_shop_id` (`shop_id`),
  ADD KEY `idx_source_id` (`source_id`),
  ADD KEY `idx_strain` (`strain_id`);

--
-- 表的索引 `pigeon_pairings`
--
ALTER TABLE `pigeon_pairings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_male` (`male_id`),
  ADD KEY `idx_female` (`female_id`);

--
-- 表的索引 `pigeon_parents`
--
ALTER TABLE `pigeon_parents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_pigeon` (`pigeon_id`),
  ADD KEY `idx_father` (`father_id`),
  ADD KEY `idx_mother` (`mother_id`);

--
-- 表的索引 `pigeon_strains`
--
ALTER TABLE `pigeon_strains`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_slug` (`slug`),
  ADD KEY `idx_name` (`name`);

--
-- 表的索引 `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_target` (`target_type`,`target_id`),
  ADD KEY `idx_end_at` (`end_at`);

--
-- 表的索引 `publish_stats`
--
ALTER TABLE `publish_stats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_user_month` (`user_id`,`stat_month`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- 表的索引 `races`
--
ALTER TABLE `races`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_loft` (`loft_id`),
  ADD KEY `idx_season` (`season_year`,`season_type`),
  ADD KEY `idx_source` (`source_id`),
  ADD KEY `idx_category` (`race_category`),
  ADD KEY `idx_status_rel` (`status`,`release_time`),
  ADD KEY `idx_city` (`city`);

--
-- 表的索引 `race_results`
--
ALTER TABLE `race_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ring` (`ring_number`),
  ADD KEY `idx_rank_only` (`rank`),
  ADD KEY `idx_speed_only` (`speed`),
  ADD KEY `idx_owner_name` (`owner_name`),
  ADD KEY `idx_race_id_speed` (`race_id`,`speed`),
  ADD KEY `idx_speed_filter` (`speed`,`rank`),
  ADD KEY `idx_race_id` (`race_id`),
  ADD KEY `idx_race_id_rank` (`race_id`,`rank`),
  ADD KEY `idx_race_id_owner` (`race_id`,`owner_name`),
  ADD KEY `idx_race_id_speed_rank` (`race_id`,`speed`,`rank`),
  ADD KEY `idx_race_id_owner_speed` (`race_id`,`owner_name`,`speed`,`rank`);

--
-- 表的索引 `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_key` (`key`);

--
-- 表的索引 `shops`
--
ALTER TABLE `shops`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_source_id` (`source_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_province` (`province`),
  ADD KEY `idx_status` (`status`);

--
-- 表的索引 `shop_categories`
--
ALTER TABLE `shop_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_shop_id` (`shop_id`),
  ADD KEY `idx_source_id` (`source_id`);

--
-- 表的索引 `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- 表的索引 `top100_rankings`
--
ALTER TABLE `top100_rankings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_rank` (`rank_pos`);

--
-- 表的索引 `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_username` (`username`),
  ADD KEY `idx_member_level` (`member_level`),
  ADD KEY `idx_status` (`status`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `advertisements`
--
ALTER TABLE `advertisements`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `articles`
--
ALTER TABLE `articles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `city_loft_rankings`
--
ALTER TABLE `city_loft_rankings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `city_owner_rankings`
--
ALTER TABLE `city_owner_rankings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `city_pigeon_rankings`
--
ALTER TABLE `city_pigeon_rankings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `claim_requests`
--
ALTER TABLE `claim_requests`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `dynamics`
--
ALTER TABLE `dynamics`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `follows`
--
ALTER TABLE `follows`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `likes`
--
ALTER TABLE `likes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `listings`
--
ALTER TABLE `listings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `lofts`
--
ALTER TABLE `lofts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `loft_entries`
--
ALTER TABLE `loft_entries`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `loft_news`
--
ALTER TABLE `loft_news`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `loft_photos`
--
ALTER TABLE `loft_photos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `loft_reviews`
--
ALTER TABLE `loft_reviews`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `member_orders`
--
ALTER TABLE `member_orders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `member_plans`
--
ALTER TABLE `member_plans`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `member_product_unlocks`
--
ALTER TABLE `member_product_unlocks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `member_race_unlocks`
--
ALTER TABLE `member_race_unlocks`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `pigeons`
--
ALTER TABLE `pigeons`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `pigeon_pairings`
--
ALTER TABLE `pigeon_pairings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `pigeon_parents`
--
ALTER TABLE `pigeon_parents`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `pigeon_strains`
--
ALTER TABLE `pigeon_strains`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `promotions`
--
ALTER TABLE `promotions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `publish_stats`
--
ALTER TABLE `publish_stats`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `races`
--
ALTER TABLE `races`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `race_results`
--
ALTER TABLE `race_results`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `shops`
--
ALTER TABLE `shops`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `shop_categories`
--
ALTER TABLE `shop_categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `top100_rankings`
--
ALTER TABLE `top100_rankings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 限制导出的表
--

--
-- 限制表 `pigeon_parents`
--
ALTER TABLE `pigeon_parents`
  ADD CONSTRAINT `fk_pp_child` FOREIGN KEY (`pigeon_id`) REFERENCES `pigeons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pp_father` FOREIGN KEY (`father_id`) REFERENCES `pigeons` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_pp_mother` FOREIGN KEY (`mother_id`) REFERENCES `pigeons` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
