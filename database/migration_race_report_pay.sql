-- ============================================================
-- 深度报告支付系统迁移（MySQL 5.7 兼容）
-- 复用 member_orders 表，新增 product_type/product_ref 区分产品
-- ============================================================

-- 1. member_orders 扩展产品类型（用存储过程检测列是否存在）
DROP PROCEDURE IF EXISTS migrate_report_pay;
DELIMITER $$
CREATE PROCEDURE migrate_report_pay()
BEGIN
  -- product_type 列
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'member_orders'
      AND COLUMN_NAME = 'product_type'
  ) THEN
    ALTER TABLE `member_orders`
      ADD COLUMN `product_type` VARCHAR(20) NOT NULL DEFAULT 'membership' COMMENT '产品类型: membership/report' AFTER `months`;
  END IF;

  -- product_ref 列
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'member_orders'
      AND COLUMN_NAME = 'product_ref'
  ) THEN
    ALTER TABLE `member_orders`
      ADD COLUMN `product_ref` VARCHAR(200) DEFAULT NULL COMMENT '产品引用: 足环号等' AFTER `product_type`;
  END IF;
END$$
DELIMITER ;
CALL migrate_report_pay();
DROP PROCEDURE migrate_report_pay;

-- 2. 足环号解锁记录表
CREATE TABLE IF NOT EXISTS `member_race_unlocks` (
  `id`       INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id`  INT UNSIGNED NOT NULL COMMENT '用户ID',
  `ring`     VARCHAR(100) NOT NULL COMMENT '足环号',
  `order_id` INT UNSIGNED DEFAULT NULL COMMENT '关联订单ID',
  `paid_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '支付时间',
  UNIQUE KEY `uk_user_ring` (`user_id`, `ring`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='足环号报告解锁记录';

SELECT '深度报告支付迁移完成!' AS result;
