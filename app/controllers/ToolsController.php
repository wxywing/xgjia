<?php
/**
 * 数据工具控制器
 */

class ToolsController extends Controller {
    
    private static $ringCodes = [
        ['code' => '01', 'name' => '北京'  , 'pinyin' => 'Beijing'],
        ['code' => '02', 'name' => '天津'  , 'pinyin' => 'Tianjin'],
        ['code' => '03', 'name' => '河北'  , 'pinyin' => 'Hebei'],
        ['code' => '04', 'name' => '山西'  , 'pinyin' => 'Shanxi'],
        ['code' => '05', 'name' => '内蒙古', 'pinyin' => 'Inner Mongolia'],
        ['code' => '06', 'name' => '辽宁'  , 'pinyin' => 'Liaoning'],
        ['code' => '07', 'name' => '吉林'  , 'pinyin' => 'Jilin'],
        ['code' => '08', 'name' => '黑龙江', 'pinyin' => 'Heilongjiang'],
        ['code' => '09', 'name' => '上海'  , 'pinyin' => 'Shanghai'],
        ['code' => '10', 'name' => '江苏'  , 'pinyin' => 'Jiangsu'],
        ['code' => '11', 'name' => '浙江'  , 'pinyin' => 'Zhejiang'],
        ['code' => '12', 'name' => '安徽'  , 'pinyin' => 'Anhui'],
        ['code' => '13', 'name' => '福建'  , 'pinyin' => 'Fujian'],
        ['code' => '14', 'name' => '江西'  , 'pinyin' => 'Jiangxi'],
        ['code' => '15', 'name' => '山东'  , 'pinyin' => 'Shandong'],
        ['code' => '16', 'name' => '河南'  , 'pinyin' => 'Henan'],
        ['code' => '17', 'name' => '湖北'  , 'pinyin' => 'Hubei'],
        ['code' => '18', 'name' => '湖南'  , 'pinyin' => 'Hunan'],
        ['code' => '19', 'name' => '广东'  , 'pinyin' => 'Guangdong'],
        ['code' => '20', 'name' => '广西'  , 'pinyin' => 'Guangxi'],
        ['code' => '21', 'name' => '海南'  , 'pinyin' => 'Hainan'],
        ['code' => '22', 'name' => '重庆'  , 'pinyin' => 'Chongqing'],
        ['code' => '23', 'name' => '四川'  , 'pinyin' => 'Sichuan'],
        ['code' => '24', 'name' => '贵州'  , 'pinyin' => 'Guizhou'],
        ['code' => '25', 'name' => '云南'  , 'pinyin' => 'Yunnan'],
        ['code' => '26', 'name' => '西藏'  , 'pinyin' => 'Tibet'],
        ['code' => '27', 'name' => '陕西'  , 'pinyin' => 'Shaanxi'],
        ['code' => '28', 'name' => '甘肃'  , 'pinyin' => 'Gansu'],
        ['code' => '29', 'name' => '青海'  , 'pinyin' => 'Qinghai'],
        ['code' => '30', 'name' => '宁夏'  , 'pinyin' => 'Ningxia'],
        ['code' => '31', 'name' => '新疆'  , 'pinyin' => 'Xinjiang'],
        ['code' => '32', 'name' => '火车头', 'pinyin' => 'Railway'],
        ['code' => '33', 'name' => '中鸽协', 'pinyin' => 'CRPA'],
    ];

    /**
     * 足环速查表
     */
    public function ringGuide() {
        $codes = self::$ringCodes;
        $pageTitle = '足环年份代码对照表 | 信鸽足环号归属速查';
        $pageDesc  = '中国信鸽足环号年份省份代码对照表，2024/2025/2026年足环编码规则，快速查询足环归属地。';
        
        $this->loadView('tools/ring_guide', [
            'codes' => $codes,
            'pageTitle' => $pageTitle,
            'pageDesc'  => $pageDesc,
        ]);
    }
    
