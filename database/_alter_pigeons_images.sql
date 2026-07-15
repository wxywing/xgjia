-- 修改 pigeons 表 images 字段为 LONGTEXT
-- 原因：base64 编码的图片数据可能超过 TEXT 类型的 65535 字节限制

ALTER TABLE `pigeons` MODIFY COLUMN `images` LONGTEXT COMMENT '图片(JSON数组)';
