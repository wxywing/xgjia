-- 添加slug字段到categories表
ALTER TABLE `categories` ADD COLUMN `slug` VARCHAR(50) NOT NULL COMMENT 'URL别名(拼音)' AFTER `parent_id`;

-- 更新已有分类的slug值
UPDATE `categories` SET `slug` = 'saishi' WHERE `name` = '赛事新闻' AND `slug` = '';
UPDATE `categories` SET `slug` = 'yangge' WHERE `name` = '养鸽知识' AND `slug` = '';
UPDATE `categories` SET `slug` = 'hangye' WHERE `name` = '行业动态' AND `slug` = '';
UPDATE `categories` SET `slug` = 'jishu' WHERE `name` = '技术交流' AND `slug` = '';
UPDATE `categories` SET `slug` = 'renwu' WHERE `name` = '鸽界人物' AND `slug` = '';
UPDATE `categories` SET `slug` = 'gebing' WHERE `name` = '鸽病防治' AND `slug` = '';
UPDATE `categories` SET `slug` = 'xinge' WHERE `name` = '信鸽' AND `slug` = '';
UPDATE `categories` SET `slug` = 'saige' WHERE `name` = '赛鸽' AND `slug` = '';
UPDATE `categories` SET `slug` = 'zhongge' WHERE `name` = '种鸽' AND `slug` = '';
UPDATE `categories` SET `slug` = 'youge' WHERE `name` = '幼鸽' AND `slug` = '';
UPDATE `categories` SET `slug` = 'guanshang' WHERE `name` = '观赏鸽' AND `slug` = '';
UPDATE `categories` SET `slug` = 'geshe' WHERE `name` = '鸽舍转让' AND `slug` = '';
UPDATE `categories` SET `slug` = 'peidui' WHERE `name` = '配对信息' AND `slug` = '';
UPDATE `categories` SET `slug` = 'qiugou' WHERE `name` = '求购信息' AND `slug` = '';
UPDATE `categories` SET `slug` = 'zhuanrang' WHERE `name` = '转让信息' AND `slug` = '';
UPDATE `categories` SET `slug` = 'zhaopin' WHERE `name` = '招聘求职' AND `slug` = '';
UPDATE `categories` SET `slug` = 'chunpeng' WHERE `name` = '春棚' AND `slug` = '';
UPDATE `categories` SET `slug` = 'qiupeng' WHERE `name` = '秋棚' AND `slug` = '';
UPDATE `categories` SET `slug` = 'tebi' WHERE `name` = '特比环棚' AND `slug` = '';
UPDATE `categories` SET `slug` = 'duoguan' WHERE `name` = '多关赛棚' AND `slug` = '';

-- 添加唯一索引
ALTER TABLE `categories` ADD UNIQUE KEY `uk_slug` (`slug`);
