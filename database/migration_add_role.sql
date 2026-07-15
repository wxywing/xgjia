-- =====================================================
-- 增量更新 - 添加用户角色字段
-- 执行日期: 2026-05-21
-- 说明: 如果数据库已经导入过 xgjia_new.sql，执行此文件添加 role 字段
-- =====================================================

-- 添加 role 字段
ALTER TABLE `users` ADD COLUMN `role` VARCHAR(20) DEFAULT 'user' COMMENT '角色: admin=管理员, user=普通用户' AFTER `status`;

-- 设置 admin 用户为管理员角色
UPDATE `users` SET `role` = 'admin' WHERE `username` = 'admin';
