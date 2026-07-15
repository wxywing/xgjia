<?php
/**
 * 文章模型
 */
class Article {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * 根据ID获取文章
     */
    public function findById($id) {
        $sql = "SELECT a.*, u.username, u.nickname, u.avatar, c.name as category_name
                FROM articles a
                LEFT JOIN users u ON a.user_id = u.id
                LEFT JOIN categories c ON a.category_id = c.id
                WHERE a.id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * 获取文章列表
     */
    public function getList($options = []) {
        $where = "WHERE a.status = 1";
        $params = [];
        
        if (!empty($options['category_id'])) {
            $where .= " AND a.category_id = ?";
            $params[] = $options['category_id'];
        }
        
        if (!empty($options['user_id'])) {
            $where .= " AND a.user_id = ?";
            $params[] = $options['user_id'];
        }
        
        if (!empty($options['keyword'])) {
            $where .= " AND (a.title LIKE ? OR a.content LIKE ?)";
            $keyword = "%{$options['keyword']}%";
            $params[] = $keyword;
            $params[] = $keyword;
        }
        
        $orderBy = "ORDER BY a.is_top DESC, a.created_at DESC";
        $limit = "";
        
        if (!empty($options['limit'])) {
            $limit = "LIMIT " . (int)$options['limit'];
        }
        
        if (!empty($options['offset'])) {
            $limit .= " OFFSET " . (int)$options['offset'];
        }
        
        $sql = "SELECT a.*, u.username, u.nickname, u.avatar, c.name as category_name
                FROM articles a
                LEFT JOIN users u ON a.user_id = u.id
                LEFT JOIN categories c ON a.category_id = c.id
                $where
                $orderBy
                $limit";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * 获取文章总数
     */
    public function getCount($options = []) {
        $where = "WHERE status = 1";
        $params = [];
        
        if (!empty($options['category_id'])) {
            $where .= " AND category_id = ?";
            $params[] = $options['category_id'];
        }
        
        if (!empty($options['user_id'])) {
            $where .= " AND user_id = ?";
            $params[] = $options['user_id'];
        }
        
        if (!empty($options['keyword'])) {
            $where .= " AND (title LIKE ? OR content LIKE ?)";
            $keyword = "%{$options['keyword']}%";
            $params[] = $keyword;
            $params[] = $keyword;
        }
        
        $sql = "SELECT COUNT(*) FROM articles $where";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
    
    /**
     * 创建文章
     */
    public function create($data) {
        $sql = "INSERT INTO articles (user_id, category_id, title, summary, content, cover, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['user_id'],
            $data['category_id'],
            $data['title'],
            $data['summary'] ?? '',
            $data['content'],
            $data['cover'] ?? '',
            $data['status'] ?? 1
        ]);
    }
    
    /**
     * 更新文章
     */
    public function update($id, $data) {
        $fields = [];
        $values = [];
        
        $allowedFields = ['category_id', 'title', 'summary', 'content', 'cover', 'status'];
        
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $fields[] = "$key = ?";
                $values[] = $value;
            }
        }
        
        $values[] = $id;
        $sql = "UPDATE articles SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($values);
    }
    
    /**
     * 删除文章
     */
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM articles WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * 增加浏览次数
     */
    public function incrementViews($id) {
        $stmt = $this->pdo->prepare("UPDATE articles SET views = views + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * 获取热门文章
     */
    public function getHot($limit = 10) {
        $sql = "SELECT * FROM articles WHERE status = 1 ORDER BY views DESC, created_at DESC LIMIT ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([(int)$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * 获取推荐文章
     */
    public function getRecommend($limit = 10) {
        $sql = "SELECT * FROM articles WHERE status = 1 AND is_recommend = 1 ORDER BY created_at DESC LIMIT ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([(int)$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 根据文章标题+内容，自动同步标签关联
     * 发布/编辑文章后调用一次
     * @param int    $articleId
     * @param string $title
     * @param string $content  HTML 内容
     */
    public function syncArticleTags($articleId, $title, $content)
    {
        require_once __DIR__ . '/../core/InternalLinker.php';
        $slugs = InternalLinker::detectTagSlugs($title . ' ' . mb_substr(strip_tags($content ?? ''), 0, 800));

        // 删除旧关联
        $this->pdo->prepare("DELETE FROM article_tags WHERE article_id = ?")->execute([$articleId]);

        if (empty($slugs)) return;

        // 查出匹配的 tag id
        $placeholders = implode(',', array_fill(0, count($slugs), '?'));
        $stmt = $this->pdo->prepare("SELECT id, slug FROM tags WHERE slug IN ($placeholders)");
        $stmt->execute($slugs);
        $tagRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 插入新关联
        $ins = $this->pdo->prepare("INSERT IGNORE INTO article_tags (article_id, tag_id) VALUES (?, ?)");
        foreach ($tagRows as $row) {
            $ins->execute([$articleId, $row['id']]);
        }

        // 刷新 article_count
        $this->pdo->exec("UPDATE tags t SET article_count = (
            SELECT COUNT(*) FROM article_tags at WHERE at.tag_id = t.id
        )");
    }
}
