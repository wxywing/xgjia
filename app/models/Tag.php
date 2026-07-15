<?php
/**
 * Tag 模型 - 文章标签管理
 */

require_once dirname(__DIR__) . '/core/Model.php';

class Tag extends Model
{
    /**
     * 获取所有标签
     */
    public function getAll($type = null)
    {
        $sql = "SELECT t.*, COUNT(at.article_id) as article_count
                FROM tags t
                LEFT JOIN article_tags at ON t.id = at.tag_id";
        $params = [];
        if ($type) {
            $sql .= " WHERE t.type = ?";
            $params[] = $type;
        }
        $sql .= " GROUP BY t.id ORDER BY article_count DESC, t.name ASC";
        return $this->fetchAll($sql, $params);
    }

    /**
     * 根据slug获取标签
     */
    public function getBySlug($slug)
    {
        return $this->fetch("SELECT * FROM tags WHERE slug = ?", [$slug]);
    }

    /**
     * 获取文章的标签
     */
    public function getArticleTags($articleId)
    {
        $sql = "SELECT t.* FROM tags t
                JOIN article_tags at ON t.id = at.tag_id
                WHERE at.article_id = ?
                ORDER BY t.name ASC";
        return $this->fetchAll($sql, [$articleId]);
    }

    /**
     * 获取标签下的文章
     */
    public function getTagArticles($tagId, $page = 1, $perPage = 12)
    {
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT a.* FROM articles a
                JOIN article_tags at ON a.id = at.article_id
                WHERE at.tag_id = ? AND a.status = 1
                ORDER BY a.published_at DESC
                LIMIT ? OFFSET ?";
        return $this->fetchAll($sql, [$tagId, $perPage, $offset]);
    }

    /**
     * 获取标签下文章总数
     */
    public function getTagArticleCount($tagId)
    {
        $result = $this->fetch(
            "SELECT COUNT(*) as cnt FROM article_tags WHERE tag_id = ?",
            [$tagId]
        );
        return $result ? (int)$result['cnt'] : 0;
    }

    /**
     * 为文章添加标签
     */
    public function addTagsToArticle($articleId, $tagIds)
    {
        if (empty($tagIds)) return;
        
        $this->query("DELETE FROM article_tags WHERE article_id = ?", [$articleId]);
        
        foreach ($tagIds as $tagId) {
            $this->query(
                "INSERT IGNORE INTO article_tags (article_id, tag_id) VALUES (?, ?)",
                [(int)$articleId, (int)$tagId]
            );
        }
        
        $this->updateArticleCounts($tagIds);
    }

    /**
     * 更新标签文章计数
     */
    public function updateArticleCounts($tagIds = null)
    {
        $where = $tagIds ? "WHERE id IN (" . implode(',', array_map('intval', $tagIds)) . ")" : "";
        $sql = "UPDATE tags t SET article_count = (
                    SELECT COUNT(*) FROM article_tags at WHERE at.tag_id = t.id
                ) $where";
        $this->query($sql);
    }

    /**
     * 创建或获取标签
     */
    public function createOrGet($name, $type = 'topic')
    {
        $tag = $this->fetch("SELECT * FROM tags WHERE name = ?", [$name]);
        if ($tag) return $tag;

        $slug = $this->generateSlug($name);
        $this->query(
            "INSERT INTO tags (name, slug, type) VALUES (?, ?, ?)",
            [$name, $slug, $type]
        );
        return $this->fetch("SELECT * FROM tags WHERE id = LAST_INSERT_ID()");
    }

    /**
     * 生成URL友好的slug
     */
    private function generateSlug($name)
    {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9\u4e00-\u9fa5]+/', '-', $slug);
        $slug = trim($slug, '-');
        
        $count = 0;
        $original = $slug;
        while ($this->fetch("SELECT id FROM tags WHERE slug = ?", [$slug])) {
            $count++;
            $slug = $original . '-' . $count;
        }
        return $slug;
    }

    /**
     * 获取热门标签
     */
    public function getHot($limit = 10)
    {
        return $this->fetchAll(
            "SELECT t.*, COUNT(at.article_id) as article_count
             FROM tags t
             LEFT JOIN article_tags at ON t.id = at.tag_id
             GROUP BY t.id
             HAVING article_count > 0
             ORDER BY article_count DESC
             LIMIT ?",
            [$limit]
        );
    }
}
