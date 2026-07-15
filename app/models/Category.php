<?php
/**
 * 分类模型
 * 
 * 支持slug别名映射，让URL更友好
 */
class Category {
    private $pdo;
    
    // slug别名映射表（拼音/简称 -> 标准slug）
    private static $slugAliases = [
        'yangge' => 'knowledge',      // 养鸽知识
        'yanggezhishi' => 'knowledge',
        'zhishi' => 'knowledge',
        'saishi' => 'race',          // 赛事资讯（预留）
        'zixun' => 'news',           // 新闻资讯（预留）
        'dongtai' => 'dynamics',     // 动态（预留）
    ];
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * 根据ID获取分类
     */
    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * 根据slug获取分类（支持别名）
     */
    public function findBySlug($slug, $type = null) {
        // 检查是否有别名映射
        $standardSlug = self::$slugAliases[$slug] ?? $slug;
        
        $sql = "SELECT * FROM categories WHERE slug = ? AND status = 1";
        $params = [$standardSlug];
        if ($type !== null) {
            $sql .= " AND type = ?";
            $params[] = $type;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * 获取分类列表
     */
    public function getList($options = []) {
        $where = "WHERE status = 1";
        $params = [];
        
        if (!empty($options['type'])) {
            $where .= " AND type = ?";
            $params[] = $options['type'];
        }
        
        if (!empty($options['parent_id'])) {
            $where .= " AND parent_id = ?";
            $params[] = $options['parent_id'];
        }
        
        $orderBy = "ORDER BY sort ASC, id ASC";
        $limit = "";
        
        if (!empty($options['limit'])) {
            $limit = "LIMIT " . (int)$options['limit'];
        }
        
        $sql = "SELECT * FROM categories $where $orderBy $limit";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * 获取分类列表（含文章数统计）
     */
    public function getListWithCount($options = []) {
        $where = "WHERE c.status = 1";
        $params = [];
        
        if (!empty($options['type'])) {
            $where .= " AND c.type = ?";
            $params[] = $options['type'];
        }
        
        $orderBy = "ORDER BY c.sort ASC, c.id ASC";
        
        $sql = "SELECT c.*, COUNT(a.id) as article_count 
                FROM categories c 
                LEFT JOIN articles a ON c.id = a.category_id AND a.status = 1
                $where 
                GROUP BY c.id 
                $orderBy";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * 获取分类树
     */
    public function getTree($type = null) {
        $where = "WHERE status = 1";
        $params = [];
        
        if ($type) {
            $where .= " AND type = ?";
            $params[] = $type;
        }
        
        $sql = "SELECT * FROM categories $where ORDER BY sort ASC, id ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $this->buildTree($categories);
    }
    
    /**
     * 构建分类树
     */
    private function buildTree($categories, $parentId = 0) {
        $tree = [];
        
        foreach ($categories as $category) {
            if ($category['parent_id'] == $parentId) {
                $children = $this->buildTree($categories, $category['id']);
                if ($children) {
                    $category['children'] = $children;
                }
                $tree[] = $category;
            }
        }
        
        return $tree;
    }
    
    /**
     * 创建分类
     */
    public function create($data) {
        $sql = "INSERT INTO categories (parent_id, name, type, sort, status, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['parent_id'] ?? 0,
            $data['name'],
            $data['type'] ?? 1,
            $data['sort'] ?? 0,
            $data['status'] ?? 1
        ]);
    }
    
    /**
     * 更新分类
     */
    public function update($id, $data) {
        $fields = [];
        $values = [];
        
        $allowedFields = ['parent_id', 'name', 'type', 'sort', 'status'];
        
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $fields[] = "$key = ?";
                $values[] = $value;
            }
        }
        
        $values[] = $id;
        $sql = "UPDATE categories SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($values);
    }
    
    /**
     * 删除分类
     */
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM categories WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
