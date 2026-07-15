<?php
require_once dirname(__DIR__) . "/app/config/config.php";
extract($data);
$page_title = $pageTitle ?? "我的配对 | " . SITE_NAME;
$noindex = true;
$active_page = "pairings";
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="管理您在信鸽之家创建的铭鸽配对记录。">
    <meta name="keywords" content="我的配对,配对记录,信鸽之家">
    <meta property="og:title" content="我的配对 - 信鸽之家">
    <meta property="og:description" content="管理您在信鸽之家创建的铭鸽配对记录。">
    <meta property="og:image" content="https://www.xgjia.com/public/images/og-cover.png">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="信鸽之家">
    <link rel="canonical" href="https://www.xgjia.com/user/pairings">

    <title><?php echo h($page_title); ?></title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/b-scheme.css">
    <link rel="shortcut icon" href="/public/images/favicon.ico">
    <style>
            :root {
                --primary: #1a5fa8;
                --primary-light: #2980b9;
                --primary-dark: #154360;
                --accent: #c9a84c;
                --accent-light: #e0c060;
                --bg: #f4f6f9;
                --white: #ffffff;
                --text: #2c3e50;
                --text-light: #6c7a89;
                --border: #e8ecf0;
                --shadow: 0 2px 12px rgba(26,95,168,0.08);
                --shadow-hover: 0 8px 30px rgba(26,95,168,0.15);
                --gold: #d4a843;
                --success: #27ae60;
                --danger: #e74c3c;
                --radius: 12px;
            }

        .page-title { font-size: 24px; font-weight: bold; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .page-title i { color: #d4a843; }
        .pairing-card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 16px; }
        .pairing-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; }
        .pairing-pair { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }
        .pigeon-mini { display: flex; align-items: center; gap: 10px; flex: 1; min-width: 200px; padding: 10px; background: #f9fafb; border-radius: 8px; }
        .pigeon-mini img { width: 50px; height: 50px; border-radius: 50%; object-fit: cover; background: #e5e7eb; }
        .pigeon-mini .info { flex: 1; }
        .pigeon-mini .name { font-weight: bold; font-size: 14px; }
        .pigeon-mini .ring { font-size: 12px; color: #6b7280; }
        .pairing-heart { font-size: 28px; color: #ef4444; flex-shrink: 0; }
        .pairing-notes { font-size: 13px; color: #6b7280; margin-top: 10px; padding-top: 10px; border-top: 1px solid #f3f4f6; }
        .pairing-status { font-size: 12px; padding: 3px 10px; border-radius: 12px; }
        .status-0 { background: #fef3c7; color: #92400e; }
        .status-1 { background: #d1fae5; color: #065f46; }
        .status-2 { background: #dbeafe; color: #1e40af; }
        .btn-delete { color: #ef4444; cursor: pointer; font-size: 13px; background: none; border: none; }
        .btn-delete:hover { text-decoration: underline; }
        .btn-create { background: #1a2a3a; color: white; border: none; padding: 10px 24px; border-radius: 8px; font-size: 14px; cursor: pointer; }
        .btn-create:hover { background: #2d4a6a; }
        .empty-state { text-align: center; padding: 60px 20px; color: #9ca3af; }
        .empty-state i { font-size: 64px; margin-bottom: 16px; }
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; }
        .modal-overlay.active { display: flex; }
        .modal-box { background: white; border-radius: 16px; padding: 30px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto; }
        .modal-box h3 { font-size: 20px; margin-bottom: 20px; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 14px; font-weight: 600; margin-bottom: 6px; }
        .search-select { width: 100%; padding: 10px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; }
        .search-results { max-height: 200px; overflow-y: auto; border: 1px solid #e5e7eb; border-radius: 8px; margin-top: 6px; }
        .search-item { padding: 10px; cursor: pointer; display: flex; align-items: center; gap: 10px; border-bottom: 1px solid #f3f4f6; }
        .search-item:hover { background: #f9fafb; }
        .search-item.selected { background: #eff6ff; }
        .search-item img { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; }
        .modal-actions { display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/_head.php'; ?>

    <!-- 主内容区 -->
    <div class="container content-with-sidebar" style="margin-bottom: 60px;">
        <?php require_once __DIR__ . "/user/sidebar.php"; ?>
        <div class="main-content">
            <div class="page-title"><i class="fas fa-heart"></i> 我的配对 <span style="font-size:14px;color:#6b7280;font-weight:normal;">(<?php echo $total; ?>)</span></div>
            <button class="btn-create" onclick="openCreateModal()" style="margin-bottom:20px;"><i class="fas fa-plus" style="margin-right:6px;"></i>新建配对</button>

            <?php if (!empty($pairings)): ?>
            <?php foreach ($pairings as $pairing): ?>
            <div class="pairing-card">
                <div class="pairing-header">
                    <span class="pairing-status status-<?php echo $pairing["status"]; ?>">
                        <?php echo ["0"=>"计划中","1"=>"已配对","2"=>"有后代"][$pairing["status"]]; ?>
                    </span>
                    <button class="btn-delete" onclick="deletePairing(<?php echo $pairing["id"]; ?>)"><i class="fas fa-trash"></i> 删除</button>
                </div>
                <div class="pairing-pair">
                    <div class="pigeon-mini">
                        <?php $mImg = json_decode($pairing["male_images"] ?? "[]", true) ?: []; ?>
                        <?php if (!empty($mImg[0])): ?><img loading="lazy" src="<?php echo h($mImg[0]); ?>" alt="铭鸽图片"><?php else: ?><div style="width:50px;height:50px;border-radius:50%;background:#e5e7eb;display:flex;align-items:center;justify-content:center;"><i class="fas fa-dove" style="color:#9ca3af;"></i></div><?php endif; ?>
                        <div class="info"><div class="name">♂ <?php echo h($pairing["male_name"] ?? "未知"); ?></div><div class="ring"><?php echo h($pairing["male_ring"] ?? ""); ?></div></div>
                    </div>
                    <div class="pairing-heart"><i class="fas fa-heart"></i></div>
                    <div class="pigeon-mini">
                        <?php $fImg = json_decode($pairing["female_images"] ?? "[]", true) ?: []; ?>
                        <?php if (!empty($fImg[0])): ?><img loading="lazy" src="<?php echo h($fImg[0]); ?>" alt="铭鸽图片"><?php else: ?><div style="width:50px;height:50px;border-radius:50%;background:#e5e7eb;display:flex;align-items:center;justify-content:center;"><i class="fas fa-dove" style="color:#9ca3af;"></i></div><?php endif; ?>
                        <div class="info"><div class="name">♀ <?php echo h($pairing["female_name"] ?? "未知"); ?></div><div class="ring"><?php echo h($pairing["female_ring"] ?? ""); ?></div></div>
                    </div>
                </div>
                <?php if (!empty($pairing["notes"])): ?>
                <div class="pairing-notes"><i class="fas fa-sticky-note" style="margin-right:4px;"></i><?php echo h($pairing["notes"]); ?></div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <div class="empty-state"><i class="fas fa-heart"></i><p>还没有配对记录</p><p style="font-size:13px;margin-top:8px;">点击上方按钮创建您的第一个配对</p></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 创建配对模态框 -->
    <div id="createModal" class="modal-overlay">
        <div class="modal-box">
            <h3><i class="fas fa-heart" style="color:#ef4444;margin-right:8px;"></i>新建配对</h3>
            <form id="pairingForm">
                <div class="form-group">
                    <label>♂ 雄鸽</label>
                    <input type="text" class="search-select" id="maleSearch" placeholder="搜索雄鸽名称或足环号..." oninput="searchPigeons(this.value, \"male\")">
                    <div id="maleResults" class="search-results" style="display:none;"></div>
                    <input type="hidden" id="maleId">
                    <div id="maleSelected" style="margin-top:6px;"></div>
                </div>
                <div class="form-group">
                    <label>♀ 雌鸽</label>
                    <input type="text" class="search-select" id="femaleSearch" placeholder="搜索雌鸽名称或足环号..." oninput="searchPigeons(this.value, \"female\")">
                    <div id="femaleResults" class="search-results" style="display:none;"></div>
                    <input type="hidden" id="femaleId">
                    <div id="femaleSelected" style="margin-top:6px;"></div>
                </div>
                <div class="form-group">
                    <label>配对说明（可选）</label>
                    <textarea class="search-select" id="pairingNotes" rows="3" placeholder="填写配对目的、期望等..." style="resize:vertical;"></textarea>
                </div>
            </form>
            <div class="modal-actions">
                <button onclick="closeCreateModal()" style="padding:10px 24px;border:1px solid #e5e7eb;border-radius:8px;background:white;cursor:pointer;">取消</button>
                <button onclick="submitPairing()" class="btn-create">确认配对</button>
            </div>
        </div>
    </div>


    <?php include __DIR__ . '/_footer.php'; ?>

    <script>
    // ========== 模态框控制 ==========
    function openCreateModal() {
        document.getElementById('createModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeCreateModal() {
        document.getElementById('createModal').classList.remove('active');
        document.body.style.overflow = '';
        // 清空表单
        document.getElementById('maleSearch').value = '';
        document.getElementById('maleId').value = '';
        document.getElementById('maleResults').innerHTML = '';
        document.getElementById('maleResults').style.display = 'none';
        document.getElementById('maleSelected').innerHTML = '';
        
        document.getElementById('femaleSearch').value = '';
        document.getElementById('femaleId').value = '';
        document.getElementById('femaleResults').innerHTML = '';
        document.getElementById('femaleResults').style.display = 'none';
        document.getElementById('femaleSelected').innerHTML = '';
        
        document.getElementById('pairingNotes').value = '';
    }

    // ========== 搜索鸽子（防抖300ms） ==========
    let searchTimeout = null;
    function searchPigeons(keyword, gender) {
        if (searchTimeout) clearTimeout(searchTimeout);
        
        searchTimeout = setTimeout(() => {
            if (!keyword || keyword.length < 1) {
                if (gender === 'male') {
                    document.getElementById('maleResults').style.display = 'none';
                } else {
                    document.getElementById('femaleResults').style.display = 'none';
                }
                return;
            }

            // 根据性别设置gender参数：male→1（雄），female→2（雌）
            const genderParam = gender === 'male' ? 1 : 2;

            fetch(`/pedigree/?action=search_pigeons&q=${encodeURIComponent(keyword)}&gender=${genderParam}`)
                .then(res => res.json())
                .then(data => {
                    const resultsId = gender === 'male' ? 'maleResults' : 'femaleResults';
                    const results = document.getElementById(resultsId);
                    results.innerHTML = '';

                    if (data.success && data.data && data.data.length > 0) {
                        data.data.forEach(p => {
                            const div = document.createElement('div');
                            div.className = 'search-item';
                            div.innerHTML = `
                                <img loading="lazy" src="${p.image || '/public/images/default-pigeon.png'}" alt="铭鸽图片">
                                <div class="info">
                                    <div class="name">${p.name || '未命名'}</div>
                                    <div class="ring">${p.ring_number || ''} ${p.bloodline ? '| ' + p.bloodline : ''}</div>
                                </div>
                            `;
                            div.onclick = () => selectPigeon(p.id, p.name || p.ring_number, gender);
                            results.appendChild(div);
                        });
                        results.style.display = 'block';
                    } else {
                        results.innerHTML = `<div style="padding: 12px; color: #999; text-align: center;">未找到匹配的${gender === 'male' ? '雄鸽' : '雌鸽'}</div>`;
                        results.style.display = 'block';
                    }
                })
                .catch(err => {
                    console.error('搜索失败:', err);
                });
        }, 300);
    }

    // ========== 选择鸽子 ==========
    function selectPigeon(id, name, gender) {
        if (gender === 'male') {
            document.getElementById('maleId').value = id;
            document.getElementById('maleSearch').value = name;
            document.getElementById('maleSelected').innerHTML = `<span style="color: #059669; font-size: 13px;"><i class="fas fa-check-circle"></i> 已选择：${name}</span>`;
            document.getElementById('maleResults').style.display = 'none';
        } else {
            document.getElementById('femaleId').value = id;
            document.getElementById('femaleSearch').value = name;
            document.getElementById('femaleSelected').innerHTML = `<span style="color: #059669; font-size: 13px;"><i class="fas fa-check-circle"></i> 已选择：${name}</span>`;
            document.getElementById('femaleResults').style.display = 'none';
        }
    }

    // ========== 点击外部关闭搜索结果 ==========
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#maleSearch') && !e.target.closest('#maleResults')) {
            document.getElementById('maleResults').style.display = 'none';
        }
        if (!e.target.closest('#femaleSearch') && !e.target.closest('#femaleResults')) {
            document.getElementById('femaleResults').style.display = 'none';
        }
    });

    // ========== 提交配对 ==========
    function submitPairing() {
        const maleId = document.getElementById('maleId').value;
        const femaleId = document.getElementById('femaleId').value;
        const notes = document.getElementById('pairingNotes').value;

        // 验证
        if (!maleId) {
            alert('请选择雄鸽');
            return;
        }
        if (!femaleId) {
            alert('请选择雌鸽');
            return;
        }
        if (maleId === femaleId) {
            alert('不能选择同一只鸽子作为雄鸽和雌鸽');
            return;
        }

        // 提交
        const formData = new FormData();
        formData.append('action', 'create_pairing');
        formData.append('sire_id', maleId);
        formData.append('dam_id', femaleId);
        formData.append('notes', notes);

        fetch('/pedigree/', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('配对创建成功！');
                closeCreateModal();
                window.location.reload();
            } else {
                alert('创建失败：' + (data.message || '未知错误'));
            }
        })
        .catch(err => {
            console.error('提交失败:', err);
            alert('提交失败，请重试');
        });
    }

    // ========== 删除配对 ==========
    function deletePairing(pairingId) {
        if (!confirm('确定要删除此配对吗？')) return;

        const formData = new FormData();
        formData.append('action', 'delete_pairing');
        formData.append('pairing_id', pairingId);

        fetch('/pedigree/', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('删除成功！');
                window.location.reload();
            } else {
                alert('删除失败：' + (data.message || '未知错误'));
            }
        })
        .catch(err => {
            console.error('删除失败:', err);
            alert('删除失败，请重试');
        });
    }

    // ========== 导航栏菜单切换 ==========
    function toggleMenu() {
        const menu = document.getElementById('navbarMenu');
        menu.classList.toggle('active');
    }
    </script>
</body>
</html>
