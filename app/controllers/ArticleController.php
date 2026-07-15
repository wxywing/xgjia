<?php
require_once __DIR__ . '/../models/Article.php';
require_once __DIR__ . '/../models/Category.php';

/**
 * 文章控制器
 */
class ArticleController extends Controller {

    /**
     * 文章列表
     */
    public function list() {
        $articleModel = new Article($this->pdo);
        $categoryModel = new Category($this->pdo);

        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $categoryId = null;
        $categorySlug = isset($_GET['category']) ? $_GET['category'] : null;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        // 支持分类slug或数字ID
        if ($categorySlug) {
            if (is_numeric($categorySlug)) {
                $categoryId = (int)$categorySlug;
            } else {
                $cat = $categoryModel->findBySlug($categorySlug, 1);
                $categoryId = $cat ? $cat['id'] : null;
            }
        }

        $options = [
            'limit' => $limit,
            'offset' => $offset
        ];

        if ($categoryId) {
            $options['category_id'] = $categoryId;
        }

        $articles = $articleModel->getList($options);
        $total = $articleModel->getCount($options);
        $totalPages = ceil($total / $limit);

        // 热门文章(按浏览量排序)
        $hotArticles = $articleModel->getHot(8);

        // 分类列表(含文章数统计)
        $categories = $categoryModel->getListWithCount(['type' => 1]);

        $data = [
            'articles' => $articles,
            'categories' => $categories,
            'hotArticles' => $hotArticles,
            'currentCategory' => $categoryId,
            'currentCategorySlug' => $categorySlug,
            'page' => $page,
            'totalPages' => $totalPages,
            'pageTitle' => '文章列表 | ' . SITE_NAME
        ];

        $this->loadView('articles', $data);
    }

    /**
     * 文章详情
     */
    public function detail($id) {
        $articleModel = new Article($this->pdo);
        $categoryModel = new Category($this->pdo);

        $article = $articleModel->findById($id);

        if (!$article) {
            http_response_code(404);
            $this->loadView('404', ['pageTitle' => '页面未找到']);
            return;
        }

        // 增加浏览次数
        $articleModel->incrementViews($id);

        // 获取相关文章
        $relatedArticles = [];
        if ($article['category_id']) {
            $relatedArticles = $articleModel->getList([
                'category_id' => $article['category_id'],
                'limit' => 5
            ]);
        }

        $categories = $categoryModel->getListWithCount(['type' => 1]);

        // 上一篇 / 下一篇（同分类）
        $prevArticle = null;
        $nextArticle = null;
        if ($article['category_id']) {
            $stmt = $this->pdo->prepare(
                "(SELECT id, title FROM articles WHERE category_id = ? AND id < ? AND status = 1 ORDER BY id DESC LIMIT 1)
                 UNION ALL
                 (SELECT id, title FROM articles WHERE category_id = ? AND id > ? AND status = 1 ORDER BY id ASC LIMIT 1)"
            );
            $stmt->execute([$article['category_id'], $id, $article['category_id'], $id]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $prevArticle = $rows[0] ?? null;
            $nextArticle = $rows[1] ?? null;
        }

        $data = [
            'article' => $article,
            'relatedArticles' => $relatedArticles,
            'categories' => $categories,
            'prevArticle' => $prevArticle,
            'nextArticle' => $nextArticle,
            'pageTitle' => $article['title'] . ' | ' . SITE_NAME
        ];

        $this->loadView('article', $data);
    }

    /**
     * 发布文章页面
     */
    public function create() {
        // 检查登录
        if (!isset($_SESSION['user_id'])) {
            header('Location: /auth?action=login');
            exit;
        }

        // 会员权限检查
        require_once __DIR__ . '/../core/MembershipGuard.php';
        $check = MembershipGuard::check($this->pdo, $_SESSION['user_id'], 'canPublish', 'article');

        $categoryModel = new Category($this->pdo);
        $categories = $categoryModel->getList(['type' => 1]);

        $data = [
            'categories' => $categories,
            'publish_check' => $check,
            'pageTitle' => '发布文章 | ' . SITE_NAME
        ];

        $this->loadView('article_create', $data);
    }

    /**
     * 保存文章
     */
    public function store() {
        if (!isset($_SESSION['user_id'])) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '请先登录', 'code' => 'not_logged_in']);
            exit;
            return;
        }

        // 会员权限检查
        require_once __DIR__ . '/../core/MembershipGuard.php';
        $check = MembershipGuard::check($this->pdo, $_SESSION['user_id'], 'canPublish', 'article');

        if (!$check['allowed']) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => $check['message'], 'code' => $check['code']]);
            exit;
            return;
        }

        $articleModel = new Article($this->pdo);

        $data = [
            'user_id' => $_SESSION['user_id'],
            'category_id' => $_POST['category_id'],
            'title' => $_POST['title'],
            'summary' => $_POST['summary'] ?? '',
            'content' => $_POST['content'],
            'cover' => $_POST['cover'] ?? '',
            'status' => 1
        ];

        $result = $articleModel->create($data);

        if ($result) {
            // 消耗配额
            MembershipGuard::check($this->pdo, $_SESSION['user_id'], 'consume', 'article');

            // 自动打标签
            $articleModel->syncArticleTags($result, $data['title'], $data['content']);

            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => true, 'message' => '发布成功']);
            exit;
        } else {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '发布失败']);
            exit;
        }
    }
}
