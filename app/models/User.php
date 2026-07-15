<?php
/**
 * 用户模型
 */
class User {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * 根据ID获取用户
     */
    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * 根据用户名获取用户
     */
    public function findByUsername($username) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * 根据邮箱获取用户
     */
    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * 创建用户
     */
    public function create($data) {
        // email 为空时用 NULL，避免 UNIQUE 约束冲突
        $email = !empty($data['email']) ? $data['email'] : null;
        $sql = "INSERT INTO users (username, password, email, phone, nickname, avatar, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['username'],
            $data['password'],
            $email,
            $data['phone'] ?? $data['username'],
            $data['nickname'] ?? $data['username'],
            $data['avatar'] ?? '/public/assets/images/default-avatar.png'
        ]);
    }
    
    /**
     * 更新用户
     */
    public function update($id, $data) {
        $fields = [];
        $values = [];
        
        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }
        
        $values[] = $id;
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($values);
    }
    
    /**
     * 验证登录
     */
    public function verifyLogin($username, $password) {
        $user = $this->findByUsername($username);
        if (!$user) {
            return false;
        }
        
        if (password_verify($password, $user['password'])) {
            return $user;
        }
        
        return false;
    }
    
    /**
     * 获取用户发布的内容数量
     */
    public function getContentCount($userId) {
        $articleCount = $this->pdo->prepare("SELECT COUNT(*) FROM articles WHERE user_id = ?");
        $articleCount->execute([$userId]);
        $articles = $articleCount->fetchColumn();
        
        $pigeonCount = $this->pdo->prepare("SELECT COUNT(*) FROM pigeons WHERE user_id = ?");
        $pigeonCount->execute([$userId]);
        $pigeons = $pigeonCount->fetchColumn();
        
        return [
            'articles' => $articles,
            'pigeons' => $pigeons,
            'total' => $articles + $pigeons
        ];
    }
}
