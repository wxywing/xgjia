-- 信鸽之家数据库设计
-- 数据库名: xgjia
-- 字符集: utf8mb4
-- 存储引擎: InnoDB

CREATE DATABASE IF NOT EXISTS `xgjia` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `xgjia`;

-- --------------------------------------------------------
-- 1. 用户表 (users)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL COMMENT '用户名',
  `password` varchar(255) NOT NULL COMMENT '密码(加密)',
  `email` varchar(100) DEFAULT NULL COMMENT '邮箱',
  `phone` varchar(20) DEFAULT NULL COMMENT '手机号',
  `avatar` varchar(255) DEFAULT NULL COMMENT '头像',
  `nickname` varchar(50) DEFAULT NULL COMMENT '昵称',
  `realname` varchar(50) DEFAULT NULL COMMENT '真实姓名',
  `gender` tinyint(1) DEFAULT 0 COMMENT '性别: 0未知 1男 2女',
  `birthday` date DEFAULT NULL COMMENT '生日',
  `province` varchar(50) DEFAULT NULL COMMENT '省份',
  `city` varchar(50) DEFAULT NULL COMMENT '城市',
  `address` varchar(255) DEFAULT NULL COMMENT '详细地址',
  `bio` varchar(500) DEFAULT NULL COMMENT '个人签名',
  `role` tinyint(2) DEFAULT 1 COMMENT '角色: 1普通用户 2编辑 3管理员',
  `status` tinyint(1) DEFAULT 1 COMMENT '状态: 0禁用 1正常',
  `points` int(11) DEFAULT 0 COMMENT '积分',
  `balance` decimal(10,2) DEFAULT 0.00 COMMENT '余额',
  `last_login_ip` varchar(50) DEFAULT NULL COMMENT '最后登录IP',
  `last_login_time` datetime DEFAULT NULL COMMENT '最后登录时间',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '注册时间',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `phone` (`phone`),
  KEY `idx_status` (`status`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户表';

-- --------------------------------------------------------
-- 2. 分类表 (categories)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '分类名称',
  `parent_id` int(11) UNSIGNED DEFAULT 0 COMMENT '父级ID',
  `level` tinyint(1) DEFAULT 1 COMMENT '层级: 1一级 2二级 3三级',
  `type` varchar(20) DEFAULT 'article' COMMENT '类型: article文章 product商品 pigeon鸽子',
  `icon` varchar(255) DEFAULT NULL COMMENT '图标',
  `sort` int(11) DEFAULT 0 COMMENT '排序',
  `status` tinyint(1) DEFAULT 1 COMMENT '状态: 0隐藏 1显示',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_parent` (`parent_id`),
  KEY `idx_type` (`type`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='分类表';

-- --------------------------------------------------------
-- 3. 文章表 (articles)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `articles` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL COMMENT '标题',
  `category_id` int(11) UNSIGNED NOT NULL COMMENT '分类ID',
  `user_id` int(11) UNSIGNED NOT NULL COMMENT '作者ID',
  `cover` varchar(255) DEFAULT NULL COMMENT '封面图',
  `summary` varchar(500) DEFAULT NULL COMMENT '摘要',
  `content` longtext NOT NULL COMMENT '内容',
  `source` varchar(100) DEFAULT NULL COMMENT '来源',
  `tags` varchar(255) DEFAULT NULL COMMENT '标签(逗号分隔)',
  `views` int(11) DEFAULT 0 COMMENT '浏览量',
  `likes` int(11) DEFAULT 0 COMMENT '点赞数',
  `comments` int(11) DEFAULT 0 COMMENT '评论数',
  `is_top` tinyint(1) DEFAULT 0 COMMENT '是否置顶: 0否 1是',
  `is_recommend` tinyint(1) DEFAULT 0 COMMENT '是否推荐: 0否 1是',
  `status` tinyint(1) DEFAULT 1 COMMENT '状态: 0草稿 1发布 2下架',
  `published_at` datetime DEFAULT NULL COMMENT '发布时间',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_top` (`is_top`),
  KEY `idx_recommend` (`is_recommend`),
  KEY `idx_created` (`created_at`),
  FULLTEXT KEY `ft_title_content` (`title`, `content`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文章表';

-- --------------------------------------------------------
-- 4. 商品表 (products)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL COMMENT '商品名称',
  `category_id` int(11) UNSIGNED NOT NULL COMMENT '分类ID',
  `user_id` int(11) UNSIGNED NOT NULL COMMENT '发布者ID',
  `cover` varchar(255) DEFAULT NULL COMMENT '封面图',
  `images` text DEFAULT NULL COMMENT '商品图片(JSON数组)',
  `price` decimal(10,2) NOT NULL COMMENT '价格',
  `original_price` decimal(10,2) DEFAULT NULL COMMENT '原价',
  `stock` int(11) DEFAULT 0 COMMENT '库存',
  `description` longtext DEFAULT NULL COMMENT '商品描述',
  `specs` text DEFAULT NULL COMMENT '规格参数(JSON)',
  `location` varchar(100) DEFAULT NULL COMMENT '所在地',
  `is_new` tinyint(1) DEFAULT 1 COMMENT '是否全新: 0否 1是',
  `views` int(11) DEFAULT 0 COMMENT '浏览量',
  `likes` int(11) DEFAULT 0 COMMENT '收藏数',
  `sales` int(11) DEFAULT 0 COMMENT '销量',
  `status` tinyint(1) DEFAULT 1 COMMENT '状态: 0下架 1在售 2售罄',
  `is_top` tinyint(1) DEFAULT 0 COMMENT '是否置顶',
  `is_recommend` tinyint(1) DEFAULT 0 COMMENT '是否推荐',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_price` (`price`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商品表';

-- --------------------------------------------------------
-- 5. 订单表 (orders)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_no` varchar(50) NOT NULL COMMENT '订单号',
  `user_id` int(11) UNSIGNED NOT NULL COMMENT '买家ID',
  `seller_id` int(11) UNSIGNED NOT NULL COMMENT '卖家ID',
  `total_amount` decimal(10,2) NOT NULL COMMENT '总金额',
  `discount_amount` decimal(10,2) DEFAULT 0.00 COMMENT '优惠金额',
  `pay_amount` decimal(10,2) NOT NULL COMMENT '实付金额',
  `shipping_fee` decimal(10,2) DEFAULT 0.00 COMMENT '运费',
  `status` tinyint(2) DEFAULT 1 COMMENT '状态: 1待付款 2待发货 3待收货 4已完成 5已取消 6退款中',
  `pay_type` varchar(20) DEFAULT NULL COMMENT '支付方式: alipay wechat bank',
  `pay_time` datetime DEFAULT NULL COMMENT '支付时间',
  `shipping_time` datetime DEFAULT NULL COMMENT '发货时间',
  `receive_time` datetime DEFAULT NULL COMMENT '收货时间',
  `express_company` varchar(50) DEFAULT NULL COMMENT '快递公司',
  `express_no` varchar(100) DEFAULT NULL COMMENT '快递单号',
  `address` text DEFAULT NULL COMMENT '收货地址',
  `remark` text DEFAULT NULL COMMENT '买家备注',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_no` (`order_no`),
  KEY `idx_user` (`user_id`),
  KEY `idx_seller` (`seller_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='订单表';

-- --------------------------------------------------------
-- 6. 订单商品表 (order_items)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` int(11) UNSIGNED NOT NULL COMMENT '订单ID',
  `product_id` int(11) UNSIGNED NOT NULL COMMENT '商品ID',
  `title` varchar(200) NOT NULL COMMENT '商品名称(快照)',
  `price` decimal(10,2) NOT NULL COMMENT '单价',
  `quantity` int(11) NOT NULL COMMENT '数量',
  `total` decimal(10,2) NOT NULL COMMENT '小计',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_order` (`order_id`),
  KEY `idx_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='订单商品表';

-- --------------------------------------------------------
-- 7. 评论表 (comments)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `comments` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `article_id` int(11) UNSIGNED DEFAULT NULL COMMENT '文章ID',
  `product_id` int(11) UNSIGNED DEFAULT NULL COMMENT '商品ID',
  `user_id` int(11) UNSIGNED NOT NULL COMMENT '评论者ID',
  `parent_id` int(11) UNSIGNED DEFAULT 0 COMMENT '父评论ID(回复)',
  `content` text NOT NULL COMMENT '评论内容',
  `likes` int(11) DEFAULT 0 COMMENT '点赞数',
  `status` tinyint(1) DEFAULT 1 COMMENT '状态: 0待审核 1通过 2驳回',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_article` (`article_id`),
  KEY `idx_product` (`product_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_parent` (`parent_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='评论表';

-- --------------------------------------------------------
-- 8. 收藏表 (favorites)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `favorites` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL COMMENT '用户ID',
  `type` varchar(20) NOT NULL COMMENT '类型: article product pigeon',
  `target_id` int(11) UNSIGNED NOT NULL COMMENT '目标ID',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_user_target` (`user_id`, `type`, `target_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_target` (`target_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='收藏表';

-- --------------------------------------------------------
-- 9. 信鸽表 (pigeons)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pigeons` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL COMMENT '鸽主ID',
  `name` varchar(100) NOT NULL COMMENT '信鸽名称/环号',
  `breed` varchar(100) DEFAULT NULL COMMENT '品种',
  `gender` tinyint(1) DEFAULT 0 COMMENT '性别: 0未知 1雄 2雌',
  `birth_year` year DEFAULT NULL COMMENT '出生年份',
  `color` varchar(50) DEFAULT NULL COMMENT '羽色',
  `eye_type` varchar(50) DEFAULT NULL COMMENT '眼砂类型',
  `bloodline` varchar(200) DEFAULT NULL COMMENT '血统',
  `father` varchar(100) DEFAULT NULL COMMENT '父鸽',
  `mother` varchar(100) DEFAULT NULL COMMENT '母鸽',
  `achievements` text DEFAULT NULL COMMENT '赛绩',
  `description` text DEFAULT NULL COMMENT '描述',
  `images` text DEFAULT NULL COMMENT '图片(JSON)',
  `price` decimal(10,2) DEFAULT NULL COMMENT '价格(出售时)',
  `status` tinyint(1) DEFAULT 1 COMMENT '状态: 0已售 1在棚 2出售中',
  `views` int(11) DEFAULT 0 COMMENT '浏览量',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_breed` (`breed`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='信鸽表';

-- --------------------------------------------------------
-- 10. 赛事表 (races)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `races` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL COMMENT '赛事名称',
  `organizer` varchar(200) DEFAULT NULL COMMENT '主办方',
  `type` varchar(50) DEFAULT NULL COMMENT '赛事类型: 公棚 协会 俱乐部',
  `start_date` date DEFAULT NULL COMMENT '开始日期',
  `end_date` date DEFAULT NULL COMMENT '结束日期',
  `location` varchar(200) DEFAULT NULL COMMENT '地点',
  `distance` decimal(10,2) DEFAULT NULL COMMENT '空距(公里)',
  `total_pigeons` int(11) DEFAULT 0 COMMENT '参赛羽数',
  `status` tinyint(2) DEFAULT 0 COMMENT '状态: 0预告 1进行中 2已结束',
  `description` longtext DEFAULT NULL COMMENT '赛事描述',
  `rules` longtext DEFAULT NULL COMMENT '竞赛规程',
  `results` longtext DEFAULT NULL COMMENT '比赛成绩(JSON)',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_start_date` (`start_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='赛事表';

-- --------------------------------------------------------
-- 11. 论坛帖子表 (posts)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `posts` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL COMMENT '标题',
  `category_id` int(11) UNSIGNED NOT NULL COMMENT '版块ID',
  `user_id` int(11) UNSIGNED NOT NULL COMMENT '发帖人ID',
  `content` longtext NOT NULL COMMENT '内容',
  `views` int(11) DEFAULT 0 COMMENT '浏览量',
  `replies` int(11) DEFAULT 0 COMMENT '回复数',
  `likes` int(11) DEFAULT 0 COMMENT '点赞数',
  `is_top` tinyint(1) DEFAULT 0 COMMENT '是否置顶',
  `is_essence` tinyint(1) DEFAULT 0 COMMENT '是否精华',
  `status` tinyint(1) DEFAULT 1 COMMENT '状态: 0删除 1正常 2关闭',
  `last_reply_at` datetime DEFAULT NULL COMMENT '最后回复时间',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_last_reply` (`last_reply_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='论坛帖子表';

-- --------------------------------------------------------
-- 12. 消息通知表 (notifications)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL COMMENT '接收用户ID',
  `type` varchar(50) NOT NULL COMMENT '类型: system order comment like follow',
  `title` varchar(200) NOT NULL COMMENT '标题',
  `content` text DEFAULT NULL COMMENT '内容',
  `is_read` tinyint(1) DEFAULT 0 COMMENT '是否已读: 0否 1是',
  `link` varchar(255) DEFAULT NULL COMMENT '跳转链接',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_is_read` (`is_read`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='消息通知表';

-- --------------------------------------------------------
-- 插入初始数据
-- --------------------------------------------------------

-- 管理员账号 (用户名: admin, 密码: admin123)
INSERT INTO `users` (`username`, `password`, `email`, `nickname`, `role`, `status`) 
VALUES ('admin', '$2y$10$YourHashedPasswordHere', 'admin@xgjia.com', '管理员', 3, 1);

-- 分类数据（文章分类）
INSERT INTO `categories` (`name`, `parent_id`, `level`, `type`, `sort`, `status`) VALUES
('信鸽资讯', 0, 1, 'article', 1, 1),
('赛鸽学堂', 0, 1, 'article', 2, 1),
('鸽病防治', 0, 1, 'article', 3, 1),
('各地鸽讯', 0, 1, 'article', 4, 1);

-- 分类数据（商品分类）
INSERT INTO `categories` (`name`, `parent_id`, `level`, `type`, `sort`, `status`) VALUES
('鸽具用品', 0, 1, 'product', 1, 1),
('饲料营养', 0, 1, 'product', 2, 1),
('药品保健', 0, 1, 'product', 3, 1),
('铭鸽展示', 0, 1, 'product', 4, 1);

COMMIT;
