<?php
/**
 * 血统/配对图谱 Controller
 */
require_once __DIR__ . '/../models/Pedigree.php';
require_once __DIR__ . '/../core/MembershipGuard.php';

class PedigreeController extends Controller {
    private $model;

    public function __construct($pdo) {
        parent::__construct($pdo);
        $this->model = new Pedigree($pdo);
    }

    // ========== 品系列表 ==========
    public function strains() {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 50;
        $offset = ($page - 1) * $limit;
        $strains = $this->model->getStrains($limit, $offset);
        $this->loadView('strains', [
            'pageTitle'    => '血统品系 | ' . SITE_NAME,
            'strains'      => $strains,
            'page'         => $page,
            'limit'        => $limit,
        ]);
    }

    // ========== 品系详情（该品系下的所有铭鸽）==========
    public function strainDetail($slugOrId) {
        // URL解码：nginx rewrite传递的REQUEST_URI中中文是编码后的
        $slugOrId = urldecode($slugOrId);

        $strain = is_numeric($slugOrId)
            ? $this->model->getStrainById((int)$slugOrId)
            : $this->model->getStrainBySlug($slugOrId);

        // 按slug查不到时，尝试按名称查询（兼容不同写法的品系名）
        if (!$strain && !is_numeric($slugOrId)) {
            $strain = $this->model->getStrainByName($slugOrId);
        }

        // 精确匹配失败，尝试模糊匹配（如URL中的品系名包含额外后缀）
        if (!$strain && !is_numeric($slugOrId)) {
            $strain = $this->model->getStrainByFuzzyName($slugOrId);
        }

        if (!$strain) {
            http_response_code(404);
            echo '品系不存在';
            return;
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $pigeons = $this->model->getPigeonsByStrain($strain['id'], 12, ($page - 1) * 12);
        $total = $this->model->getStrainPigeonCount($strain['id']);
        $colorDist = $this->model->getStrainColorDistribution($strain['id']);
        $genderStats = $this->model->getStrainGenderStats($strain['id']);
        $eyeDist = $this->model->getStrainEyeColorDistribution($strain['id']);

        // 性别映射
        $genderMap = [0 => '未知', 1 => '雄', 2 => '雌'];
        $totalPigeons = $total ?: 1;

        $this->loadView('strain_detail', [
            'pageTitle'    => $strain['name'] . ' - 血统品系 | ' . SITE_NAME,
            'strain'       => $strain,
            'pigeons'      => $pigeons,
            'page'         => $page,
            'total'        => $total,
            'totalPages'   => max(1, ceil($total / 12)),
            'colorDist'    => $colorDist,
            'genderStats'  => $genderStats,
            'eyeDist'      => $eyeDist,
            'genderMap'    => $genderMap,
            'totalPigeons' => $totalPigeons,
        ]);
    }

    // ========== 品系赛事成绩独立页（P2）==========
    public function strainRaceResults($slugOrId) {
        $slugOrId = urldecode($slugOrId);

        $strain = is_numeric($slugOrId)
            ? $this->model->getStrainById((int)$slugOrId)
            : $this->model->getStrainBySlug($slugOrId);

        if (!$strain && !is_numeric($slugOrId)) {
            $strain = $this->model->getStrainByName($slugOrId);
        }
        if (!$strain && !is_numeric($slugOrId)) {
            $strain = $this->model->getStrainByFuzzyName($slugOrId);
        }

        if (!$strain) {
            http_response_code(404);
            echo '品系不存在';
            return;
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        require_once __DIR__ . '/../models/Race.php';
        $raceModel = new Race($this->pdo);
        $total = $raceModel->getStrainRaceResultsCount($strain['id']);
        $results = $total > 0 ? $raceModel->getStrainRaceResults($strain['id'], $limit, $offset) : [];
        $raceStats = $raceModel->getStrainRaceStats($strain['id']);

        $this->loadView('strain_race_results', [
            'pageTitle' => $strain['name'] . ' - 赛事成绩 | ' . SITE_NAME,
            'strain'    => $strain,
            'results'   => $results,
            'raceStats' => $raceStats,
            'page'      => $page,
            'total'     => $total,
            'totalPages'=> max(1, ceil($total / $limit)),
            'limit'     => $limit,
        ]);
    }

    // ========== 血统树（JSON API）==========
    public function pedigreeTree($pigeonId) {
        header('Content-Type: application/json; charset=utf-8');
        $tree = $this->model->getPedigreeTree((int)$pigeonId, 3);
        header("Content-Type: application/json; charset=utf-8");
        echo json_encode($tree ?: ['error' => '铭鸽不存在或无血统数据'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ========== 设置父母（AJAX）==========
    public function setParents() {
        header('Content-Type: application/json; charset=utf-8');

        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '请先登录']);
            exit;
            return;
        }

        $pigeonId = (int)($_POST['pigeon_id'] ?? 0);
        $fatherId = $_POST['father_id'] !== '' ? (int)$_POST['father_id'] : null;
        $motherId = $_POST['mother_id'] !== '' ? (int)$_POST['mother_id'] : null;

        if (!$pigeonId) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '参数错误']);
            exit;
            return;
        }

