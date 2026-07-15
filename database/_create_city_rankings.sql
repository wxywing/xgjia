-- 城市 TOP 预计算表（方案 B）
-- 执行一次即可

CREATE TABLE IF NOT EXISTS city_pigeon_rankings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    city VARCHAR(64) NOT NULL COMMENT '城市名',
    rank_pos TINYINT NOT NULL COMMENT '排名 1-10',
    ring_number VARCHAR(64) DEFAULT '' COMMENT '足环号',
    owner_name VARCHAR(128) DEFAULT '' COMMENT '鸽主姓名',
    speed DECIMAL(10,1) DEFAULT 0 COMMENT '分速 m/min',
    `rank` INT DEFAULT 0 COMMENT '该场比赛排名',
    race_name VARCHAR(255) DEFAULT '' COMMENT '赛事名称',
    release_time DATETIME DEFAULT NULL COMMENT '开笼时间',
    distance_km DECIMAL(8,1) DEFAULT 0 COMMENT '空距 km',
    loft_name VARCHAR(255) DEFAULT '' COMMENT '所属公棚',
    loft_id INT DEFAULT 0 COMMENT '公棚ID',
    computed_at DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '计算时间',
    UNIQUE KEY uk_city_pigeon_rank (city, rank_pos),
    KEY idx_city (city),
    KEY idx_computed_at (computed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='城市速度鸽 TOP10 预计算表';

CREATE TABLE IF NOT EXISTS city_owner_rankings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    city VARCHAR(64) NOT NULL COMMENT '城市名',
    rank_pos TINYINT NOT NULL COMMENT '排名 1-10',
    owner_name VARCHAR(128) DEFAULT '' COMMENT '鸽主姓名',
    entry_count INT DEFAULT 0 COMMENT '参赛羽数',
    top100_count INT DEFAULT 0 COMMENT '入赏次数（≤100名）',
    best_speed DECIMAL(10,1) DEFAULT 0 COMMENT '最高分速',
    avg_speed DECIMAL(10,1) DEFAULT 0 COMMENT '平均分速',
    computed_at DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '计算时间',
    UNIQUE KEY uk_city_owner_rank (city, rank_pos),
    KEY idx_city (city),
    KEY idx_computed_at (computed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='城市鸽主 TOP10 预计算表';

CREATE TABLE IF NOT EXISTS city_loft_rankings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    city VARCHAR(64) NOT NULL COMMENT '城市名',
    rank_pos TINYINT NOT NULL COMMENT '排名 1-10',
    loft_id INT DEFAULT 0 COMMENT '公棚ID',
    loft_name VARCHAR(255) DEFAULT '' COMMENT '公棚名称',
    race_count INT DEFAULT 0 COMMENT '参赛场次',
    total_entries INT DEFAULT 0 COMMENT '总参赛羽数',
    top100_count INT DEFAULT 0 COMMENT '入赏羽数（≤100名）',
    avg_speed DECIMAL(10,1) DEFAULT 0 COMMENT '平均分速',
    max_speed DECIMAL(10,1) DEFAULT 0 COMMENT '最高分速',
    computed_at DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '计算时间',
    UNIQUE KEY uk_city_loft_rank (city, rank_pos),
    KEY idx_city (city),
    KEY idx_computed_at (computed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='城市公棚 TOP10 预计算表';
