-- ============================================
-- xgjia.com 测试数据
-- 创建日期: 2026-05-20
-- 说明: 模拟真实数据，用于功能测试
-- ============================================

-- 清空现有数据（保留表结构）
TRUNCATE TABLE `likes`;
TRUNCATE TABLE `comments`;
TRUNCATE TABLE `dynamics`;
TRUNCATE TABLE `listings`;
TRUNCATE TABLE `pigeons`;
TRUNCATE TABLE `articles`;
TRUNCATE TABLE `races`;
TRUNCATE TABLE `follows`;
TRUNCATE TABLE `orders`;
TRUNCATE TABLE `advertisements`;
TRUNCATE TABLE `categories`;
TRUNCATE TABLE `users`;
TRUNCATE TABLE `settings`;
TRUNCATE TABLE `loft_reviews`;
TRUNCATE TABLE `loft_entries`;
TRUNCATE TABLE `lofts`;

-- ============================================
-- 1. 系统设置
-- ============================================
INSERT INTO `settings` (`key`, `value`, `description`) VALUES
('site_name', '信鸽之家', '网站名称'),
('site_url', 'https://xgjia.com', '网站地址'),
('contact_email', 'admin@xgjia.com', '联系邮箱'),
('contact_phone', '400-000-0000', '联系电话'),
('audit_enabled', '0', '是否开启内容审核（0=否，1=是）'),
('vip_price_monthly', '99.00', 'VIP会员月费'),
('vip_price_yearly', '999.00', 'VIP会员年费');

-- ============================================
-- 2. 分类数据
-- ============================================

-- 文章分类（type=1）
INSERT INTO `categories` (`slug`, `name`, `type`, `parent_id`, `sort`, `status`) VALUES
('saishi', '赛事新闻', 1, 0, 1, 1),
('yangge', '养鸽知识', 1, 0, 2, 1),
('hangye', '行业动态', 1, 0, 3, 1),
('jishu', '技术交流', 1, 0, 4, 1),
('renwu', '鸽界人物', 1, 0, 5, 1);

-- 铭鸽分类（type=2）
INSERT INTO `categories` (`slug`, `name`, `type`, `parent_id`, `sort`, `status`) VALUES
('saige', '赛鸽', 2, 0, 1, 1),
('zhongge', '种鸽', 2, 0, 2, 1),
('youge', '幼鸽', 2, 0, 3, 1),
('guanshang', '观赏鸽', 2, 0, 4, 1);

-- 分类信息类型（type=3）
INSERT INTO `categories` (`slug`, `name`, `type`, `parent_id`, `sort`, `status`) VALUES
('geshe', '鸽舍转让', 3, 0, 1, 1),
('peidui', '配对信息', 3, 0, 2, 1),
('qiugou', '求购信息', 3, 0, 3, 1),
('zhuanrang', '转让信息', 3, 0, 4, 1),
('zhaopin', '招聘求职', 3, 0, 5, 1);

-- ============================================
-- 3. 用户数据
-- ============================================

-- 管理员账户
INSERT INTO `users` (`username`, `password`, `nickname`, `email`, `phone`, `avatar`, `member_level`, `member_expire_at`, `role`, `status`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '管理员', 'admin@xgjia.com', '13800000000', NULL, 1, '2099-12-31 23:59:59', 'admin', 1);

