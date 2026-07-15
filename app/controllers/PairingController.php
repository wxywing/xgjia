<?php
require_once __DIR__ . '/../models/Pairing.php';

class PairingController {
    private $pairingModel;
    private $pigeonModel;
    
    public function __construct() {
        $this->pairingModel = new Pairing();
        $this->pigeonModel = new Pigeon();
    }
    
    // 列表
    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /user.php?action=login');
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        $pairings = $this->pairingModel->getByUserId($userId);
        
        require_once __DIR__ . '/../views/user/pairings.php';
    }
    
    // 创建配对
    public function create() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /user.php?action=login');
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $sireId = intval($_POST['sire_id']);
            $damId = intval($_POST['dam_id']);
            $pairingDate = $_POST['pairing_date'];
            $notes = trim($_POST['notes']);
            
            // 验证鸽子属于当前用户
            $sire = $this->pigeonModel->getById($sireId);
            $dam = $this->pigeonModel->getById($damId);
            
            if (!$sire || $sire['user_id'] != $userId || 
                !$dam || $dam['user_id'] != $userId) {
                $_SESSION['error'] = '只能配对您自己的铭鸽';
                header('Location: /user/pairings');
                exit;
            }
            
            $data = [
                'user_id' => $userId,
                'sire_id' => $sireId,
                'dam_id' => $damId,
                'pairing_date' => $pairingDate ?: null,
                'notes' => $notes
            ];
            
            $this->pairingModel->create($data);
            $_SESSION['success'] = '配对创建成功';
            header('Location: /user/pairings');
            exit;
        }
        
        // GET 请求，显示创建表单
        $myPigeons = $this->pigeonModel->getByUserId($userId);
        require_once __DIR__ . '/../views/user/pairings_create.php';
    }
    
    // 编辑配对
    public function edit($id) {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /user.php?action=login');
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        $pairing = $this->pairingModel->getById($id);
        
        if (!$pairing || $pairing['user_id'] != $userId) {
            $_SESSION['error'] = '配对不存在或无权编辑';
            header('Location: /user/pairings');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $sireId = intval($_POST['sire_id']);
            $damId = intval($_POST['dam_id']);
            $pairingDate = $_POST['pairing_date'];
            $notes = trim($_POST['notes']);
            
            $data = [
                'sire_id' => $sireId,
                'dam_id' => $damId,
                'pairing_date' => $pairingDate ?: null,
                'notes' => $notes
            ];
            
            $this->pairingModel->update($id, $data);
            $_SESSION['success'] = '配对更新成功';
            header('Location: /user/pairings');
            exit;
        }
        
        $myPigeons = $this->pigeonModel->getByUserId($userId);
        require_once __DIR__ . '/../views/user/pairings_edit.php';
    }
    
    // 删除配对
    public function delete($id) {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /user.php?action=login');
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        $pairing = $this->pairingModel->getById($id);
        
        if (!$pairing || $pairing['user_id'] != $userId) {
            $_SESSION['error'] = '配对不存在或无权删除';
            header('Location: /user/pairings');
            exit;
        }
        
        $this->pairingModel->delete($id);
        $_SESSION['success'] = '配对删除成功';
        header('Location: /user/pairings');
        exit;
    }
}
