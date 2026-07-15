<?php
$pageTitle = "创建配对 | " . SITE_NAME;
$breadcrumbs = [["title" => "会员中心", "url" => "/user"], ["title" => "我的配对", "url" => "/user/pairings"], ["title" => "创建配对"]];
include 'views/partials/header.php';
?>

<div class="container">
    <div class="main-content">
        <?php include 'views/user/sidebar.php'; ?>
        <div class="content-with-sidebar">
            <h1>创建新配对</h1>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="/user/pairings_create" id="pairingForm">
                <!-- 父鸽（雄）搜索选择 -->
                <div class="form-group">
                    <label for="sire_search">父鸽（雄）</label>
                    <div style="position: relative;">
                        <input type="text" 
                               id="sire_search" 
                               class="form-control" 
                               placeholder="输入铭鸽名称或足环号搜索..." 
                               autocomplete="off">
                        <input type="hidden" name="sire_id" id="sire_id" required>
                        <div id="sire_results" 
                             style="display: none; position: absolute; top: 100%; left: 0; right: 0; 
                                    background: white; border: 1px solid #ddd; border-radius: 4px;
                                    max-height: 200px; overflow-y: auto; z-index: 1000; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                        </div>
                    </div>
                    <div id="sire_selected" 
                         style="margin-top: 8px; padding: 8px 12px; background: #f0f9ff; border-radius: 4px; display: none;">
                        <strong>已选择：</strong><span id="sire_name"></span> 
                        <button type="button" onclick="clearSire()" 
                                style="margin-left: 8px; color: #ef4444; background: none; border: none; cursor: pointer; font-size: 12px;">
                            清除
                        </button>
                    </div>
                </div>

                <!-- 母鸽（雌）搜索选择 -->
                <div class="form-group">
                    <label for="dam_search">母鸽（雌）</label>
                    <div style="position: relative;">
                        <input type="text" 
                               id="dam_search" 
                               class="form-control" 
                               placeholder="输入铭鸽名称或足环号搜索..." 
                               autocomplete="off">
                        <input type="hidden" name="dam_id" id="dam_id" required>
                        <div id="dam_results" 
                             style="display: none; position: absolute; top: 100%; left: 0; right: 0; 
                                    background: white; border: 1px solid #ddd; border-radius: 4px;
                                    max-height: 200px; overflow-y: auto; z-index: 1000; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                        </div>
                    </div>
                    <div id="dam_selected" 
                         style="margin-top: 8px; padding: 8px 12px; background: #f0f9ff; border-radius: 4px; display: none;">
                        <strong>已选择：</strong><span id="dam_name"></span> 
                        <button type="button" onclick="clearDam()" 
                                style="margin-left: 8px; color: #ef4444; background: none; border: none; cursor: pointer; font-size: 12px;">
                            清除
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="pairing_date">配对日期</label>
                    <input type="date" name="pairing_date" id="pairing_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <div class="form-group">
                    <label for="notes">备注</label>
                    <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="可选"></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">保存配对</button>
                    <a href="/user/pairings" class="btn btn-secondary">取消</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// 搜索父鸽（雄）
let sireTimer = null;
document.getElementById('sire_search').addEventListener('input', function() {
    clearTimeout(sireTimer);
    const keyword = this.value.trim();
    
    if (keyword.length < 2) {
        document.getElementById('sire_results').style.display = 'none';
        return;
    }
    
    sireTimer = setTimeout(() => {
        fetch(`/pedigree/?action=searchPigeons&q=${encodeURIComponent(keyword)}&gender=1`)
            .then(res => res.json())
            .then(data => {
                const results = document.getElementById('sire_results');
                results.innerHTML = '';
                
                if (data.success && data.data && data.data.length > 0) {
                    data.data.forEach(p => {
                        const div = document.createElement('div');
                        div.style.padding = '10px 12px';
                        div.style.cursor = 'pointer';
                        div.style.borderBottom = '1px solid #f0f0f0';
                        div.innerHTML = `
                            <div style="font-weight: 600; font-size: 14px; margin-bottom: 2px;">${p.name || '未命名'}</div>
                            <div style="color: #666; font-size: 12px;">
                                ${p.ring_number ? '足环: ' + p.ring_number : ''} 
                                ${p.bloodline ? ' | 血统: ' + p.bloodline : ''}
                            </div>
                        `;
                        div.onmouseover = () => div.style.background = '#f5f5f5';
                        div.onmouseout = () => div.style.background = 'white';
                        div.onclick = () => selectSire(p.id, p.name || p.ring_number);
                        results.appendChild(div);
                    });
                    results.style.display = 'block';
                } else {
                    results.innerHTML = '<div style="padding: 12px; color: #999; text-align: center;">未找到匹配的雄鸽</div>';
                    results.style.display = 'block';
                }
            })
            .catch(err => {
                console.error('搜索父鸽失败:', err);
            });
    }, 300);
});

