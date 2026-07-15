<?php
/**
 * TagController - 标签页面控制器
 */

require_once dirname(__DIR__) . '/models/Tag.php';
require_once dirname(__DIR__) . '/core/Controller.php';

class TagController extends Controller
{
    private $tagModel;

    public function __construct($pdo)
    {
        parent::__construct($pdo);
        $this->tagModel = new Tag($this->pdo);
    }

    /**
     * 标签列表页 /tags/
     */
    public function index()
    {
        $type = $_GET['type'] ?? null;
        $tags = $this->tagModel->getAll($type);
        
        // 按类型分组
        $grouped = [];
        foreach ($tags as $tag) {
            $grouped[$tag['type']][] = $tag;
        }

        $page_title = '文章标签 - 信鸽之家';
        $meta_description = '信鸽之家文章标签分类，包括赛前调整、幼鸽管理、公棚赛、血统等热门话题标签。';

        require dirname(__DIR__, 2) . '/views/tag_list.php';
    }

    /**
     * 标签详情页 /tag/{slug}/
     */
    public function detail($slug)
    {
        $tag = $this->tagModel->getBySlug($slug);
        if (!$tag) {
            http_response_code(404);
            require dirname(__DIR__, 2) . '/views/404.php';
            return;
        }

        $page = max(1, intval($_GET['page'] ?? 1));
        $perPage = 12;
        
        $articles = $this->tagModel->getTagArticles($tag['id'], $page, $perPage);
        $total = $this->tagModel->getTagArticleCount($tag['id']);
        $totalPages = ceil($total / $perPage);

        $page_title = $tag['name'] . '相关文章 - 信鸽之家';
        $meta_description = "信鸽之家关于「{$tag['name']}」的文章合集，共{$total}篇相关内容。";

        require dirname(__DIR__, 2) . '/views/tag_detail.php';
    }

    /**
     * 获取热门标签（供其他页面调用）
     */
    public static function getHotTags($limit = 10)
    {
        $pdo = get_pdo();
        $tagModel = new Tag($pdo);
        return $tagModel->getHot($limit);
    }
}
