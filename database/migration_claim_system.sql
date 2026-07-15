-- 信鸽之家 - 商家认领系统数据库迁移
-- 执行时间: 2026-05-23
-- 说明: 认领申请表 + shops/lofts 索引优化

SET NAMES utf8mb4;

-- 1. 认领申请表
DROP TABLE IF EXISTS claim_requests;
CREATE TABLE claim_requests (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL COMMENT '申请人ID',
    target_type VARCHAR(20) NOT NULL COMMENT '认领目标类型: shop/loft',
    target_id INT UNSIGNED NOT NULL COMMENT '认领目标ID',
    real_name VARCHAR(50) NOT NULL COMMENT '真实姓名',
    phone VARCHAR(20) NOT NULL COMMENT '联系电话',
    wechat VARCHAR(50) DEFAULT NULL COMMENT '微信号',
    evidence TEXT DEFAULT NULL COMMENT '证明材料(营业执照/名片等URL,JSON数组)',
    reason VARCHAR(500) NOT NULL COMMENT '申请理由',
    status TINYINT DEFAULT 0 COMMENT '状态: 0=待审核, 1=已通过, 2=已拒绝, 3=已取消',
    admin_note VARCHAR(500) DEFAULT NULL COMMENT '管理员备注',
    reviewed_at DATETIME DEFAULT NULL COMMENT '审核时间',
    reviewed_by INT UNSIGNED DEFAULT NULL COMMENT '审核人ID',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_user (user_id),
    KEY idx_target (target_type, target_id),
    KEY idx_status (status),
    KEY idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商家认领申请表';

-- 2. shops 表加索引（加速 user_id=0 查询待认领数据）
ALTER TABLE shops ADD INDEX idx_user_id (user_id);

-- 3. lofts 表加索引（同上）
ALTER TABLE lofts ADD INDEX idx_user_id (user_id);

SELECT '商家认领系统数据库迁移完成!' AS result;
