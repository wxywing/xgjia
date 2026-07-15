<?php
/**
 * 铭鸽血统/配对图谱 Model
 */
class Pedigree {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // ========== 品系 (Strains) ==========

    public function getStrains($limit = 50, $offset = 0) {
        $stmt = $this->pdo->prepare("SELECT * FROM pigeon_strains ORDER BY pigeon_count DESC, id ASC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStrainById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM pigeon_strains WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getStrainBySlug($slug) {
        $stmt = $this->pdo->prepare("SELECT * FROM pigeon_strains WHERE slug = ?");
        $stmt->execute([$slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getStrainByName($name) {
        $stmt = $this->pdo->prepare("SELECT * FROM pigeon_strains WHERE name = ?");
        $stmt->execute([$name]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * 模糊匹配品系（用于URL兼容）
     * 当精确匹配失败时，尝试 LIKE 匹配
     */
    public function getStrainByFuzzyName($keyword) {
        $stmt = $this->pdo->prepare("SELECT * FROM pigeon_strains WHERE name LIKE ? ORDER BY pigeon_count DESC LIMIT 1");
        $stmt->execute(["%{$keyword}%"]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createStrain($name, $slug = '', $description = '') {
        $slug = $slug ?: $this->makeSlug($name);
        $stmt = $this->pdo->prepare("INSERT INTO pigeon_strains (name, slug, description) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)");
        $stmt->execute([$name, $slug, $description]);
        return (int)$this->pdo->lastInsertId();
    }

    public function updateStrainCount($strainId) {
        $stmt = $this->pdo->prepare("UPDATE pigeon_strains SET pigeon_count = (SELECT COUNT(*) FROM pigeons WHERE strain_id = ? AND status = 1) WHERE id = ?");
        $stmt->execute([$strainId, $strainId]);
    }

    public function searchStrains($keyword) {
        $stmt = $this->pdo->prepare("SELECT * FROM pigeon_strains WHERE name LIKE ? ORDER BY pigeon_count DESC LIMIT 20");
        $stmt->execute(["%{$keyword}%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ========== 父母关系 (Parents) ==========

    public function getParents($pigeonId) {
        $stmt = $this->pdo->prepare("
            SELECT pp.*, 
                   f.name as father_name, f.ring_number as father_ring, f.images as father_images,
                   m.name as mother_name, m.ring_number as mother_ring, m.images as mother_images
            FROM pigeon_parents pp
            LEFT JOIN pigeons f ON pp.father_id = f.id
            LEFT JOIN pigeons m ON pp.mother_id = m.id
            WHERE pp.pigeon_id = ?
        ");
        $stmt->execute([$pigeonId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function setParents($pigeonId, $fatherId = null, $motherId = null) {
        $stmt = $this->pdo->prepare("
            INSERT INTO pigeon_parents (pigeon_id, father_id, mother_id) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE father_id = VALUES(father_id), mother_id = VALUES(mother_id)
        ");
        return $stmt->execute([$pigeonId, $fatherId, $motherId]);
    }

    public function getChildren($pigeonId) {
        $stmt = $this->pdo->prepare("
            SELECT p.id, p.name, p.ring_number, p.bloodline, p.images, p.gender
            FROM pigeon_parents pp
            JOIN pigeons p ON pp.pigeon_id = p.id
            WHERE pp.father_id = ? OR pp.mother_id = ?
            AND p.status = 1
            ORDER BY p.id DESC
        ");
        $stmt->execute([$pigeonId, $pigeonId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPedigreeTree($pigeonId, $depth = 3) {
        return $this->buildTree($pigeonId, $depth, 0);
    }

    private function buildTree($pigeonId, $maxDepth, $currentDepth) {
        if ($currentDepth >= $maxDepth || !$pigeonId) return null;
        $stmt = $this->pdo->prepare("SELECT id, name, ring_number, bloodline, gender, images FROM pigeons WHERE id = ? AND status = 1");
        $stmt->execute([$pigeonId]);
        $pigeon = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$pigeon) return null;
        $parents = $this->getParents($pigeonId);
        return [
            'id' => $pigeon['id'],
            'name' => $pigeon['name'],
            'ring_number' => $pigeon['ring_number'],
            'bloodline' => $pigeon['bloodline'],
            'gender' => $pigeon['gender'],
            'images' => json_decode($pigeon['images'] ?? '[]', true) ?: [],
            'depth' => $currentDepth,
            'father' => $parents && $parents['father_id'] ? $this->buildTree($parents['father_id'], $maxDepth, $currentDepth + 1) : null,
            'mother' => $parents && $parents['mother_id'] ? $this->buildTree($parents['mother_id'], $maxDepth, $currentDepth + 1) : null,
        ];
    }

    // ========== 配对记录 (Pairings) ==========

    public function getPairings($userId, $limit = 20, $offset = 0, $pigeonId = null) {
        // 如果提供了 pigeon_id，筛选出该鸽子作为父本或母本的配对
        if ($pigeonId) {
            $stmt = $this->pdo->prepare("
                SELECT p.*, 
                       m.name as male_name, m.ring_number as male_ring, m.images as male_images,
                       f.name as female_name, f.ring_number as female_ring, f.images as female_images
                FROM pigeon_pairings p
                LEFT JOIN pigeons m ON p.male_id = m.id
                LEFT JOIN pigeons f ON p.female_id = f.id
                WHERE p.user_id = :user_id
                  AND (p.male_id = :pigeon_id OR p.female_id = :pigeon_id2)
                ORDER BY p.created_at DESC
                LIMIT :limit OFFSET :offset
            ");
            $stmt->bindValue(':user_id', (int)$userId, PDO::PARAM_INT);
            $stmt->bindValue(':pigeon_id', (int)$pigeonId, PDO::PARAM_INT);
            $stmt->bindValue(':pigeon_id2', (int)$pigeonId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        } else {
            $stmt = $this->pdo->prepare("
                SELECT p.*, 
                       m.name as male_name, m.ring_number as male_ring, m.images as male_images,
                       f.name as female_name, f.ring_number as female_ring, f.images as female_images
                FROM pigeon_pairings p
                LEFT JOIN pigeons m ON p.male_id = m.id
                LEFT JOIN pigeons f ON p.female_id = f.id
                WHERE p.user_id = :user_id
                ORDER BY p.created_at DESC
                LIMIT :limit OFFSET :offset
            ");
            $stmt->bindValue(':user_id', (int)$userId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPairingCount($userId, $pigeonId = null) {
        // 如果提供了 pigeon_id，筛选出该鸽子作为父本或母本的配对
        if ($pigeonId) {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) 
                FROM pigeon_pairings 
                WHERE user_id = ? 
                  AND (male_id = ? OR female_id = ?)
            ");
            $stmt->execute([$userId, $pigeonId, $pigeonId]);
        } else {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM pigeon_pairings WHERE user_id = ?");
            $stmt->execute([$userId]);
        }
        return (int)$stmt->fetchColumn();
    }

    public function createPairing($userId, $maleId, $femaleId, $notes = '') {
        $stmt = $this->pdo->prepare("INSERT INTO pigeon_pairings (user_id, male_id, female_id, notes) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $maleId, $femaleId, $notes]);
        return (int)$this->pdo->lastInsertId();
    }

    public function updatePairing($id, $userId, $data) {
        $allowed = ['notes', 'status', 'children_ids'];
        $sets = []; $params = [];
        foreach ($data as $key => $val) {
            if (in_array($key, $allowed)) { $sets[] = "$key = ?"; $params[] = $val; }
        }
        if (empty($sets)) return false;
        $params[] = $id; $params[] = $userId;
        $stmt = $this->pdo->prepare("UPDATE pigeon_pairings SET " . implode(', ', $sets) . " WHERE id = ? AND user_id = ?");
        return $stmt->execute($params);
    }

    public function deletePairing($id, $userId) {
        $stmt = $this->pdo->prepare("DELETE FROM pigeon_pairings WHERE id = ? AND user_id = ?");
        return $stmt->execute([$id, $userId]);
    }

    public function getStrainPigeonCount($strainId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM pigeons WHERE strain_id = :strain_id AND status = 1");
        $stmt->bindValue(':strain_id', (int)$strainId, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    // ========== 品系统计 ==========

    public function getStrainColorDistribution($strainId) {
        $stmt = $this->pdo->prepare("
            SELECT color, COUNT(*) AS cnt
            FROM pigeons
            WHERE strain_id = :strain_id AND status = 1 AND color IS NOT NULL AND color != ''
            GROUP BY color ORDER BY cnt DESC
        ");
        $stmt->bindValue(':strain_id', (int)$strainId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStrainGenderStats($strainId) {
        $stmt = $this->pdo->prepare("
            SELECT gender, COUNT(*) AS cnt
            FROM pigeons
            WHERE strain_id = :strain_id AND status = 1
            GROUP BY gender
        ");
        $stmt->bindValue(':strain_id', (int)$strainId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStrainEyeColorDistribution($strainId) {
        $stmt = $this->pdo->prepare("
            SELECT eye_color, COUNT(*) AS cnt
            FROM pigeons
            WHERE strain_id = :strain_id AND status = 1 AND eye_color IS NOT NULL AND eye_color != ''
            GROUP BY eye_color ORDER BY cnt DESC
        ");
        $stmt->bindValue(':strain_id', (int)$strainId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPigeonsByStrain($strainId, $limit = 12, $offset = 0) {
        $stmt = $this->pdo->prepare("
            SELECT p.*, s.name as strain_name
            FROM pigeons p LEFT JOIN pigeon_strains s ON p.strain_id = s.id
            WHERE p.strain_id = :strain_id AND p.status = 1
            ORDER BY p.views DESC, p.id DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':strain_id', (int)$strainId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function autoMatchStrains() {
        $strains = $this->pdo->query("SELECT id, name FROM pigeon_strains")->fetchAll(PDO::FETCH_ASSOC);
        $strainMap = [];
        foreach ($strains as $s) $strainMap[$s['name']] = $s['id'];
        $pigeons = $this->pdo->query("SELECT id, bloodline FROM pigeons WHERE strain_id IS NULL AND bloodline IS NOT NULL AND bloodline != '' AND status = 1")->fetchAll(PDO::FETCH_ASSOC);
        $matched = 0;
        foreach ($pigeons as $p) {
            $bl = trim($p['bloodline']);
            if (isset($strainMap[$bl])) {
                $this->pdo->prepare("UPDATE pigeons SET strain_id = ? WHERE id = ?")->execute([$strainMap[$bl], $p['id']]);
                $matched++;
            }
        }
        return $matched;
    }

    private function makeSlug($text) {
        $slug = preg_replace('/[^\x{4e00}-\x{9fff}a-zA-Z0-9]/u', '-', $text);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }
}