<?php
require_once __DIR__ . '/../models/Dynamic.php';

/**
 * 动态控制器（鸽友圈）
 */
class DynamicController extends Controller {
    
    /**
     * 动态列表
     */
    public function list() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $dynamicModel = new Dynamic($this->pdo);
        
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $options = [
            'limit' => $limit,
            'offset' => $offset
        ];
        
        $dynamics = $dynamicModel->getList($options);
        $total = $dynamicModel->getCount($options);
        $totalPages = ceil($total / $limit);
        
        $data = [
            'dynamics' => $dynamics,
            'page' => $page,
            'totalPages' => $totalPages,
            'pageTitle' => '鸽友圈 | ' . SITE_NAME
        ];
        
        $this->loadView('dynamics', $data);
    }
    
    /**
     * 发布动态
     */
    public function create() {
        if (!isset($_SESSION['user_id'])) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '请先登录']);
            exit;
            return;
        }
        
        $dynamicModel = new Dynamic($this->pdo);
        
        $data = [
            'user_id' => $_SESSION['user_id'],
            'content' => $_POST['content'],
            'images' => $_POST['images'] ?? ''
        ];
        
        $result = $dynamicModel->create($data);
        
        if ($result) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => true, 'message' => '发布成功']);
            exit;
        } else {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '发布失败']);
            exit;
        }
    }
    
    /**
     * 编辑动态
     */
    public function edit() {
        if (!isset($_SESSION['user_id'])) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '请先登录']);
            exit;
            return;
        }

        $id = $_POST['id'] ?? 0;
        $content = $_POST['content'] ?? '';

        if (empty($content)) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '内容不能为空']);
            exit;
            return;
        }

        $dynamicModel = new Dynamic($this->pdo);
        $result = $dynamicModel->update($id, $_SESSION['user_id'], [
            'content' => $content,
            'images' => $_POST['images'] ?? ''
        ]);

        if ($result) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => true, 'message' => '修改成功']);
            exit;
        } else {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '修改失败']);
            exit;
        }
    }

    /**
     * 删除动态
     */
    public function delete() {
        if (!isset($_SESSION['user_id'])) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '请先登录']);
            exit;
            return;
        }
        
        $dynamicModel = new Dynamic($this->pdo);
        
        $id = $_POST['id'] ?? 0;
        
        $result = $dynamicModel->delete($id, $_SESSION['user_id']);
        
        if ($result) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => true, 'message' => '删除成功']);
            exit;
        } else {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '删除失败']);
            exit;
        }
    }
    
    /**
     * 我的动态
     */
    public function my() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $dynamicModel = new Dynamic($this->pdo);
        
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $options = [
            'user_id' => $_SESSION['user_id'],
            'limit' => $limit,
            'offset' => $offset
        ];
        
        $dynamics = $dynamicModel->getList($options);
        $total = $dynamicModel->getCount($options);
        $totalPages = ceil($total / $limit);
        
        $data = [
            'dynamics' => $dynamics,
            'page' => $page,
            'totalPages' => $totalPages,
            'pageTitle' => '我的动态 | ' . SITE_NAME
        ];
        
        $this->loadView('my_dynamics', $data);
    }
}
