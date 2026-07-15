<?php
require_once __DIR__ . '/../models/Article.php';
require_once __DIR__ . '/../models/Pigeon.php';
require_once __DIR__ . '/../models/Category.php';

/**
 * 搜索控制器
 */
class SearchController extends Controller {
    
    /**
     * 搜索页面
     */
    public function index() {
        $keyword = $_GET['keyword'] ?? '';
        $type = $_GET['type'] ?? 'all';
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $articles = [];
        $pigeons = [];
        $totalArticles = 0;
        $totalPigeons = 0;
        
        if (!empty($keyword)) {
            $articleModel = new Article($this->pdo);
            $pigeonModel = new Pigeon($this->pdo);
            
            // 搜索文章
            if ($type === 'all' || $type === 'article') {
                $articles = $articleModel->getList([
                    'keyword' => $keyword,
                    'limit' => $limit,
                    'offset' => $offset
                ]);
                $totalArticles = $articleModel->getCount(['keyword' => $keyword]);
            }
            
            // 搜索铭鸽
            if ($type === 'all' || $type === 'pigeon') {
                $pigeons = $pigeonModel->getList([
                    'keyword' => $keyword,
                    'limit' => $limit,
                    'offset' => $offset
                ]);
                $totalPigeons = $pigeonModel->getCount(['keyword' => $keyword]);
            }
        }
        
        $total = $totalArticles + $totalPigeons;
        $totalPages = ceil($total / $limit);
        
        $data = [
            'keyword' => $keyword,
            'type' => $type,
            'articles' => $articles,
            'pigeons' => $pigeons,
            'totalArticles' => $totalArticles,
            'totalPigeons' => $totalPigeons,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'pageTitle' => '搜索结果 | ' . SITE_NAME
        ];
        
        $this->loadView('search', $data);
    }
    
    /**
     * 搜索建议（AJAX）
     */
    public function suggest() {
        $keyword = $_GET['keyword'] ?? '';
        
        if (strlen($keyword) < 2) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['suggestions' => []]);
            exit;
            return;
        }
        
        $articleModel = new Article($this->pdo);
        $pigeonModel = new Pigeon($this->pdo);
        
        $suggestions = [];
        
        // 搜索文章标题
        $articles = $articleModel->getList([
            'keyword' => $keyword,
            'limit' => 5
        ]);
        
        foreach ($articles as $article) {
            $suggestions[] = [
                'type' => 'article',
                'id' => $article['id'],
                'title' => $article['title'],
                'url' => '/article/' . $article['id'] . '.html'
            ];
        }
        
        // 搜索铭鸽名称
        $pigeons = $pigeonModel->getList([
            'keyword' => $keyword,
            'limit' => 5
        ]);
        
        foreach ($pigeons as $pigeon) {
            $suggestions[] = [
                'type' => 'pigeon',
                'id' => $pigeon['id'],
                'title' => $pigeon['name'],
                'url' => '/pigeon/' . $pigeon['id'] . '.html'
            ];
        }
        
        header("Content-Type: application/json; charset=utf-8");
        echo json_encode(['suggestions' => $suggestions]);
        exit;
    }
}
