<?php
/**
 * 信鸽之家 - 2026春赛数据白皮书生成器
 * 
 * 使用方法：
 *   1. 上传此文件到服务器
 *   2. 浏览器访问 http://www.xgjia.com/scripts/generate_data_report.php
 *   3. 等待页面加载完成（可能需要1-2分钟）
 *   4. 浏览器打印 → 另存为PDF
 * 
 * 注意：此脚本仅用于生成报告，完成后请删除
 */

require_once __DIR__ . "/../app/config/config.php";

header("Content-Type: text/html; charset=utf-8");

// 数据库连接
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}

$pdo = getDB();

// ============================================================
// 数据查询
// ============================================================

echo "<!-- 开始查询数据... -->\n";
ob_flush();
flush();

// 1. 基本统计
$stats = [];

// 总记录数
$stats["total_records"] = $pdo->query("SELECT COUNT(*) FROM race_results")->fetchColumn();

// 2026春赛记录数
$stats["records_2026"] = $pdo->query("SELECT COUNT(*) FROM race_results WHERE year = 2026")->fetchColumn();

// 参赛鸽子数（去重足环号）
$stats["pigeons_2026"] = $pdo->query("SELECT COUNT(DISTINCT ring_number) FROM race_results WHERE year = 2026")->fetchColumn();

// 参赛鸽友数
$stats["owners_2026"] = $pdo->query("SELECT COUNT(DISTINCT owner_name) FROM race_results WHERE year = 2026")->fetchColumn();

// 公棚数
$stats["lofts_count"] = $pdo->query("SELECT COUNT(*) FROM lofts")->fetchColumn();

