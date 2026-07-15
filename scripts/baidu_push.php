<?php
/**
 * 信鸽之家 - 多引擎 URL 推送脚本（百度 + IndexNow）
 * 
 * 功能：主动向百度(Baidu)和 IndexNow(Bing+Yandex+Seznam)提交 URLs，提升收录速度
 * 百度搜索资源平台：https://ziyuan.baidu.com/crawl/index
 * IndexNow：https://www.indexnow.org
 * 
 * 用法：
 *   php baidu_push.php --daily         # 每日轮转推送（推荐cron用这个）
 *   php baidu_push.php --all           # 推送 sitemap 所有 URL
 *   php baidu_push.php --recent 24     # 推送最近 24 小时新产生的 URL
 *   php baidu_push.php --setup         # 查看配置说明
 * 
 * 密钥文件：
 *   scripts/.baidu_token    — 百度推送 token
 *   scripts/.indexnow_key   — IndexNow API key（32位十六进制）
 * 
 * 建议 cron（宝塔面板 → 计划任务 → Shell脚本）：
 *   0 3 * * *  php /www/wwwroot/www.xgjia.com/scripts/baidu_push.php --daily
 */

require_once __DIR__ . '/../app/config/config.php';

// ===== 配置区 =====
$CONFIG = [
    // 百度搜索资源平台 API Token（从 https://ziyuan.baidu.com/zdys_url/push 获取）
    // ⚠️ 首次使用需先在百度搜索资源平台添加网站并验证所有权
    'baidu_token'  => getenv('BAIDU_TOKEN') ?: '',  // 通过环境变量设置，或直接填入下方的 '' 中
    'baidu_site'   => 'https://www.xgjia.com',        // 站点域名（需与百度平台填写的完全一致）
    'batch_size'   => 100,                            // 每批推送数量（百度限制单次最多2000条）
    'log_file'     => __DIR__ . '/baidu_push.log',    // 推送日志
    
    // IndexNow 配置（Bing + Yandex + Seznam，日配额10000条）
    // 注册: https://www.bing.com/webmasters → 添加站点 → API Access → Generate API Key
    'indexnow_key'  => getenv('INDEXNOW_KEY') ?: '',
    'indexnow_host' => 'www.xgjia.com',               // 不带协议前缀
];

// 如果 Baidu token 未设置，尝试从文件读取
$token_file = __DIR__ . '/.baidu_token';
if (empty($CONFIG['baidu_token']) && file_exists($token_file)) {
    $CONFIG['baidu_token'] = trim(file_get_contents($token_file));
}

// 如果 IndexNow key 未设置，尝试从文件读取
$indexnow_key_file = __DIR__ . '/.indexnow_key';
if (empty($CONFIG['indexnow_key']) && file_exists($indexnow_key_file)) {
    $CONFIG['indexnow_key'] = trim(file_get_contents($indexnow_key_file));
}

// ===== 参数解析 =====
$argv = $argv ?? [];
$mode = 'help';
$daily_count = 10;  // daily 模式每次推送数量
$recent_hours = 24;
$custom_urls = [];

if (in_array('--setup', $argv)) {
    $mode = 'setup';
} elseif (in_array('--all', $argv)) {
    $mode = 'all';
} elseif (in_array('--recent', $argv)) {
    $mode = 'recent';
    $idx = array_search('--recent', $argv);
    if (isset($argv[$idx + 1]) && is_numeric($argv[$idx + 1])) {
        $recent_hours = intval($argv[$idx + 1]);
    }
} elseif (in_array('--daily', $argv)) {
    $mode = 'daily';
} elseif (in_array('--urls', $argv)) {
    $mode = 'custom';
    $idx = array_search('--urls', $argv);
    if (isset($argv[$idx + 1])) {
        $custom_urls = explode("\n", $argv[$idx + 1]);
    }
}

