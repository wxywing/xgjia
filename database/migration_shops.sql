-- =====================================================
-- 铭鸽展厅表 - 数据库迁移
-- 创建时间: 2026-05-23
-- 数据来源: chinaxinge.com/xinge/product/netshop.asp
-- =====================================================

-- 展厅表
DROP TABLE IF EXISTS `shops`;
CREATE TABLE `shops` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED DEFAULT 0 COMMENT '认领用户ID，0=未认领',
    `source_id` VARCHAR(20) COMMENT '来源ID(中信网shop_id)',
    `name` VARCHAR(100) NOT NULL COMMENT '展厅名称',
    `avatar` VARCHAR(255) COMMENT '展厅头像/Logo',
    `province` VARCHAR(30) COMMENT '省份',
    `city` VARCHAR(30) COMMENT '城市',
    `address` VARCHAR(255) COMMENT '详细地址',
    `contact_name` VARCHAR(50) COMMENT '联系人',
    `contact_phone` VARCHAR(20) COMMENT '联系电话',
    `description` TEXT COMMENT '展厅简介',
    `website` VARCHAR(255) COMMENT '独立域名/网址',
    `model` VARCHAR(10) COMMENT '模板编号(m1~m30)',
    `pigeon_count` INT UNSIGNED DEFAULT 0 COMMENT '展品数量',
    `is_certified` TINYINT DEFAULT 0 COMMENT '是否认证: 0=未认证, 1=已认证',
    `is_hot` TINYINT DEFAULT 0 COMMENT '是否热门',
    `status` TINYINT DEFAULT 0 COMMENT '状态: 0=待审核, 1=正常, 2=已关闭',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_source_id` (`source_id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_province` (`province`),
    KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='铭鸽展厅表';

-- 展厅血系分类表（每个展厅有自己的展品目录）
DROP TABLE IF EXISTS `shop_categories`;
CREATE TABLE `shop_categories` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `shop_id` INT UNSIGNED NOT NULL COMMENT '展厅ID',
    `source_id` VARCHAR(20) COMMENT '来源分类ID(shop_gride)',
    `name` VARCHAR(100) NOT NULL COMMENT '分类名称(如"詹森""佛卡门")',
    `pigeon_count` INT UNSIGNED DEFAULT 0 COMMENT '该分类下展品数',
    `sort` INT DEFAULT 0 COMMENT '排序',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_shop_id` (`shop_id`),
    KEY `idx_source_id` (`source_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='展厅血系分类表';

-- 修改 pigeons 表：增加 shop_id 字段关联展厅
ALTER TABLE `pigeons` ADD COLUMN `shop_id` INT UNSIGNED DEFAULT NULL COMMENT '所属展厅ID' AFTER `user_id`;
ALTER TABLE `pigeons` ADD COLUMN `source_id` VARCHAR(20) DEFAULT NULL COMMENT '来源ID(中信网product_id)' AFTER `shop_id`;
ALTER TABLE `pigeons` ADD KEY `idx_shop_id` (`shop_id`);
ALTER TABLE `pigeons` ADD KEY `idx_source_id` (`source_id`);

-- 环号字段（中信网展品有足环号）
ALTER TABLE `pigeons` ADD COLUMN `ring_number` VARCHAR(50) DEFAULT NULL COMMENT '足环号' AFTER `name`;
ALTER TABLE `pigeons` ADD KEY `idx_ring_number` (`ring_number`);