// 2. 分速统计
$speedStats = $pdo->query("
    SELECT 
        AVG(speed) as avg_speed,
        MAX(speed) as max_speed,
        MIN(speed) as min_speed,
        COUNT(*) as total_with_speed
    FROM race_results 
    WHERE year = 2026 AND speed > 0 AND speed < 2000
")->fetch();

$stats["avg_speed"] = round($speedStats["avg_speed"], 2);
$stats["max_speed"] = round($speedStats["max_speed"], 2);
$stats["min_speed"] = round($speedStats["min_speed"], 2);

// 3. 分速分布（分档统计）
$speedDistribution = $pdo->query("
    SELECT 
        CASE 
            WHEN speed < 800 THEN "不足800"
            WHEN speed < 1000 THEN "800-1000"
            WHEN speed < 1200 THEN "1000-1200"
            WHEN speed < 1400 THEN "1200-1400"
            WHEN speed < 1600 THEN "1400-1600"
            ELSE "1600以上"
        END as speed_range,
        COUNT(*) as cnt
    FROM race_results
    WHERE year = 2026 AND speed > 0 AND speed < 2000
    GROUP BY speed_range
    ORDER BY MIN(speed)
")->fetchAll();

// 4. TOP100（从预计算表）
$top100 = [];
try {
    $top100 = $pdo->query("SELECT * FROM top100_rankings ORDER BY rank ASC LIMIT 100")->fetchAll();
} catch (Exception $e) {
    // 表不存在，跳过
}

// 5. 省份分布
$provinceStats = $pdo->query("
    SELECT 
        CASE 
            WHEN ring_number REGEXP "^-?[0-9]+$" THEN SUBSTRING(ring_number, 5, 2)
            ELSE NULL
        END as province_code,
        COUNT(*) as cnt
    FROM race_results
    WHERE year = 2026 AND ring_number IS NOT NULL AND ring_number != ""
    GROUP BY province_code
    HAVING province_code IS NOT NULL
    ORDER BY cnt DESC
    LIMIT 20
")->fetchAll();

// 省份代码映射
$provinceMap = [
    "01" => "北京", "02" => "天津", "03" => "河北", "04" => "山西", "05" => "内蒙古",
    "06" => "辽宁", "07" => "吉林", "08" => "黑龙江", "09" => "上海", "10" => "江苏",
    "11" => "浙江", "12" => "安徽", "13" => "福建", "14" => "江西", "15" => "山东",
    "16" => "河南", "17" => "湖北", "18" => "湖南", "19" => "广东", "20" => "广西",
    "21" => "海南", "22" => "重庆", "23" => "四川", "24" => "贵州", "25" => "云南",
    "26" => "西藏", "27" => "陕西", "28" => "甘肃", "29" => "青海", "30" => "宁夏",
    "31" => "新疆"
];

foreach ($provinceStats as &$row) {
    $row["province_name"] = $provinceMap[$row["province_code"]] ?? $row["province_code"];
}
unset($row);

// 6. 返巢率TOP公棚（如果有返巢率数据）
$loftReturnStats = [];
try {
    $loftReturnStats = $pdo->query("
        SELECT l.name, l.return_rate, l.total_pigeons
        FROM lofts l
        WHERE l.return_rate IS NOT NULL AND l.return_rate > 0
        ORDER BY l.return_rate DESC
        LIMIT 20
    ")->fetchAll();
} catch (Exception $e) {
    // 字段不存在，跳过
}

echo "<!-- 数据查询完成 -->\n";
ob_flush();
flush();

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>2026年春赛数据白皮书 - 信鸽之家</title>
    <style>
        /* =============================================
           打印样式 - PDF生成用
           ============================================= */
        @media print {
            .no-print { display: none; }
            .page-break { page-break-after: always; }
            body { font-size: 12pt; }
        }
        
        /* =============================================
           屏幕样式
           ============================================= */
        body {
            font-family: "Microsoft YaHei", "微软雅黑", SimSun, "宋体", sans-serif;
            line-height: 1.8;
            color: #333;
            max-width: 900px;
            margin: 0 auto;
            padding: 40px 20px;
            background: #fff;
        }
        
        h1 {
            color: #1a5fa8;
            border-bottom: 3px solid #1a5fa8;
            padding-bottom: 10px;
            font-size: 28pt;
        }
        
        h2 {
            color: #1a5fa8;
            border-left: 5px solid #1a5fa8;
            padding-left: 15px;
            margin-top: 40px;
            font-size: 18pt;
        }
        
        h3 {
            color: #c9a84c;
            font-size: 14pt;
            margin-top: 25px;
        }
        
        .subtitle {
            color: #666;
            font-size: 14pt;
            margin-bottom: 30px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin: 30px 0;
        }
        
        .stat-card {
            background: #f8f9fa;
            border-left: 4px solid #1a5fa8;
            padding: 20px;
            border-radius: 6px;
        }
        
        .stat-card .label {
            color: #666;
            font-size: 11pt;
        }
        
        .stat-card .value {
            color: #1a5fa8;
            font-size: 24pt;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .stat-card .unit {
            color: #999;
            font-size: 10pt;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 11pt;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 10px 12px;
            text-align: left;
        }
        
        th {
            background: #1a5fa8;
            color: #fff;
            font-weight: 600;
        }
        
        tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .bar-chart {
            margin: 20px 0;
        }
        
        .bar-item {
            display: flex;
            align-items: center;
            margin: 8px 0;
        }
        
        .bar-label {
            width: 120px;
            text-align: right;
            padding-right: 15px;
            font-size: 10pt;
        }
        
        .bar-fill {
            background: linear-gradient(90deg, #1a5fa8, #c9a84c);
            height: 24px;
            border-radius: 3px;
            min-width: 2px;
        }
        
        .bar-value {
            padding-left: 10px;
            font-size: 10pt;
            color: #666;
        }
        
        .footer-note {
            margin-top: 60px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #999;
            font-size: 10pt;
            text-align: center;
        }
        
        .website-link {
            color: #1a5fa8;
            font-weight: bold;
        }
        
        .no-print {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 30px;
        }
        
        .print-btn {
            background: #1a5fa8;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12pt;
            margin-top: 10px;
        }
        
        .print-btn:hover {
            background: #c9a84c;
        }
    </style>
</head>
<body>
    <div class="no-print">
        <strong>📊 数据报告已生成</strong>
        <p>点击下方按钮打印为PDF，或直接使用浏览器打印功能（Ctrl+P / Cmd+P）</p>
        <button class="print-btn" onclick="window.print()">打印 / 另存为PDF</button>
        <p><small>提示：打印时选择"另存为PDF"，取消勾选"页眉和页脚"以获得最佳效果</small></p>
    </div>
    
    <!-- 封面 -->
    <h1>2026年春赛数据白皮书</h1>
    <p class="subtitle">基于全国1270万条赛事记录的数据分析报告</p>
    
    <p>本报告由信鸽之家（www.xgjia.com）数据分析团队编制，基于2026年全国春季赛鸽赛事的真实成绩数据，旨在为鸽友、公棚、行业研究者提供客观的数据参考。</p>
    
    <p><strong>数据来源</strong>：中国信鸽协会官方发布、各公棚赛事成绩公告</p>
    <p><strong>数据规模</strong>：<?php echo number_format($stats["total_records"]); ?>条赛事记录</p>
    <p><strong>报告生成时间</strong>：<?php echo date("Y年m月d日"); ?></p>
    
    <div class="page-break"></div>
    
    <!-- 第一章：数据概览 -->
    <h2>一、数据概览</h2>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="label">总赛事记录</div>
            <div class="value"><?php echo number_format($stats["total_records"]); ?></div>
            <div class="unit">条（覆盖历年）</div>
        </div>
        
        <div class="stat-card">
            <div class="label">2026春赛记录</div>
            <div class="value"><?php echo number_format($stats["records_2026"]); ?></div>
            <div class="unit">条</div>
        </div>
        
        <div class="stat-card">
            <div class="label">参赛鸽子</div>
            <div class="value"><?php echo number_format($stats["pigeons_2026"]); ?></div>
            <div class="unit">只（去重足环号）</div>
        </div>
        
        <div class="stat-card">
            <div class="label">参赛鸽友</div>
            <div class="value"><?php echo number_format($stats["owners_2026"]); ?></div>
            <div class="unit">人</div>
        </div>
        
        <div class="stat-card">
            <div class="label">收录公棚</div>
            <div class="value"><?php echo number_format($stats["lofts_count"]); ?></div>
            <div class="unit">家</div>
        </div>
        
        <div class="stat-card">
            <div class="label">平均分速</div>
            <div class="value"><?php echo $stats["avg_speed"]; ?></div>
            <div class="unit">米/分钟</div>
        </div>
    </div>
    
    <div class="page-break"></div>
    
    <!-- 第二章：分速分析 -->
    <h2>二、分速分析</h2>
    
    <p>分速（米/分钟）是衡量赛鸽飞行速度的核心指标，计算公式为：<strong>分速 = 比赛距离（米）÷ 飞行时间（分钟）</strong>。</p>
    
    <h3>基本统计</h3>
    <table>
        <tr><th>指标</th><th>数值</th><th>说明</th></tr>
        <tr><td>平均分速</td><td><?php echo $stats["avg_speed"]; ?> 米/分</td><td>2026春赛所有有效记录的平均值</td></tr>
        <tr><td>最高分速</td><td><?php echo $stats["max_speed"]; ?> 米/分</td><td>2026春赛最快记录</td></tr>
        <tr><td>最低分速</td><td><?php echo $stats["min_speed"]; ?> 米/分</td><td>2026春赛最慢有效记录</td></tr>
    </table>
    
    <h3>分速分布</h3>
    <p>2026春赛成绩的分速分布情况：</p>
    
    <div class="bar-chart">
        <?php 
        $maxCnt = max(array_column($speedDistribution, "cnt"));
        foreach ($speedDistribution as $row): 
            $width = ($row["cnt"] / $maxCnt) * 100;
        ?>
        <div class="bar-item">
            <div class="bar-label"><?php echo $row["speed_range"]; ?> 米/分</div>
            <div class="bar-fill" style="width: <?php echo $width; ?>%;"></div>
            <div class="bar-value"><?php echo number_format($row["cnt"]); ?> 条</div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div class="page-break"></div>
    
    <!-- 第三章：TOP100分析 -->
    <?php if (!empty($top100)): ?>
    <h2>三、2026春赛分速TOP100</h2>
    
    <p>以下为2026年春赛分速排名前100的赛鸽记录：</p>
    
    <table>
        <tr><th>排名</th><th>足环号</th><th>分速（米/分）</th><th>公棚/赛事</th></tr>
        <?php foreach ($top100 as $row): ?>
        <tr>
            <td><?php echo $row["rank"]; ?></td>
            <td><?php echo htmlspecialchars($row["ring_number"]); ?></td>
            <td><?php echo round($row["speed"], 2); ?></td>
            <td><?php echo htmlspecialchars($row["loft_name"] ?? "-"); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>
    
    <div class="page-break"></div>
    
    <!-- 第四章：省份分析 -->
    <h2>四、省份参赛规模分析</h2>
    
    <p>按足环号地区编码统计的2026春赛参赛规模：</p>
    
    <table>
        <tr><th>排名</th><th>省份</th><th>参赛记录数</th><th>占比</th></tr>
        <?php 
        $totalForPercent = array_sum(array_column($provinceStats, "cnt"));
        $rank = 1;
        foreach ($provinceStats as $row): 
            $percent = round(($row["cnt"] / $totalForPercent) * 100, 2);
        ?>
        <tr>
            <td><?php echo $rank++; ?></td>
            <td><?php echo $row["province_name"]; ?></td>
            <td><?php echo number_format($row["cnt"]); ?></td>
            <td><?php echo $percent; ?>%</td>
        </tr>
        <?php endforeach; ?>
    </table>
    
    <div class="page-break"></div>
    
    <!-- 第五章：公棚分析 -->
    <?php if (!empty($loftReturnStats)): ?>
    <h2>五、公棚返巢率分析</h2>
    
    <p>返巢率是衡量公棚比赛难度和管理水平的重要指标。以下是返巢率TOP20的公棚：</p>
    
    <table>
        <tr><th>排名</th><th>公棚名称</th><th>返巢率</th><th>参赛规模</th></tr>
        <?php 
        $rank = 1;
        foreach ($loftReturnStats as $row): 
            $returnRate = round($row["return_rate"] * 100, 2);
        ?>
        <tr>
            <td><?php echo $rank++; ?></td>
            <td><?php echo htmlspecialchars($row["name"]); ?></td>
            <td><?php echo $returnRate; ?>%</td>
            <td><?php echo number_format($row["total_pigeons"]); ?> 羽</td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>
    
    <!-- 附录 -->
    <div class="page-break"></div>
    <h2>附录：关于信鸽之家</h2>
    
    <p>信鸽之家（www.xgjia.com）是专业的赛鸽数据分析平台，提供以下服务：</p>
    <ul>
        <li><strong>足环号深度查询</strong>：输入足环号，查询鸽子完整赛绩记录</li>
        <li><strong>血统证书生成</strong>：在线生成标准格式血统证书PDF</li>
        <li><strong>公棚对比工具</strong>：多维度对比公棚数据，辅助决策</li>
        <li><strong>分速TOP100排行榜</strong>：实时更新的最快赛鸽排名</li>
    </ul>
    
    <p>网站收录了全国1270万+条赛事记录、550+家公棚、10000+只铭鸽数据，是鸽友查询成绩、研究数据、引种决策的好帮手。</p>
    
    <div class="footer-note">
        <p>© 2026 信鸽之家 www.xgjia.com | 数据仅供参考，请以官方发布为准</p>
        <p>联系微信：pigeon_cs | 邮箱：service@xgjia.com</p>
    </div>
</body>
</html>