        $ok = $this->model->setParents($pigeonId, $fatherId, $motherId);
        header("Content-Type: application/json; charset=utf-8");
        echo json_encode(['success' => $ok, 'message' => $ok ? '保存成功' : '保存失败']);
        exit;
    }

    // ========== 配对记录列表 ==========
    public function myPairings() {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            header('Location: /auth?action=login');
            return;
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $pigeonId = isset($_GET['pigeon_id']) ? (int)$_GET['pigeon_id'] : null;
        
        $pairings = $this->model->getPairings($userId, 20, ($page - 1) * 20, $pigeonId);
        $total = $this->model->getPairingCount($userId, $pigeonId);

        $this->loadView('my_pairings', [
            'pageTitle'   => '我的配对 | ' . SITE_NAME,
            'pairings'    => $pairings,
            'page'        => $page,
            'totalPages'  => max(1, ceil($total / 20)),
            'total'       => $total,
            'pigeon_id'   => $pigeonId,  // 传递 pigeon_id 到视图，用于高亮显示
        ]);
    }

    // ========== 创建配对 ==========
    public function createPairing() {
        header('Content-Type: application/json; charset=utf-8');

        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '请先登录']);
            exit;
            return;
        }

        $maleId = (int)($_POST['male_id'] ?? 0);
        $femaleId = (int)($_POST['female_id'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');

        if (!$maleId || !$femaleId) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '请选择配对铭鸽']);
            exit;
            return;
        }
        if ($maleId === $femaleId) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '不能选择同一只铭鸽']);
            exit;
            return;
        }

        $id = $this->model->createPairing($userId, $maleId, $femaleId, $notes);
        header("Content-Type: application/json; charset=utf-8");
        echo json_encode(['success' => true, 'id' => $id, 'message' => '配对创建成功']);
        exit;
    }

    // ========== 删除配对 ==========
    public function deletePairing() {
        header('Content-Type: application/json; charset=utf-8');

        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '请先登录']);
            exit;
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        $ok = $this->model->deletePairing($id, $userId);
        header("Content-Type: application/json; charset=utf-8");
        echo json_encode(['success' => $ok, 'message' => $ok ? '已删除' : '删除失败']);
        exit;
    }

    // ========== 搜索铭鸽（配对选择用）==========
    public function searchPigeons() {
        header('Content-Type: application/json; charset=utf-8');

        $keyword = trim($_GET['q'] ?? '');
        $gender = $_GET['gender'] ?? '';

        $pigeonModel = new Pigeon($this->pdo);
        $params = ['keyword' => $keyword, 'limit' => 20];
        if ($gender !== '') $params['gender'] = (int)$gender;

        $pigeons = $pigeonModel->getList($params);
        header("Content-Type: application/json; charset=utf-8");
        echo json_encode(['success' => true, 'data' => $pigeons], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ========== 血统证书 ==========

    /**
     * 证书专用：按足环号查找鸽子 + 尝试查找父母
     * GET /pedigree/?action=certificate_search_pigeon&ring=XXX
     */
    public function findPigeonForCertificate()
    {
        header('Content-Type: application/json; charset=utf-8');

        $ring = trim($_GET['ring'] ?? '');
        if ($ring === '') {
            echo json_encode(['success' => false, 'message' => '请输入足环号']);
            exit;
        }

        $pigeonModel = new Pigeon($this->pdo);
        $pigeon = $pigeonModel->findByRingNumber($ring);

        if (!$pigeon) {
            // 铭鸽展厅没有 → 查赛事数据库
            $raceModel = new Race($this->pdo);
            $raceResults = $raceModel->getResultsByRing($ring);

            if (empty($raceResults)) {
                echo json_encode(['success' => false, 'message' => '未找到该足环号（铭鸽展厅和赛事数据库均无记录）']);
                exit;
            }

                // 从赛事数据库构建信息
                $first = $raceResults[0];
                $result = [
                    'ring_number' => $first['ring_number'] ?? $ring,
                    'bird_name'   => '',
                    'gender'      => '',
                    'color'       => $first['color'] ?? '',
                    'eye_color'   => '',
                    'birth_date'  => '',
                    'bloodline'   => '',
                    'source'      => 'race_results',
                    'owner_name'  => $first['owner_name'] ?? '',
                    'region'      => $first['region'] ?? '',
                    'best_speed'  => $first['speed'] ?? '',
                    'best_rank'   => $first['rank'] ?? '',
                    'race_count'  => count($raceResults),
                    'father'      => null,
                    'mother'      => null,
                ];
                echo json_encode(['success' => true, 'data' => $result], JSON_UNESCAPED_UNICODE);
                exit;
        }

        // 构建本鸽信息
        $result = [
            'ring_number' => $pigeon['ring_number'] ?? '',
            'bird_name'    => $pigeon['name'] ?? '',
            'gender'       => ($pigeon['gender'] == 1) ? '雄' : (($pigeon['gender'] == 2) ? '雌' : ''),
            'color'        => $pigeon['color'] ?? '',
            'eye_color'    => $pigeon['eye_color'] ?? '',
            'birth_date'   => $pigeon['birth_date'] ?? '',
            'bloodline'    => $pigeon['bloodline'] ?? '',
            'father'       => null,
            'mother'       => null,
        ];

        // 尝试从配对表中找父母
        try {
            $stmt = $this->pdo->prepare(
                "SELECT pp.*, 
                        m.name AS male_name, m.ring_number AS male_ring, m.bloodline AS male_bloodline,
                        f.name AS female_name, f.ring_number AS female_ring, f.bloodline AS female_bloodline
                 FROM pigeon_pairings pp
                 JOIN pigeons m ON pp.male_id = m.id
                 JOIN pigeons f ON pp.female_id = f.id
                 WHERE pp.user_id = ?
                 ORDER BY pp.created_at DESC
                 LIMIT 5"
            );
            $stmt->execute([$pigeon['user_id']]);
            $pairings = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($pairings as $p) {
                // 如果这只鸽子是配对子代，取它的父母
                if (!empty($p['male_name']) && !empty($p['female_name'])) {
                    $result['father'] = [
                        'ring_number'  => $p['male_ring'] ?? '',
                        'name'         => $p['male_name'] ?? '',
                        'bloodline'    => $p['male_bloodline'] ?? '',
                    ];
                    $result['mother'] = [
                        'ring_number'  => $p['female_ring'] ?? '',
                        'name'         => $p['female_name'] ?? '',
                        'bloodline'    => $p['female_bloodline'] ?? '',
                    ];
                    break;
                }
            }
        } catch (\Exception $e) {
            // 表不存在时静默跳过
        }

        echo json_encode(['success' => true, 'data' => $result], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function certificate() {
        $thisUrl = '/pedigree/certificate/';
        $cert = null;

        // POST: 生成证书
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $cert = [
                'ring_number'         => trim($_POST['ring_number'] ?? ''),
                'bird_name'           => trim($_POST['bird_name'] ?? ''),
                'gender'              => trim($_POST['gender'] ?? ''),
                'color'               => trim($_POST['color'] ?? ''),
                'eye_color'           => trim($_POST['eye_color'] ?? ''),
                'birth_date'          => trim($_POST['birth_date'] ?? ''),
                'father_ring'         => trim($_POST['father_ring'] ?? ''),
                'father_name'         => trim($_POST['father_name'] ?? ''),
                'father_strain'       => trim($_POST['father_strain'] ?? ''),
                'father_achievements' => trim($_POST['father_achievements'] ?? ''),
                'mother_ring'         => trim($_POST['mother_ring'] ?? ''),
                'mother_name'         => trim($_POST['mother_name'] ?? ''),
                'mother_strain'       => trim($_POST['mother_strain'] ?? ''),
                'mother_achievements' => trim($_POST['mother_achievements'] ?? ''),
                'grand_fa_father_ring'  => trim($_POST['grand_fa_father_ring'] ?? ''),
                'grand_fa_father_name'  => trim($_POST['grand_fa_father_name'] ?? ''),
                'grand_fa_mother_ring'  => trim($_POST['grand_fa_mother_ring'] ?? ''),
                'grand_fa_mother_name'  => trim($_POST['grand_fa_mother_name'] ?? ''),
                'grand_mo_father_ring'  => trim($_POST['grand_mo_father_ring'] ?? ''),
                'grand_mo_father_name'  => trim($_POST['grand_mo_father_name'] ?? ''),
                'grand_mo_mother_ring'  => trim($_POST['grand_mo_mother_ring'] ?? ''),
                'grand_mo_mother_name'  => trim($_POST['grand_mo_mother_name'] ?? ''),
                'breeder_name'          => trim($_POST['breeder_name'] ?? ''),
                'breeder_loft'          => trim($_POST['breeder_loft'] ?? ''),
                'breeder_phone'         => trim($_POST['breeder_phone'] ?? ''),
                'cert_id'               => 0,
            ];

            // 保存到数据库
            $userId = $_SESSION['user_id'] ?? null;
            if ($userId) {
                try {
                    $stmt = $this->pdo->prepare(
                        "INSERT INTO pedigree_certificates (user_id, ring_number, bird_name, gender, color, eye_color, birth_date, " .
                        "father_ring, father_name, father_strain, father_achievements, " .
                        "mother_ring, mother_name, mother_strain, mother_achievements, " .
                        "grand_fa_father_ring, grand_fa_father_name, grand_fa_mother_ring, grand_fa_mother_name, " .
                        "grand_mo_father_ring, grand_mo_father_name, grand_mo_mother_ring, grand_mo_mother_name, " .
                        "breeder_name, breeder_loft, breeder_phone) " .
                        "VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
                    );
                    $stmt->execute([
                        $userId,
                        $cert['ring_number'], $cert['bird_name'], $cert['gender'], $cert['color'], $cert['eye_color'], $cert['birth_date'],
                        $cert['father_ring'], $cert['father_name'], $cert['father_strain'], $cert['father_achievements'],
                        $cert['mother_ring'], $cert['mother_name'], $cert['mother_strain'], $cert['mother_achievements'],
                        $cert['grand_fa_father_ring'], $cert['grand_fa_father_name'], $cert['grand_fa_mother_ring'], $cert['grand_fa_mother_name'],
                        $cert['grand_mo_father_ring'], $cert['grand_mo_father_name'], $cert['grand_mo_mother_ring'], $cert['grand_mo_mother_name'],
                        $cert['breeder_name'], $cert['breeder_loft'], $cert['breeder_phone'],
                    ]);
                    $cert['cert_id'] = (int)$this->pdo->lastInsertId();
                } catch (PDOException $e) {
                    // 表不存在时静默跳过
                }
            }

            // 血统证书免费开放
            $unlocked = true;
        }

        $this->loadView('pedigree_certificate', [
            'pageTitle' => '血统证书生成器 | ' . SITE_NAME,
            'cert'      => $cert ?? null,
            'unlocked'  => $unlocked ?? false,
            'thisUrl'   => $thisUrl,
        ]);
    }
}