// 搜索母鸽（雌）
let damTimer = null;
document.getElementById('dam_search').addEventListener('input', function() {
    clearTimeout(damTimer);
    const keyword = this.value.trim();
    
    if (keyword.length < 2) {
        document.getElementById('dam_results').style.display = 'none';
        return;
    }
    
    damTimer = setTimeout(() => {
        fetch(`/pedigree/?action=searchPigeons&q=${encodeURIComponent(keyword)}&gender=2`)
            .then(res => res.json())
            .then(data => {
                const results = document.getElementById('dam_results');
                results.innerHTML = '';
                
                if (data.success && data.data && data.data.length > 0) {
                    data.data.forEach(p => {
                        const div = document.createElement('div');
                        div.style.padding = '10px 12px';
                        div.style.cursor = 'pointer';
                        div.style.borderBottom = '1px solid #f0f0f0';
                        div.innerHTML = `
                            <div style="font-weight: 600; font-size: 14px; margin-bottom: 2px;">${p.name || '未命名'}</div>
                            <div style="color: #666; font-size: 12px;">
                                ${p.ring_number ? '足环: ' + p.ring_number : ''} 
                                ${p.bloodline ? ' | 血统: ' + p.bloodline : ''}
                            </div>
                        `;
                        div.onmouseover = () => div.style.background = '#f5f5f5';
                        div.onmouseout = () => div.style.background = 'white';
                        div.onclick = () => selectDam(p.id, p.name || p.ring_number);
                        results.appendChild(div);
                    });
                    results.style.display = 'block';
                } else {
                    results.innerHTML = '<div style="padding: 12px; color: #999; text-align: center;">未找到匹配的雌鸽</div>';
                    results.style.display = 'block';
                }
            })
            .catch(err => {
                console.error('搜索母鸽失败:', err);
            });
    }, 300);
});

// 选择父鸽
function selectSire(id, name) {
    document.getElementById('sire_id').value = id;
    document.getElementById('sire_search').value = name;
    document.getElementById('sire_name').textContent = name;
    document.getElementById('sire_selected').style.display = 'block';
    document.getElementById('sire_results').style.display = 'none';
}

// 选择母鸽
function selectDam(id, name) {
    document.getElementById('dam_id').value = id;
    document.getElementById('dam_search').value = name;
    document.getElementById('dam_name').textContent = name;
    document.getElementById('dam_selected').style.display = 'block';
    document.getElementById('dam_results').style.display = 'none';
}

// 清除父鸽选择
function clearSire() {
    document.getElementById('sire_id').value = '';
    document.getElementById('sire_search').value = '';
    document.getElementById('sire_name').textContent = '';
    document.getElementById('sire_selected').style.display = 'none';
}

// 清除母鸽选择
function clearDam() {
    document.getElementById('dam_id').value = '';
    document.getElementById('dam_search').value = '';
    document.getElementById('dam_name').textContent = '';
    document.getElementById('dam_selected').style.display = 'none';
}

// 点击页面其他区域时关闭搜索结果
document.addEventListener('click', function(e) {
    if (!e.target.closest('#sire_search') && !e.target.closest('#sire_results')) {
        document.getElementById('sire_results').style.display = 'none';
    }
    if (!e.target.closest('#dam_search') && !e.target.closest('#dam_results')) {
        document.getElementById('dam_results').style.display = 'none';
    }
});

// 表单提交前验证
document.getElementById('pairingForm').addEventListener('submit', function(e) {
    if (!document.getElementById('sire_id').value) {
        alert('请选择父鸽（雄）');
        e.preventDefault();
        return false;
    }
    if (!document.getElementById('dam_id').value) {
        alert('请选择母鸽（雌）');
        e.preventDefault();
        return false;
    }
    
    // 检查不能选择同一只鸽子
    if (document.getElementById('sire_id').value === document.getElementById('dam_id').value) {
        alert('不能选择同一只铭鸽作为父鸽和母鸽');
        e.preventDefault();
        return false;
    }
});
</script>

<?php include 'views/partials/footer.php'; ?>