    /**
     * 春赛 TOP100（读预计算表，毫秒级响应）
     */
    public function top100() {
        $pageTitle = '2026春赛TOP100排行榜 | 信鸽赛事数据';
        $pageDesc  = '2026年春季赛鸽分速TOP100排名，数据来自1270万+条赛事记录，含鸽主、足环号、公棚、分速等详细信息。';
        
        // 直接从预计算表读（100行，毫秒级，无慢查询）
        $rankings = $this->pdo->query("SELECT * FROM top100_rankings ORDER BY rank_pos ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        
        // 从第一行提取统计数据
        $stats = null;
        if (!empty($rankings)) {
            $r0 = $rankings[0];
            $stats = [
                'total_birds'  => $r0['stats_birds'] ?? 0,
                'total_owners' => $r0['stats_owners'] ?? 0,
                'total_lofts'  => $r0['stats_lofts'] ?? 0,
                'max_speed'    => $r0['stats_max_spd'] ?? 0,
                'avg_speed'    => $r0['stats_avg_spd'] ?? 0,
                'updated_at'   => $r0['updated_at'] ?? '',
            ];
        }
        
        $this->loadView('tools/top100', [
            'rankings'  => $rankings,
            'stats'     => $stats,
            'pageTitle' => $pageTitle,
            'pageDesc'  => $pageDesc,
        ]);
    }
    
    /**
     * 重建 TOP100 预计算表（cron: 每天凌晨 2:00 执行）
     */
    public function top100Warm() {
        // 统计
        $stats = $this->pdo->query("SELECT
            COUNT(DISTINCT rr.ring_number) AS total_birds,
            COUNT(DISTINCT rr.owner_name) AS total_owners,
            COUNT(DISTINCT l.name) AS total_lofts,
            MAX(rr.speed) AS max_speed,
            ROUND(AVG(rr.speed), 2) AS avg_speed
            FROM races r
            INNER JOIN race_results rr ON rr.race_id = r.id AND rr.speed > 0
            LEFT JOIN lofts l ON r.loft_id = l.id
            WHERE r.season_year = 2026 AND r.status = 1")->fetch(PDO::FETCH_ASSOC) ?: [];
        
        // TOP100
        $rankings = $this->pdo->query("SELECT rr.ring_number, rr.owner_name, rr.rank, rr.speed,
            r.name AS race_name, r.distance_km, r.release_time, r.id AS race_id,
            l.name AS loft_name, l.id AS loft_id
            FROM races r
            INNER JOIN race_results rr ON rr.race_id = r.id AND rr.speed > 0
            LEFT JOIN lofts l ON r.loft_id = l.id
            WHERE r.season_year = 2026 AND r.status = 1
            ORDER BY rr.speed DESC LIMIT 100")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        
        // 写入预计算表
        $this->pdo->exec("TRUNCATE TABLE top100_rankings");
        $ins = $this->pdo->prepare("INSERT INTO top100_rankings
            (rank_pos, ring_number, owner_name, speed, race_name, distance_km,
             release_time, race_id, loft_name, loft_id,
             stats_birds, stats_owners, stats_lofts, stats_max_spd, stats_avg_spd)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        
        foreach ($rankings as $i => $r) {
            $ins->execute([
                $i + 1,
                $r['ring_number'] ?? '',
                $r['owner_name'] ?? '',
                floatval($r['speed'] ?? 0),
                $r['race_name'] ?? '',
                intval($r['distance_km'] ?? 0),
                $r['release_time'] ?? null,
                intval($r['race_id'] ?? 0),
                $r['loft_name'] ?? '',
                intval($r['loft_id'] ?? 0),
                intval($stats['total_birds'] ?? 0),
                intval($stats['total_owners'] ?? 0),
                intval($stats['total_lofts'] ?? 0),
                floatval($stats['max_speed'] ?? 0),
                floatval($stats['avg_speed'] ?? 0),
            ]);
        }
        
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok' => true,
            'updated_at' => date('Y-m-d H:i:s'),
            'rankings' => count($rankings),
            'stats' => $stats,
        ], JSON_UNESCAPED_UNICODE);
    }
}
