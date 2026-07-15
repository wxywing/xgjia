<?php
require_once __DIR__ . '/../models/Article.php';
require_once __DIR__ . '/../models/Pigeon.php';
require_once __DIR__ . '/../models/Listing.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/Race.php';
require_once __DIR__ . '/../models/Loft.php';
require_once __DIR__ . '/../models/Dynamic.php';

/**
 * 首页控制器
 */
class HomeController extends Controller {
    
    public function index() {
        $articleModel = new Article($this->pdo);
        $pigeonModel = new Pigeon($this->pdo);
        $listingModel = new Listing($this->pdo);
        $categoryModel = new Category($this->pdo);
        $raceModel = new Race($this->pdo);
        $loftModel = new Loft($this->pdo);
        $dynamicModel = new Dynamic($this->pdo);
        
        // 获取最新文章
        $latestArticles = $articleModel->getList(['limit' => 6]);
        
        // 获取热门铭鸽
        $hotPigeons = $pigeonModel->getHot(10);
        
        // 获取热门分类信息（改版用）
        $hotListings = $listingModel->getHot(10);
        
        // 获取即将到来的赛事（增强版用）
        $upcomingRaces = $raceModel->getUpcoming(10);
        $hotRaces = $raceModel->getHot(5);
        
        // 获取热门公棚
        $hotLofts = $loftModel->getHot(8);
        
        // 获取鸽友圈热帖
        $hotDynamics = $dynamicModel->getList(['limit' => 6, 'status' => 1]);
        
        // 最新赛事（按发布时间倒序）
        $latestRaces = $raceModel->getList(1, 6)['list'];
        
        // 获取分类
        $categories = $categoryModel->getList(['limit' => 6]);
        
        // 平台统计数据
        $stats = [
            'users'     => $this->pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
            'articles'  => $articleModel->getCount(['status' => 1]),
            'pigeons'   => $pigeonModel->getCount(['status' => 1]),
            'listings'  => $listingModel->getCount(['status' => 1]),
            'races'     => $raceModel->getCount(),
            'lofts'     => $loftModel->getCount(),
            'dynamics'  => $dynamicModel->getCount(),
            'race_results' => 12700000, // 避免12.7M行全表扫描，用近似值
        ];
        
        // 今日新增
        $todayStats = [
            'articles' => $this->pdo->query("SELECT COUNT(*) FROM articles WHERE DATE(created_at) = CURDATE() AND status=1")->fetchColumn(),
            'pigeons'  => $this->pdo->query("SELECT COUNT(*) FROM pigeons WHERE DATE(created_at) = CURDATE() AND status=1")->fetchColumn(),
            'listings' => $this->pdo->query("SELECT COUNT(*) FROM listings WHERE DATE(created_at) = CURDATE() AND status=1")->fetchColumn(),
            'dynamics' => $this->pdo->query("SELECT COUNT(*) FROM dynamics WHERE DATE(created_at) = CURDATE() AND status=1")->fetchColumn(),
        ];
        
        // 传递数据到视图
        $data = [
            'latestArticles' => $latestArticles,
            'hotPigeons' => $hotPigeons,
            'hotListings' => $hotListings,
            'upcomingRaces' => $upcomingRaces,
            'hotRaces' => $hotRaces,
            'hotLofts' => $hotLofts,
            'hotDynamics' => $hotDynamics,
            'latestRaces' => $latestRaces,
            'categories' => $categories,
            'stats' => $stats,
            'todayStats' => $todayStats,
            'pageTitle' => '信鸽之家 - 鸽友的信息交流平台'
        ];
        
        $this->loadView('index', $data);
    }
}
