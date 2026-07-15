<?php
/**
 * 信鸽之家 - 评论API
 * 
 * 功能：提交评论
 * 方法：POST
 * 参数：
 *   - article_id: 文章ID
 *   - content: 评论内容
 *   - parent_id: 父评论ID（可选，用于回复）
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

$article_id = intval($data['article_id'] ?? 0);
$content = trim($data['content'] ?? '');
$parent_id = intval($data['parent_id'] ?? 0);

if ($article_id <= 0) {
    json_response(null, 400, '文章ID错误');
}

if (empty($content)) {
    json_response(null, 400, '评论内容不能为空');
}

if (mb_strlen($content) > 500) {
    json_response(null, 400, '评论内容不能超过500字');
}

// 检查文章是否存在
$stmt = $pdo->prepare("SELECT id, title FROM articles WHERE id = ? AND status = 1");
$stmt->execute([$article_id]);
$article = $stmt->fetch();

if (!$article) {
    json_response(null, 404, '文章不存在或已下架');
}

// 提交评论
$stmt = $pdo->prepare("
    INSERT INTO comments (article_id, user_id, content, parent_id, status, created_at) 
    VALUES (?, ?, ?, ?, 1, NOW())
");
$stmt->execute([$article_id, $user_id, $content, $parent_id ?: null]);

// 更新文章评论数
$stmt = $pdo->prepare("UPDATE articles SET comments = comments + 1 WHERE id = ?");
$stmt->execute([$article_id]);

// 获取用户信息（用于返回）
$stmt = $pdo->prepare("SELECT nickname, avatar FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

json_response([
    'nickname' => $user['nickname'] ?: '匿名',
    'avatar' => $user['avatar'] ?: '/public/images/default-avatar.png',
    'content' => $content,
    'created_at' => date('Y-m-d H:i:s')
], 200, '评论成功');