// ===== Token 检查 =====
if (empty($CONFIG['baidu_token'])) {
    echo "⚠️ 未配置百度 Token\n";
    echo "请选择以下任一方式配置：\n\n";
    show_setup();
    exit(1);
}

// ===== 日志函数 =====
function push_log($msg) {
    global $CONFIG;
    $ts = date('Y-m-d H:i:s');
    $line = "[{$ts}] {$msg}\n";
    file_put_contents($CONFIG['log_file'], $line, FILE_APPEND);
    echo $msg . "\n";
}

// ===== Setup 说明 =====
function show_setup() {
    global $CONFIG;
    echo <<<HELP
📋 多引擎 URL 推送配置

══════════════════ 百度推送 ══════════════════

1. 访问 https://ziyuan.baidu.com/crawl/index 登录百度账号
2. 添加并验证您的网站（支持 HTML 文件验证 / TXT 记录验证）
3. 进入「链接提交」→「自动提交」→「快速收录」或「普通收录」
4. 复制「接口调用地址」中的 token 参数值

配置百度 token：
  echo "你的baidu_token值" > scripts/.baidu_token
  chmod 600 scripts/.baidu_token

══════════════ IndexNow（Bing + Yandex + Seznam）══════════════

1. 访问 https://www.bing.com/webmasters 用微软账号登录
2. 添加站点 www.xgjia.com（验证所有权）
3. 左侧菜单 → Settings → API Access → Generate API Key
4. 复制生成的 32 位 Key
5. 将 key 文件上传到网站根目录：https://www.xgjia.com/{key}.txt
   （例如 key 为 a1b2c3d4，则上传 a1b2c3d4.txt 到网站根目录）

配置 IndexNow key：
  echo "你的indexnow_key" > scripts/.indexnow_key
  chmod 600 scripts/.indexnow_key

══════════════ 环境变量方式（可选） ══════════════






  export BAIDU_TOKEN="..."
  export INDEXNOW_KEY="..."

HELP;
    echo "当前配置:\n";
    echo "  百度 Token: " . (empty($CONFIG['baidu_token']) ? '❌ 未配置' : '✅ 已配置') . "\n";
    echo "  IndexNow Key: " . (empty($CONFIG['indexnow_key']) ? '❌ 未配置' : '✅ 已配置') . "\n";
}

