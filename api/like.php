<?php
/**
 * 信鸽之家 - 点赞API
 * 
 * 功能：点赞/取消点赞文章或商品
 * 方法：POST
 * 参数：
 *   - type: article|product
 *   - id: 文章或商品ID
 */

require_once __DIR__ . '/../app/config/config.php';

// 检查登录
if (!isset($_SESSION['user_id'])) {
    json_response(null, 401, '请先登录');
}

$user_id = $_SESSION['user_id'];
$pdo = get_db_connection();

// 获取请求数据
$data = json_decode(file_get_contents('php://input'), true);
if (empty($data)) {
    $data = $_POST;
}

$type = $data['type'] ?? '';
$id = intval($data['id'] ?? 0);

if (!in_array($type, ['article', 'product'])) {
    json_response(null, 400, '类型错误');
}

if ($id <= 0) {
    json_response(null, 400, 'ID错误');
}

// 检查是否已点赞
$table = $type === 'article' ? 'article_likes' : 'product_likes';
$stmt = $pdo->prepare("SELECT id FROM $table WHERE user_id = ? AND {$type}_id = ?");
$stmt->execute([$user_id, $id]);
$existing = $stmt->fetch();

if ($existing) {
    // 取消点赞
    $stmt = $pdo->prepare("DELETE FROM $table WHERE id = ?");
    $stmt->execute([$existing['id']]);
    
    // 更新计数
    $count_table = $type === 'article' ? 'articles' : 'products';
    $stmt = $pdo->prepare("UPDATE $count_table SET likes = likes - 1 WHERE id = ?");
    $stmt->execute([$id]);
    
    json_response(['liked' => false], 200, '已取消点赞');
} else {
    // 添加点赞
    $stmt = $pdo->prepare("INSERT INTO $table (user_id, {$type}_id, created_at) VALUES (?, ?, NOW())");
    $stmt->execute([$user_id, $id]);
    
    // 更新计数
    $count_table = $type === 'article' ? 'articles' : 'products';
    $stmt = $pdo->prepare("UPDATE $count_table SET likes = likes + 1 WHERE id = ?");
    $stmt->execute([$id]);
    
    json_response(['liked' => true], 200, '点赞成功');
}
