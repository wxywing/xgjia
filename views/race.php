<?php
/**
 * 信鸽之家 - 赛事详情页（SEO优化版）
 */

require_once dirname(__DIR__) . '/app/config/config.php';

// $data 由 Controller::loadView() 提取
extract($data);

$page_title = $pageTitle ?? (h($race['title']) . ' | ' . ($race['race_date'] ?? '') . ' ' . ($race['location'] ?? '') . ' | ' . SITE_NAME);

// SEO 元信息
$meta_description = h($race['title']);
if (!empty($race['race_date'])) {
    $meta_description .= '，比赛时间：' . h($race['race_date']);
}
if (!empty($race['location'])) {
    $meta_description .= '，比赛地点：' . h($race['location']);
}
$meta_description .= '。查看赛事详情、参赛鸽数、获奖名单，信鸽之家赛事频道。';

$meta_keywords = h($race['title']) . ',' . ($race['location'] ?? '') . ',信鸽赛事,' . KEYWORDS_RACES;

$og_url = 'https://www.xgjia.com/race/' . $race['id'] . '.html';

// JSON-LD - Event
$ld_event = [
    '@context' => 'https://schema.org',
    '@type' => 'Event',
    'name' => $race['title'],
    'description' => $race['description'] ?? $meta_description,
];
if (!empty($race['race_date'])) {
    $ld_event['startDate'] = $race['race_date'];
}
if (!empty($race['location'])) {
    $ld_event['location'] = [
        '@type' => 'Place',
        'name' => $race['location'],
    ];
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo h($page_title); ?></title>
    
    <!-- SEO Meta -->
    <meta name="description" content="<?php echo h($meta_description); ?>">
    <meta name="keywords" content="<?php echo h($meta_keywords); ?>">
    <link rel="canonical" href="<?php echo h($og_url); ?>">
    
    <!-- Open Graph -->
    <meta property="og:type" content="event">
    <meta property="og:title" content="<?php echo h($page_title); ?>">
    <meta property="og:description" content="<?php echo h($meta_description); ?>">
    <meta property="og:url" content="<?php echo h($og_url); ?>">
    <meta property="og:site_name" content="<?php echo SITE_NAME; ?>">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="<?php echo h($page_title); ?>">
    <meta name="twitter:description" content="<?php echo h($meta_description); ?>">
    
    <!-- JSON-LD -->
    <script type="application/ld+json"><?php echo json_encode($ld_event, JSON_UNESCAPED_UNICODE); ?></script>

    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/b-scheme.css">
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="/public/images/favicon.ico">
    
    <style>
            :root {
                --primary: #1a5fa8;
                --primary-light: #2980b9;
                --primary-dark: #154360;
                --accent: #c9a84c;
                --accent-light: #e0c060;
                --bg: #f4f6f9;
                --white: #ffffff;
                --text: #2c3e50;
                --text-light: #6c7a89;
                --border: #e8ecf0;
                --shadow: 0 2px 12px rgba(26,95,168,0.08);
                --shadow-hover: 0 8px 30px rgba(26,95,168,0.15);
                --gold: #d4a843;
                --success: #27ae60;
                --danger: #e74c3c;
                --radius: 12px;
            }

        /* 详情页样式 */
        .detail-header {
            background: linear-gradient(135deg, var(--primary) 0%, #1e40af 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 30px;
        }
        
        .detail-content {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 30px;
        }
        
        @media (max-width: 1024px) {
            .detail-content {
                grid-template-columns: 1fr;
            }
        }
        
        .main-content {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
        }
        
        .race-title {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .race-title {
                font-size: 24px;
            }
        }
        
        .race-status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            margin-bottom: 20px;
        }
        
        .status-upcoming {
            background-color: #dbeafe;
            color: #1e40af;
        }
        
        .status-ongoing {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .status-finished {
            background-color: var(--gray-200);
            color: var(--gray-600);
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }
        
        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .info-item {
            display: flex;
            padding: 15px;
            background-color: var(--gray-50);
            border-radius: var(--border-radius);
        }
        
        .info-label {
            color: var(--gray-600);
            min-width: 100px;
        }
        
        .info-value {
            flex: 1;
            font-weight: bold;
        }
        
        .race-content {
            line-height: 1.8;
            color: var(--gray-700);
        }
        
        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .sidebar-box {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 20px;
        }
        
        .sidebar-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--primary);
        }
        
        .related-item {
            padding: 12px 0;
            border-bottom: 1px solid var(--gray-200);
        }
        
        .related-item:last-child {
            border-bottom: none;
        }
        
        .related-item a {
            color: var(--gray-800);
            text-decoration: none;
        }
        
        .related-item a:hover {
            color: var(--primary);
        }
        
        .related-meta {
            font-size: 12px;
            color: var(--gray-500);
            margin-top: 5px;
        }
        
        .countdown-box {
            background-color: var(--primary);
            color: white;
            padding: 20px;
            border-radius: var(--border-radius);
            text-align: center;
        }
        
        .countdown-label {
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .countdown-value {
            font-size: 32px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/_head.php'; ?>
<!-- 详情头部 -->
    <div class="detail-header">
        <div class="container">
            <div class="flex items-center gap-2 mb-2">
                <a href="/race/" class="text-white hover:text-yellow-300">
                    <i class="fas fa-calendar-alt mr-1"></i>赛事日历
                </a>
                <i class="fas fa-chevron-right text-sm"></i>
                <span>赛事详情</span>
            </div>
            <h1 class="text-3xl font-bold"><?php echo h($race['title']); ?></h1>
        </div>
    </div>

    <!-- 主内容区 -->
    <div class="container">
        <div class="detail-content">
            <!-- 左侧主内容 -->
            <div class="main-content">
                <h2 class="race-title"><?php echo h($race['title']); ?></h2>
                
                <?php
                $statusClass = '';
                $statusText = '';
                switch ($race['status']) {
                    case 1:
                        $statusClass = 'status-upcoming';
                        $statusText = '报名中';
                        break;
                    case 2:
                        $statusClass = 'status-ongoing';
                        $statusText = '进行中';
                        break;
                    case 3:
                        $statusClass = 'status-finished';
                        $statusText = '已结束';
                        break;
                }
                ?>
                <span class="race-status <?php echo $statusClass; ?>">
                    <i class="fas fa-circle mr-1"></i><?php echo $statusText; ?>
                </span>

                <!-- 基本信息 -->
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-calendar mr-2"></i>比赛时间：</div>
                        <div class="info-value"><?php echo date('Y-m-d', strtotime($race['race_date'])); ?></div>
                    </div>
                    
                    <?php if (!empty($race['location'])): ?>
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-map-marker-alt mr-2"></i>比赛地点：</div>
                        <div class="info-value"><?php echo h($race['location']); ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($race['organizer'])): ?>
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-building mr-2"></i>主办方：</div>
                        <div class="info-value"><?php echo h($race['organizer']); ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($race['distance'])): ?>
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-route mr-2"></i>比赛空距：</div>
                        <div class="info-value"><?php echo h($race['distance']); ?> 公里</div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- 赛事详情 -->
                <?php if (!empty($race['description'])): ?>
                <div class="race-content">
                    <h3 class="font-bold mb-3">赛事详情</h3>
                    <p><?php echo nl2br(h($race['description'])); ?></p>
                </div>
                <?php endif; ?>
            </div>

            <!-- 右侧边栏 -->
            <div class="sidebar">
                <!-- 倒计时 -->
                <?php if ($race['status'] == 1): ?>
                <div class="countdown-box">
                    <div class="countdown-label">距离比赛开始</div>
                    <div class="countdown-value" id="countdown">
                        <?php
                        $raceTime = strtotime($race['race_date']);
                        $now = time();
                        $diff = $raceTime - $now;
                        $days = floor($diff / 86400);
                        echo $days > 0 ? $days . '天' : '已开始';
                        ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- 相关赛事 -->
                <?php if (!empty($relatedRaces)): ?>
                <div class="sidebar-box">
                    <h3 class="sidebar-title">相关赛事</h3>
                    <ul>
                        <?php foreach ($relatedRaces as $related): ?>
                        <li class="related-item">
                            <a href="/race/<?php echo $related['id']; ?>.html">
                                <?php echo h($related['title']); ?>
                            </a>
                            <div class="related-meta">
                                <?php echo date('Y-m-d', strtotime($related['race_date'])); ?>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>


    <?php include __DIR__ . '/_footer.php'; ?>