// ===== 获取 sitemap 所有 URL =====
function get_sitemap_urls($pdo, $site_url) {
    $urls = [];
    
    // 1. 首页
    $urls[] = $site_url . '/';
    
    // 2. 列表页
    $list_pages = [
        '/articles' => 0.8,
        '/races' => 0.9,
        '/lofts' => 0.8,
        '/pigeons' => 0.8,
        '/pedigree' => 0.7,
        '/race/champions' => 0.7,
        '/race/browse' => 0.9,
        '/race/provinces' => 0.7,
        '/race/cities' => 0.7,
        '/tools/top100' => 0.8,
        '/tools/ring-guide' => 0.7,
        '/strains' => 0.7,
    ];
    foreach ($list_pages as $path => $prio) {
        $urls[] = $site_url . $path;
    }
    
    // 3. 文章详情（最新50篇）
    $stmt = $pdo->query('SELECT id FROM articles WHERE status = 1 ORDER BY id DESC LIMIT 50');
    while ($row = $stmt->fetch()) {
        $urls[] = $site_url . '/article/' . $row['id'] . '.html';
    }
    
    // 4. 赛事详情（当前赛季 + 最新50篇）
    $stmt = $pdo->query('SELECT id FROM races WHERE season_year >= 2025 ORDER BY id DESC LIMIT 100');
    while ($row = $stmt->fetch()) {
        $urls[] = $site_url . '/race/' . $row['id'] . '.html';
    }
    
    // 5. 省份页
    $stmt = $pdo->query("
        SELECT DISTINCT province 
        FROM pigeon_lofts 
        WHERE province IS NOT NULL AND province != '' 
        ORDER BY province
    ");
    while ($row = $stmt->fetch()) {
        $urls[] = $site_url . '/race/province/' . urlencode($row['province']) . '/';
    }
    
    // 6. 公棚详情（最新50个）
    $stmt = $pdo->query('SELECT id FROM pigeon_lofts ORDER BY id DESC LIMIT 50');
    while ($row = $stmt->fetch()) {
        $urls[] = $site_url . '/loft/' . $row['id'] . '.html';
    }
    
    return $urls;
}

// ===== 获取最近更新的 URL =====
function get_recent_urls($pdo, $site_url, $hours) {
    $cutoff = date('Y-m-d H:i:s', strtotime("-{$hours} hours"));
    $urls = [];
    
    // 最近更新的文章
    $stmt = $pdo->prepare('SELECT id FROM articles WHERE status = 1 AND created_at >= ? ORDER BY id DESC');
    $stmt->execute([$cutoff]);
    while ($row = $stmt->fetch()) {
        $urls[] = $site_url . '/article/' . $row['id'] . '.html';
    }
    
    // 最近添加的赛事
    $stmt = $pdo->prepare('SELECT id FROM races WHERE created_at >= ? OR release_time >= ? ORDER BY id DESC');
    $stmt->execute([$cutoff, $cutoff]);
    while ($row = $stmt->fetch()) {
        $urls[] = $site_url . '/race/' . $row['id'] . '.html';
    }
    
    return array_unique($urls);
}

// ===== Daily 轮转推送（新） =====
$state_file = __DIR__ . '/.baidu_push_state.json';

function load_push_state() {
    global $state_file;
    if (file_exists($state_file)) {
        $data = json_decode(file_get_contents($state_file), true);
        return is_array($data) ? $data : ['urls' => [], 'last_run' => null];
    }
    return ['urls' => [], 'last_run' => null];
}

function save_push_state($state) {
    global $state_file;
    file_put_contents($state_file, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}

function get_daily_url_pool($pdo, $site_url) {
    $pool = [];  // url => ['priority' => int, 'source' => string]

    // A组: 静态页面 + 工具页（每次轮转必选）
    $static = [
        '/' => 100,
        '/tools.php?action=top100' => 85,
        '/tools.php?action=ring_guide' => 70,
        '/pages/about' => 60,
        '/pages/help' => 55,
        '/pages/faq' => 50,
        '/pages/agreement' => 40,
        '/pages/privacy' => 40,
        '/race/browse/' => 90,
    ];
    foreach ($static as $path => $prio) {
        $pool[$site_url . $path] = ['priority' => $prio, 'source' => 'static'];
    }

    // B组: 赛事浏览分页（每页权重递减）
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM race_results WHERE season_year >= 2025");
        $total = $stmt ? $stmt->fetchColumn() : 0;
        $browse_pages = min(max(1, ceil($total / 50)), 25);
        for ($i = 2; $i <= $browse_pages; $i++) {
            $pool[$site_url . '/race/browse/page/' . $i] = ['priority' => 45 - $i, 'source' => 'race_browse'];
        }
    } catch (Exception $e) {}

    // C组: 省份页
    try {
        $stmt = $pdo->query("SELECT DISTINCT province FROM pigeon_lofts WHERE province IS NOT NULL AND province != '' ORDER BY province LIMIT 31");
        while ($stmt && ($row = $stmt->fetch())) {
            $pool[$site_url . '/race/province/' . urlencode($row['province']) . '/'] = ['priority' => 55, 'source' => 'province'];
        }
    } catch (Exception $e) {}

    // D组: 赛事详情（最新30条）
    try {
        $stmt = $pdo->query("SELECT id FROM races WHERE season_year >= 2025 ORDER BY id DESC LIMIT 30");
        while ($stmt && ($row = $stmt->fetch())) {
            $pool[$site_url . '/race/' . $row['id'] . '.html'] = ['priority' => 35, 'source' => 'race'];
        }
    } catch (Exception $e) {}

    // E组: 文章详情
    try {
        $stmt = $pdo->query("SELECT id FROM articles WHERE status = 1 ORDER BY id DESC LIMIT 20");
        while ($stmt && ($row = $stmt->fetch())) {
            $pool[$site_url . '/article/' . $row['id'] . '.html'] = ['priority' => 40, 'source' => 'article'];
        }
    } catch (Exception $e) {}

    return $pool;
}

function pick_daily_urls($pool, $state, $count) {
    $history = $state['urls'] ?? [];
    $now = time();
    $scored = [];

    foreach ($pool as $url => $meta) {
        $history_entry = $history[$url] ?? null;
        $last_push = $history_entry['last_push'] ?? 0;
        $push_count = $history_entry['count'] ?? 0;
        $hours_since = ($now - $last_push) / 3600;

        // 得分公式: 基础优先级 + 时间奖励（越久没推越高） - 重复惩罚
        $base = $meta['priority'];
        $time_bonus = min($hours_since * 2, 50);  // 每小时+2分，上限50
        $repeat_penalty = $push_count * 5;          // 每推一次-5分
        $score = $base + $time_bonus - $repeat_penalty;

        // 首页每天必推
        $path = parse_url($url, PHP_URL_PATH);
        if ($path === '/' || $path === '') {
            $score += 200;
        }
        // 24h内推过的大幅降权
        if ($hours_since < 24) {
            $score -= 100;
        }
        // 48h内推过的降权
        elseif ($hours_since < 48) {
            $score -= 40;
        }

        $scored[$url] = $score;
    }

    // 按得分降序
    arsort($scored);
    return array_slice(array_keys($scored), 0, $count);
}

// ===== 执行百度推送 =====
function push_to_baidu($urls, $site, $token) {
    if (empty($urls)) {
        return ['success' => true, 'remain' => 0, 'msg' => '无URL需要推送'];
    }
    
    $api_url = "http://data.zz.baidu.com/urls?site={$site}&token={$token}";
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $api_url,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => implode("\n", $urls),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: text/plain'],
        CURLOPT_TIMEOUT => 30,
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return ['success' => false, 'error' => $error];
    }
    
    $data = json_decode($response, true);
    
    return [
        'success' => true,
        'http_code' => $http_code,
        'remain' => $data['remain'] ?? '?',
        'success_count' => $data['success'] ?? count($urls),
        'not_same_site' => $data['not_same_site'] ?? 0,
        'not_valid' => $data['invalid'] ?? 0,
        'response' => $data,
    ];
}