-- VIP会员（可以发布内容）
INSERT INTO `users` (`username`, `password`, `nickname`, `email`, `phone`, `avatar`, `member_level`, `member_expire_at`, `role`, `status`) VALUES
('zhangsan', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '张三鸽舍', 'zhangsan@example.com', '13800000001', NULL, 1, '2027-12-31 23:59:59', 'user', 1),
('lisi', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '李四公棚', 'lisi@example.com', '13800000002', NULL, 1, '2027-06-30 23:59:59', 'user', 1),
('wangwu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '王五赛鸽', 'wangwu@example.com', '13800000003', NULL, 1, '2026-12-31 23:59:59', 'user', 1);

-- 普通会员（只能浏览）
INSERT INTO `users` (`username`, `password`, `nickname`, `email`, `phone`, `avatar`, `member_level`, `member_expire_at`, `role`, `status`) VALUES
('user1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '鸽子爱好者', 'user1@example.com', '13900000001', NULL, 0, NULL, 'user', 1),
('user2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '新手鸽友', 'user2@example.com', '13900000002', NULL, 0, NULL, 'user', 1),
('user3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '赛鸽达人', 'user3@example.com', '13900000003', NULL, 0, NULL, 'user', 1);

-- ============================================
-- 4. 文章数据
-- ============================================

INSERT INTO `articles` (`user_id`, `category_id`, `title`, `summary`, `content`, `cover`, `views`, `likes`, `comments`, `is_top`, `is_recommend`, `status`) VALUES
-- 赛事新闻
(1, 1, '2026年全国信鸽锦标赛即将开幕', '一年一度的全国信鸽锦标赛将于下月举行，本次比赛规模创历史新高。', '<p>2026年全国信鸽锦标赛将于6月15日在北京正式拉开帷幕。本届比赛吸引了来自全国32个省市自治区的超过5000羽赛鸽参赛，参赛规模创历史新高。</p><p>比赛设置500公里、700公里、1000公里三个级别，预计历时15天。赛事组委会表示，本届比赛奖金总额达到200万元，冠军将获得30万元现金奖励。</p>', NULL, 1256, 89, 45, 1, 1, 1),
(1, 1, '广东公棚赛季收官，多项记录被打破', '2026年广东公棚赛季圆满结束，多项赛事记录被刷新。', '<p>2026年广东公棚赛季于上周正式收官。在为期三个月的比赛中，参赛鸽友创造了多项新记录：</p><ul><li>500公里最快分速达到1850米/分钟</li><li>单场比赛归巢率达到98.5%</li><li>冠军鸽拍卖价格突破50万元</li></ul><p>业内人士表示，这反映了我国信鸽运动水平的持续提升。</p>', NULL, 892, 67, 32, 0, 1, 1),

-- 养鸽知识
(1, 2, '赛鸽换羽期的饲养管理要点', '换羽期是赛鸽生长的关键时期，科学的饲养管理至关重要。', '<p>每年8-10月是赛鸽的换羽期，这个时期的饲养管理直接影响到鸽子来年的竞技状态。以下是几点重要建议：</p><h3>1. 营养搭配</h3><p>换羽期需要增加蛋白质和矿物质的摄入，建议使用含硫氨基酸丰富的饲料，如豌豆、油菜籽等。</p><h3>2. 环境控制</h3><p>保持鸽舍干燥通风，避免潮湿环境导致羽毛质量下降。</p><h3>3. 适度运动</h3><p>换羽期不宜进行高强度训练，但要保持适度的家飞运动。</p>', NULL, 2341, 156, 78, 0, 1, 1),
(1, 2, '如何鉴别优质赛鸽', '优质赛鸽的鉴别是每个鸽友必须掌握的基本技能。', '<p>鉴别优质赛鸽需要从多个维度进行观察：</p><h3>外观特征</h3><p>羽毛紧实有光泽，龙骨略弯且长度适中，耻骨紧闭。</p><h3>眼睛特征</h3><p>眼砂饱满，色彩鲜艳，瞳孔收缩灵活。</p><h3>肌肉状态</h3><p>胸肌富有弹性，肌肉饱满但不臃肿。</p><h3>性格特征</h3><p>反应敏捷，有强烈的归巢欲望。</p>', NULL, 1876, 134, 56, 0, 1, 1),

-- 行业动态
(1, 3, '信鸽产业年产值突破100亿元', '最新统计显示，我国信鸽产业年产值已突破100亿元大关。', '<p>据中国信鸽协会最新统计，2025年我国信鸽产业年产值已突破100亿元，较上年增长15%。其中：</p><ul><li>赛鸽交易：45亿元</li><li>鸽具用品：25亿元</li><li>赛事奖金：20亿元</li><li>公棚服务：10亿元</li></ul><p>业内专家预测，未来五年信鸽产业将保持年均10%以上的增长率。</p>', NULL, 678, 45, 23, 0, 1, 1),

-- 技术交流
(2, 4, '我是这样训练幼鸽的', '分享我多年训练幼鸽的经验和方法。', '<p>训练幼鸽是每个鸽友的基本功，我总结了一套行之有效的方法：</p><h3>第一阶段：熟悉环境（1-2周）</h3><p>让幼鸽在鸽舍周围熟悉环境，建立方位感。</p><h3>第二阶段：短距离训放（3-4周）</h3><p>从5公里开始，逐步增加到50公里。</p><h3>第三阶段：定向训练（5-6周）</h3><p>选择不同方向进行训放，培养定向能力。</p><h3>第四阶段：比赛模拟（7-8周）</h3><p>模拟比赛环境，进行100公里以上训放。</p>', NULL, 543, 38, 29, 0, 0, 1),

-- 鸽界人物
(1, 5, '专访：三连冠鸽主的养鸽秘诀', '本刊独家专访连续三年获得省赛冠军的鸽主张明先生。', '<p>张明，人称"金牌鸽主"，连续三年获得省级赛鸽比赛冠军。近日，本刊记者对他进行了独家专访。</p><h3>问：您的成功秘诀是什么？</h3><p>答：其实没有秘诀，就是用心。每天早上5点起床，第一件事就是去鸽舍观察每羽鸽子的状态。</p><h3>问：您如何选种？</h3><p>答：我选种有三个标准：血统清楚、成绩稳定、体型完美。</p><h3>问：对新手有什么建议？</h3><p>答：少买多学，先学习养鸽知识，再慢慢积累经验。</p>', NULL, 432, 27, 18, 0, 1, 1);

-- ============================================
-- 5. 铭鸽数据
-- ============================================

INSERT INTO `pigeons` (`user_id`, `category_id`, `name`, `ring_number`, `bloodline`, `gender`, `color`, `eye_color`, `achievements`, `description`, `images`, `video`, `views`, `likes`, `comments`, `is_recommend`, `status`) VALUES
-- 赛鸽
(2, 1, '飞龙号', 'CHN-2024-001234', '詹森', 1, '灰', '黄眼', '2025年省赛500公里冠军', '本羽鸽子体型完美，肌肉饱满，多次在大赛中取得优异成绩。性格沉稳，适合做赛鸽。', '["https://picsum.photos/seed/pigeon1/400/300", "https://picsum.photos/seed/pigeon1a/400/300"]', NULL, 2345, 167, 45, 1, 1),
(3, 1, '追风者', 'CHN-2024-002345', '范内', 1, '雨点', '砂眼', '2025年秋季公棚赛第5名', '速度型赛鸽，爆发力强，适合500-700公里赛事。', '["https://picsum.photos/seed/pigeon2/400/300"]', NULL, 1876, 134, 38, 1, 1),
(4, 1, '闪电侠', 'CHN-2025-003456', '胡本', 1, '灰白条', '黄眼', '2026年春季赛300公里亚军', '年轻有潜力，刚在春季赛中取得好成绩。', '["https://picsum.photos/seed/pigeon3/400/300"]', NULL, 1234, 89, 23, 0, 1),

-- 种鸽
(2, 2, '金凤凰', 'CHN-2020-004567', '詹森', 2, '灰', '黄眼', '作育出3羽冠军鸽', '优秀种雌，已作育出多羽优秀赛鸽。', '["https://picsum.photos/seed/pigeon4/400/300"]', NULL, 3456, 234, 67, 1, 1),
(3, 2, '王者之翼', 'CHN-2019-005678', '克拉克', 2, '深灰', '砂眼', '作育出5羽获奖鸽', '镇舍种雄，血统纯正，作育能力极强。', '["https://picsum.photos/seed/pigeon5/400/300", "https://picsum.photos/seed/pigeon5a/400/300", "https://picsum.photos/seed/pigeon5b/400/300"]', NULL, 4567, 312, 89, 1, 1),

-- 幼鸽
(4, 3, '小飞侠', 'CHN-2026-006789', '詹森', 1, '灰', '黄眼', NULL, '2026年幼鸽，体型匀称，骨架硬朗，潜力股。', '["https://picsum.photos/seed/pigeon6/400/300"]', NULL, 876, 56, 12, 0, 1),
(2, 3, '小精灵', 'CHN-2026-007890', '范内', 2, '雨点', '砂眼', NULL, '2026年幼鸽，眼睛漂亮，性格活泼。', '["https://picsum.photos/seed/pigeon7/400/300"]', NULL, 654, 43, 9, 0, 1);

-- ============================================
-- 6. 分类信息数据
-- ============================================

INSERT INTO `listings` (`user_id`, `type`, `title`, `description`, `images`, `contact_name`, `contact_phone`, `contact_wechat`, `price`, `negotiable`, `location`, `views`, `likes`, `status`) VALUES
-- 鸽舍转让
(2, 1, '优质赛鸽鸽舍转让', '因工作调动，现转让经营中的赛鸽鸽舍一处。鸽舍面积200平米，可容纳赛鸽300羽，设施齐全，接手即可经营。', '["https://picsum.photos/seed/loft1/400/300"]', '张三', '13800000001', 'zhangsan_wx', 50000.00, 1, '广东省广州市', 234, 12, 1),
(3, 1, '公棚股份转让', '现有某知名公棚10%股份转让，公棚运营稳定，年年盈利。', '["https://picsum.photos/seed/loft2/400/300"]', '李四', '13800000002', 'lisi_wx', 100000.00, 0, '广东省深圳市', 345, 18, 1),

-- 配对信息
(4, 2, '优秀种鸽配对转让', '詹森配胡本，已作育出多羽获奖鸽。现转让配对权利，有意者联系。', '["https://picsum.photos/seed/pair1/400/300", "https://picsum.photos/seed/pair1a/400/300"]', '王五', '13800000003', 'wangwu_wx', 8000.00, 0, '北京市', 567, 34, 1),

-- 求购信息
(2, 3, '求购优质种雄', '求购血统纯正的詹森系种雄1羽，必须有成绩或作育记录。价格面议。', NULL, '张三', '13800000001', 'zhangsan_wx', NULL, 1, '广东省广州市', 123, 5, 1),

-- 转让信息
(3, 4, '转让多羽优秀赛鸽', '因调整鸽舍，现转让赛鸽10羽，血统包括詹森、范内、胡本等，价格从优。', '["https://picsum.photos/seed/sale1/400/300"]', '李四', '13800000002', 'lisi_wx', 3000.00, 1, '广东省深圳市', 456, 23, 1),

-- 招聘求职
(4, 5, '招聘鸽舍管理员', '招聘有经验的鸽舍管理员一名，包吃住，月薪6000元起。', NULL, '王五', '13800000003', 'wangwu_wx', NULL, 0, '北京市', 189, 8, 1);

-- 公棚分类（type=4）
INSERT INTO `categories` (`slug`, `name`, `type`, `parent_id`, `sort`, `status`) VALUES
('chunpeng', '春棚', 4, 0, 1, 1),
('qiupeng', '秋棚', 4, 0, 2, 1),
('tebi', '特比环棚', 4, 0, 3, 1),
('duoguan', '多关赛棚', 4, 0, 4, 1);

-- ============================================
-- 7. 赛事数据
-- ============================================

INSERT INTO `races` (`title`, `organizer`, `race_type`, `race_date`, `end_date`, `location`, `distance`, `prize_pool`, `registration_deadline`, `description`, `status`) VALUES
('2026年全国信鸽锦标赛', '中国信鸽协会', '国家级', '2026-06-15', '2026-06-30', '北京市大兴区', 500, 2000000.00, '2026-05-31', '全国最高级别信鸽赛事，设置500公里、700公里、1000公里三个级别。', 1),
('2026年广东省春季赛', '广东省信鸽协会', '省级', '2026-04-01', '2026-04-15', '广东省广州市', 500, 500000.00, '2026-03-15', '广东省春季传统赛事，设有500公里、700公里两个级别。', 4),
('2026年秋赛季首场比赛', '北京市信鸽协会', '市级', '2026-09-01', '2026-09-05', '北京市通州区', 300, 100000.00, '2026-08-20', '北京市秋季首场比赛，为后续大赛热身。', 0),
('2026年长三角精英赛', '江浙沪信鸽协会联盟', '区域级', '2026-10-10', '2026-10-20', '上海市浦东新区', 600, 800000.00, '2026-09-30', '长三角地区精英赛，仅限三省一市注册鸽友参加。', 0);

-- ============================================
-- 8. 鸽友动态（鸽友圈）
-- ============================================

INSERT INTO `dynamics` (`user_id`, `content`, `images`, `views`, `likes`, `comments`, `status`) VALUES
(2, '今天家飞，30羽鸽子飞行2小时，状态很好！准备下周开始短距离训放。', '["https://picsum.photos/seed/dynamic1/400/300"]', 234, 18, 5, 1),
(3, '恭喜自己！秋季公棚赛获得第5名，这羽鸽子从幼鸽开始就表现很优秀。', '["https://picsum.photos/seed/dynamic2/400/300", "https://picsum.photos/seed/dynamic2a/400/300"]', 567, 45, 12, 1),
(4, '新引进的种鸽已到家，体型完美，眼睛漂亮，希望能作育出好鸽子！', '["https://picsum.photos/seed/dynamic3/400/300"]', 189, 23, 8, 1),
(5, '请教各位鸽友：幼鸽换羽期间应该注意什么？第一次养幼鸽，很多不懂的地方。', NULL, 123, 7, 15, 1),
(2, '分享一个小技巧：训练赛鸽时，可以在饲料中添加少量大蒜粉，有助于提高免疫力。', NULL, 345, 28, 9, 1);

-- ============================================
-- 9. 评论数据
-- ============================================

INSERT INTO `comments` (`user_id`, `target_type`, `target_id`, `content`, `status`) VALUES
(5, 1, 1, '期待今年的全国锦标赛，希望能看到更多精彩比赛！'),
(6, 1, 3, '文章写得很详细，学习了，感谢分享！'),
(5, 1, 4, '请教一下，如何判断鸽子的眼砂好坏？'),
(2, 2, 1, '这羽鸽子体型确实很完美，羡慕！'),
(6, 3, 2, '恭喜恭喜！真是好成绩啊！'),
(2, 3, 4, '换羽期注意营养，增加蛋白质摄入，保持鸽舍干燥通风。');

-- ============================================
-- 10. 点赞数据
-- ============================================

INSERT INTO `likes` (`user_id`, `target_type`, `target_id`) VALUES
(5, 1, 1),
(6, 1, 1),
(5, 1, 3),
(6, 1, 4),
(5, 2, 1),
(6, 2, 1),
(5, 3, 1),
(6, 3, 2);

-- ============================================
-- 11. 关注数据
-- ============================================

INSERT INTO `follows` (`follower_id`, `following_id`) VALUES
(5, 2),
(5, 3),
(6, 2),
(6, 4);

-- ============================================
-- 12. 广告数据
-- ============================================

INSERT INTO `advertisements` (`title`, `image`, `link`, `position`, `sort`, `start_at`, `end_at`, `status`) VALUES
('优质鸽粮限时促销', 'https://picsum.photos/seed/ad1/800/200', 'https://example.com/ad1', 'home_banner', 1, '2026-01-01', '2026-12-31', 1),
('2026赛季鸽具新品', 'https://picsum.photos/seed/ad2/800/200', 'https://example.com/ad2', 'home_banner', 2, '2026-01-01', '2026-12-31', 1);

-- ============================================
-- 13. 公棚数据
-- ============================================

INSERT INTO `lofts` (`user_id`, `name`, `province`, `city`, `address`, `contact_name`, `contact_phone`, `logo`, `description`, `capacity`, `current_count`, `entry_fee`, `management_fee`, `prize_pool`, `race_distance`, `race_type`, `collect_start`, `collect_end`, `training_start`, `race_date`, `rules`, `rating`, `rating_count`, `views`, `is_certified`, `is_hot`, `status`) VALUES
(1, '北京长城公棚', '北京', '北京', '北京市顺义区马坡镇', '王建国', '010-12345678', '/public/assets/images/loft-default.jpg', '北京长城公棚成立于2005年，是华北地区知名秋棚，设施完善，管理严格，历年归巢率名列前茅。', 3000, 1850, 500.00, 200.00, 100000.00, 500, '秋棚', '2026-03-01', '2026-05-31', '2026-08-01', '2026-10-15', '参赛规程详见公棚官网，严格执行中鸽协相关规定。', 8.5, 120, 2500, 1, 1, 1),
(1, '上海东方公棚', '上海', '上海', '上海市浦东新区曹路镇', '李明华', '021-98765432', '/public/assets/images/loft-default.jpg', '上海东方公棚是华东地区最大的春棚之一，拥有现代化鸽舍和专业训放团队。', 5000, 3200, 800.00, 300.00, 200000.00, 350, '春棚', '2026-10-01', '2026-12-31', '2027-02-01', '2027-05-01', '参赛规程严格执行，确保公平公正。', 9.2, 200, 5800, 1, 1, 1),
(2, '广州南方公棚', '广东', '广州', '广州市白云区太和镇', '张伟', '020-87654321', '/public/assets/images/loft-default.jpg', '广州南方公棚专注华南地区信鸽赛事，适合南方气候条件训养。', 2000, 1200, 300.00, 150.00, 50000.00, 300, '秋棚', '2026-03-01', '2026-06-30', '2026-09-01', '2026-11-20', '规程规范，收费合理。', 7.8, 80, 1200, 0, 0, 1),
(1, '成都天府公棚', '四川', '成都', '成都市新都区大丰街道', '陈志远', '028-76543210', '/public/assets/images/loft-default.jpg', '成都天府公棚西南地区知名特比环棚，历年比赛成绩优异。', 2500, 1500, 600.00, 250.00, 150000.00, 400, '特比环棚', '2026-04-01', '2026-07-31', '2026-09-15', '2026-11-01', '特比环规程，严格执行。', 8.0, 95, 1800, 1, 0, 1),
(3, '武汉长江公棚', '湖北', '武汉', '武汉市洪山区花山街道', '刘国强', '027-54321678', '/public/assets/images/loft-default.jpg', '武汉长江公棚地处华中，交通便利，多关赛经验丰富。', 3000, 800, 400.00, 180.00, 80000.00, 450, '多关赛棚', '2026-02-01', '2026-05-31', '2026-08-01', '2026-10-30', '多关赛规程，五关赛制。', 7.5, 50, 600, 0, 0, 1);

-- ============================================
-- 14. 公棚参赛记录
-- ============================================

INSERT INTO `loft_entries` (`loft_id`, `user_id`, `pigeon_ring`, `pigeon_name`, `pigeon_color`, `entry_fee_paid`, `management_fee_paid`, `status`, `remark`) VALUES
(1, 2, '2026-01-123456', '灰雄001', '灰', 1, 1, 1, '状态良好'),
(1, 2, '2026-01-123457', '雨点雄002', '雨点', 1, 1, 1, ''),
(1, 3, '2026-01-234567', '灰雌001', '灰', 1, 1, 2, '训放中'),
(2, 2, '2026-02-345678', '红轮雄001', '红轮', 1, 1, 1, ''),
(2, 3, '2026-02-345679', '白条雌001', '白条', 1, 0, 1, '管理费未付');

-- ============================================
-- 数据统计
-- ============================================

SELECT '数据导入完成！' AS message;
SELECT '用户' AS `table`, COUNT(*) AS count FROM `users`
UNION ALL
SELECT '分类', COUNT(*) FROM `categories`
UNION ALL
SELECT '文章', COUNT(*) FROM `articles`
UNION ALL
SELECT '铭鸽', COUNT(*) FROM `pigeons`
UNION ALL
SELECT '分类信息', COUNT(*) FROM `listings`
UNION ALL
SELECT '赛事', COUNT(*) FROM `races`
UNION ALL
SELECT '动态', COUNT(*) FROM `dynamics`
UNION ALL
SELECT '评论', COUNT(*) FROM `comments`
UNION ALL
SELECT '点赞', COUNT(*) FROM `likes`
UNION ALL
SELECT '公棚', COUNT(*) FROM `lofts`
UNION ALL
SELECT '参赛记录', COUNT(*) FROM `loft_entries`;
