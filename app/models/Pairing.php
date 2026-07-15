<?php
require_once __DIR__ . '/../config.php';

class Pairing {
    private $db;
    
    public function __construct() {
        $this->db = get_db_connection();
    }
    
    // 获取用户的配对列表
    public function getByUserId($userId) {
        $stmt = $this->db->prepare("
            SELECT p.*, 
                   s.ring_number as sire_ring, s.name as sire_name,
                   d.ring_number as dam_ring, d.name as dam_name
            FROM pigeon_pairings p
            LEFT JOIN pigeons s ON p.sire_id = s.id
            LEFT JOIN pigeons d ON p.dam_id = d.id
            WHERE p.user_id = ?
            ORDER BY p.pairing_date DESC, p.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // 根据ID获取配对
    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT p.*, 
                   s.ring_number as sire_ring, s.name as sire_name,
                   d.ring_number as dam_ring, d.name as dam_name
            FROM pigeon_pairings p
            LEFT JOIN pigeons s ON p.sire_id = s.id
            LEFT JOIN pigeons d ON p.dam_id = d.id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // 创建配对
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO pigeon_pairings (user_id, sire_id, dam_id, pairing_date, notes)
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['user_id'],
            $data['sire_id'],
            $data['dam_id'],
            $data['pairing_date'],
            $data['notes']
        ]);
    }
    
    // 更新配对
    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE pigeon_pairings 
            SET sire_id = ?, dam_id = ?, pairing_date = ?, notes = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['sire_id'],
            $data['dam_id'],
            $data['pairing_date'],
            $data['notes'],
            $id
        ]);
    }
    
    // 删除配对
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM pigeon_pairings WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    // 获取配对的后代
    public function getOffspring($pairingId) {
        $stmt = $this->db->prepare("
            SELECT p.* 
            FROM pigeons p
            JOIN pairing_offspring po ON p.id = po.pigeon_id
            WHERE po.pairing_id = ?
        ");
        $stmt->execute([$pairingId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