// ===== IndexNow 推送（Bing + Yandex + Seznam）=====
function push_to_indexnow($urls, $host, $key) {
    if (empty($key)) {
        return ['success' => false, 'error' => 'IndexNow key 未配置（跳过）'];
    }
    if (empty($urls)) {
        return ['success' => true, 'msg' => '无URL需要推送'];
    }
    
    $api_url = "https://api.indexnow.org/indexnow?key={$key}";
    
    $payload = json_encode([
        'host' => $host,
        'key' => $key,
        'keyLocation' => "https://{$host}/{$key}.txt",
        'urlList' => array_values($urls),
    ], JSON_UNESCAPED_SLASHES);
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $api_url,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json; charset=utf-8'],
        CURLOPT_TIMEOUT => 30,
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return ['success' => false, 'error' => $error];
    }
    
    return [
        'success' => ($http_code >= 200 && $http_code < 300),
        'http_code' => $http_code,
        'response' => $response,
        'url_count' => count($urls),
    ];
}

// ===== 主流程 =====
$pdo = get_db_connection();
$site = $CONFIG['baidu_site'];
$token = $CONFIG['baidu_token'];

if ($mode === 'setup') {
    show_setup();
    exit(0);
}

if ($mode === 'help') {
    $baidu_status = empty($CONFIG['baidu_token']) ? '❌ 未配置' : '✅ 已配置';
    $indexnow_status = empty($CONFIG['indexnow_key']) ? '❌ 未配置' : '✅ 已配置';
    echo <<<HELP
📋 多引擎 URL 推送工具（百度 + IndexNow）

用法：
  php baidu_push.php --daily         # ⭐ 每日轮转推送（百度+IndexNow）
  php baidu_push.php --recent 24     # 推送最近24小时新URL
  php baidu_push.php --all           # 推送全站URL（每周执行）
  php baidu_push.php --setup         # 查看配置说明

百度 Token: {$baidu_status}
IndexNow  Key: {$indexnow_status}

宝塔 cron 配置：
  任务类型: Shell脚本
  脚本内容: php /www/wwwroot/xgjia.com/scripts/baidu_push.php --daily
  执行周期: 每天 03:00
  说明: 每次运行自动推送百度 + IndexNow(Bing/Yandex/Seznam)
HELP;
    exit(0);
}

// 收集 URL
$urls = [];
switch ($mode) {
    case 'daily':
        $state = load_push_state();
        $pool = get_daily_url_pool($pdo, $site);
        $urls = pick_daily_urls($pool, $state, $daily_count);
        echo "📡 Daily 轮转推送模式\n";
        echo "   URL池总数: " . count($pool) . " | 今日选中: " . count($urls) . "\n";
        echo "   上次运行: " . ($state['last_run'] ?? '首次') . "\n";
        if (!empty($urls)) {
            echo "   今日URL预览:\n";
            foreach ($urls as $u) {
                echo "     → " . str_replace($site, '', $u) . "\n";
            }
        }
        break;
    case 'all':
        $urls = get_sitemap_urls($pdo, $site);
        echo "📡 准备推送全站 {$site}，共 " . count($urls) . " 个 URL\n";
        break;
    case 'recent':
        $urls = get_recent_urls($pdo, $site, $recent_hours);
        echo "📡 准备推送最近 {$recent_hours} 小时新增 URL，共 " . count($urls) . " 个 URL\n";
        break;
    case 'custom':
        $urls = $custom_urls;
        echo "📡 准备推送 " . count($urls) . " 个指定 URL\n";
        break;
}

// 去重
$urls = array_values(array_unique($urls));

if (empty($urls)) {
    echo "⚠️ 没有 URL 需要推送\n";
    exit(0);
}

// 分批推送
$batch_size = $CONFIG['batch_size'];
$batches = array_chunk($urls, $batch_size);
$total_success = 0;
$total_failed = 0;

push_log("===== 开始推送 =====");
push_log("模式: {$mode} | 站点: {$site} | Token: " . substr($token, 0, 6) . "***");
push_log("总 URL 数: " . count($urls) . " | 批次数: " . count($batches));
if ($mode === 'daily' && !empty($urls)) {
    foreach ($urls as $u) {
        push_log("  推送: " . $u);
    }
}

foreach ($batches as $i => $batch) {
    $batch_num = $i + 1;
    echo "\n[批次 {$batch_num}/" . count($batches) . "] 推送 " . count($batch) . " 个 URL...\n";
    
    $result = push_to_baidu($batch, $site, $token);
    
    if ($result['success']) {
        $success_count = $result['success_count'];
        $remain = $result['remain'];
        $not_valid = $result['not_valid'] ?? 0;
        
        echo "  ✅ 成功: {$success_count} | ";
        if ($not_valid > 0) echo "⚠️ 无效: {$not_valid} | ";
        echo "剩余配额: {$remain}\n";
        
        push_log("批次{$batch_num}: 成功{$success_count}, 无效{$not_valid}, 剩余{$remain}");
        $total_success += $success_count;
        $total_failed += $not_valid;
        
        // 配额用尽则停止
        if (is_numeric($remain) && $remain <= 0) {
            echo "⚠️ 每日配额已用尽，停止推送\n";
            push_log("配额耗尽，停止推送");
            break;
        }
    } else {
        $err = $result['error'] ?? $result['response'];
        echo "  ❌ 失败: " . (is_string($err) ? $err : json_encode($err)) . "\n";
        push_log("批次{$batch_num} 失败: " . json_encode($result));
    }
    
    // 批次间稍作延迟
    if ($i < count($batches) - 1) {
        usleep(200000); // 200ms
    }
}

push_log("===== 百度推送完成 =====");
push_log("百度总计: 成功 {$total_success}, 无效 {$total_failed}");

// ===== IndexNow 推送（Bing + Yandex + Seznam）=====
if (!empty($CONFIG['indexnow_key']) && !empty($urls)) {
    echo "\n--- IndexNow 推送（Bing + Yandex + Seznam）---\n";
    push_log("--- IndexNow 推送 ---");
    
    // IndexNow 单次最多10,000 URL，我们分批200条更稳妥
    $idx_batches = array_chunk($urls, 200);
    $idx_total = 0;
    $idx_errors = 0;
    
    foreach ($idx_batches as $j => $idx_batch) {
        $idx_result = push_to_indexnow($idx_batch, $CONFIG['indexnow_host'], $CONFIG['indexnow_key']);
        
        if ($idx_result['success']) {
            $idx_total += $idx_result['url_count'];
            echo "  ✅ 批次" . ($j + 1) . ": 已提交 " . $idx_result['url_count'] . " 个 URL (HTTP {$idx_result['http_code']})\n";
            push_log("IndexNow批次" . ($j + 1) . ": 已提交 {$idx_result['url_count']} URL, HTTP {$idx_result['http_code']}");
        } else {
            $idx_errors++;
            $err_msg = $idx_result['error'] ?? $idx_result['response'];
            echo "  ⚠️ 批次" . ($j + 1) . ": " . (is_string($err_msg) ? $err_msg : json_encode($err_msg, JSON_UNESCAPED_UNICODE)) . "\n";
            push_log("IndexNow批次" . ($j + 1) . " 失败: " . json_encode($idx_result, JSON_UNESCAPED_UNICODE));
        }
    }
    
    echo "  📊 IndexNow 汇总: 提交 {$idx_total} URL" . ($idx_errors > 0 ? ", {$idx_errors} 批失败" : '') . "\n";
    push_log("IndexNow汇总: 提交 {$idx_total} URL" . ($idx_errors > 0 ? ", {$idx_errors} 批失败" : ''));
} else {
    echo "\n⚠️ IndexNow key 未配置，跳过 Bing 推送\n";
    echo "   配置方法: echo \"你的key\" > scripts/.indexnow_key\n";
}

// ===== Daily 模式: 更新推送状态 =====
if ($mode === 'daily') {
    $now = time();
    foreach ($urls as $url) {
        if (!isset($state['urls'][$url])) {
            $state['urls'][$url] = ['count' => 0, 'first_push' => date('Y-m-d')];
        }
        $state['urls'][$url]['last_push'] = $now;
        $state['urls'][$url]['count']++;
    }
    $state['last_run'] = date('Y-m-d H:i:s');
    save_push_state($state);
    echo "📊 推送状态已更新 (" . count($state['urls']) . " 条历史记录)\n";
}

echo "\n✅ 推送完成：成功 {$total_success} 条，失败 {$total_failed} 条\n";
echo "📝 详细日志: {$CONFIG['log_file']}\n";

// 读取并显示最近日志
if (file_exists($CONFIG['log_file'])) {
    $lines = array_slice(file($CONFIG['log_file']), -10);
    echo "\n--- 最近日志 ---\n";
    foreach ($lines as $line) {
        echo $line;
    }
}
