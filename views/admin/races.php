<?php
extract($data); $activeMenu = 'races';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($pageTitle); ?></title>
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php include __DIR__ . '/_styles.php'; ?>
</head>
<body>
<div class="admin-container">
    <?php include __DIR__ . '/_sidebar.php'; ?>
    <main class="main-content">
<?php include __DIR__ . '/_header.php'; ?>

        <!-- 操作按钮 + 筛选 -->
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px;flex-wrap:wrap;gap:12px;">
            <button onclick="openRaceModal(0)" class="btn btn-primary"><i class="fas fa-plus"></i> 新增赛事</button>
            <form method="get" action="admin.php" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                <input type="hidden" name="action" value="races">
                <select name="season_year" style="padding:6px 10px;border:1px solid #d1d5db;border-radius:4px;font-size:13px;">
                    <option value="">全部年份</option>
                    <?php foreach ($years as $y): $yv = $y['season_year']; ?>
                    <option value="<?php echo $yv; ?>" <?php echo ($seasonYear == $yv) ? 'selected' : ''; ?>><?php echo $yv; ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="season_type" style="padding:6px 10px;border:1px solid #d1d5db;border-radius:4px;font-size:13px;">
                    <option value="">全部赛季</option>
                    <option value="spring" <?php echo $seasonType === 'spring' ? 'selected' : ''; ?>>春赛</option>
                    <option value="autumn" <?php echo $seasonType === 'autumn' ? 'selected' : ''; ?>>秋赛</option>
                    <option value="other" <?php echo $seasonType === 'other' ? 'selected' : ''; ?>>其他</option>
                </select>
                <select name="race_category" style="padding:6px 10px;border:1px solid #d1d5db;border-radius:4px;font-size:13px;">
                    <option value="">全部类别</option>
                    <option value="final" <?php echo $raceCategory === 'final' ? 'selected' : ''; ?>>决赛</option>
                    <option value="pre" <?php echo $raceCategory === 'pre' ? 'selected' : ''; ?>>预赛</option>
                    <option value="train" <?php echo $raceCategory === 'train' ? 'selected' : ''; ?>>训放</option>
                    <option value="toll" <?php echo $raceCategory === 'toll' ? 'selected' : ''; ?>>收费站</option>
                    <option value="other" <?php echo $raceCategory === 'other' ? 'selected' : ''; ?>>其他</option>
                </select>
                <input type="text" name="keyword" value="<?php echo h($keyword); ?>" placeholder="搜索赛事/公棚..." style="padding:6px 12px;border:1px solid #d1d5db;border-radius:4px;font-size:13px;width:180px;">
                <button type="submit" class="btn btn-sm btn-primary" style="padding:6px 12px;">筛选</button>
                <a href="admin.php?action=races" class="btn btn-sm btn-secondary" style="padding:6px 12px;text-decoration:none;">重置</a>
            </form>
        </div>

        <div class="content-card">
            <div class="card-header">
                <h3>赛事列表（共 <?php echo $total; ?> 条）</h3>
            </div>
            <div class="card-body" style="overflow-x:auto;">
                <?php if (empty($races)): ?>
                <div class="empty-state"><i class="fas fa-flag-checkered"></i><p>暂无赛事</p></div>
                <?php else: ?>
                <table class="admin-table">
                    <thead><tr>
                        <th>ID</th>
                        <th>赛事名称</th>
                        <th>所属公棚</th>
                        <th>类别</th>
                        <th>赛季</th>
                        <th>放飞时间</th>
                        <th>空距</th>
                        <th>参赛/归巢</th>
                        <th>来源</th>
                        <th>状态</th>
                        <th>操作</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($races as $race): ?>
                    <tr>
                        <td><?php echo $race['id']; ?></td>
                        <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?php echo h($race['name']); ?>">
                            <?php echo h(mb_strlen($race['name']) > 28 ? mb_substr($race['name'], 0, 28) . '...' : $race['name']); ?>
                        </td>
                        <td><?php echo h($race['loft_name'] ?? '-'); ?></td>
                        <td>
                            <?php
                            $catLabels = ['final' => ['决赛','#dc2626'], 'pre' => ['预赛','#2563eb'], 'train' => ['训放','#6b7280'], 'toll' => ['收费站','#f59e0b'], 'other' => ['其他','#8b5cf6']];
                            $cat = $catLabels[$race['race_category']] ?? ['其他','#8b5cf6'];
                            ?>
                            <span style="display:inline-block;padding:2px 8px;border-radius:10px;font-size:12px;background:<?php echo $cat[1]; ?>15;color:<?php echo $cat[1]; ?>;border:1px solid <?php echo $cat[1]; ?>40;"><?php echo $cat[0]; ?></span>
                        </td>
                        <td><?php echo h(($race['season_year'] ?? '') . ($race['season_type'] ? (' ' . ($race['season_type'] === 'spring' ? '春' : ($race['season_type'] === 'autumn' ? '秋' : ''))) : '')); ?></td>
                        <td><?php echo $race['release_time'] ? date('m-d H:i', strtotime($race['release_time'])) : '-'; ?></td>
                        <td><?php echo $race['distance_km'] ? number_format($race['distance_km'], 2) . ' km' : '-'; ?></td>
                        <td><?php echo $race['entry_count']; ?> / <?php echo $race['returned_count']; ?></td>
                        <td>
                            <?php $ds = $race['data_source'] ?? 'manual'; ?>
                            <span style="font-size:11px;padding:1px 6px;border-radius:8px;background:<?php echo $ds === 'crawl' ? '#dbeafe' : '#fef3c7'; ?>;color:<?php echo $ds === 'crawl' ? '#1e40af' : '#92400e'; ?>;"><?php echo $ds === 'crawl' ? '爬虫' : '手动'; ?></span>
                        </td>
                        <td>
                            <?php
                            $stLabels = [0 => ['已取消','badge-danger'], 1 => ['报名中','badge-success'], 2 => ['进行中','badge-warning'], 3 => ['已结束','badge-secondary']];
                            $st = $stLabels[$race['status']] ?? ['未知','badge-secondary'];
                            ?>
                            <span class="badge <?php echo $st[1]; ?>"><?php echo $st[0]; ?></span>
                        </td>
                        <td class="actions">
                            <?php if ($race['source_id']): ?>
                            <a href="/race.php?id=<?php echo $race['id']; ?>" class="btn btn-sm btn-info" title="查看成绩">成绩</a>
                            <?php endif; ?>
                            <button onclick="openRaceModal(<?php echo $race['id']; ?>)" class="btn btn-sm btn-primary">编辑</button>
                            <button onclick="confirmDelete('race', <?php echo $race['id']; ?>, '<?php echo h(addslashes($race['name'] ?? '')); ?>')" class="btn btn-sm btn-danger">删除</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php
            $queryStr = '';
            if ($seasonYear) $queryStr .= "&season_year=$seasonYear";
            if ($seasonType) $queryStr .= "&season_type=$seasonType";
            if ($raceCategory) $queryStr .= "&race_category=$raceCategory";
            if ($keyword) $queryStr .= "&keyword=" . urlencode($keyword);

            $range = 2;
            $start_p = max(1, $page - $range);
            $end_p = min($totalPages, $page + $range);

            if ($page > 1): ?>
            <a href="?action=races&page=<?php echo $page-1 . $queryStr; ?>">上一页</a>
            <?php endif;

            if ($start_p > 1): ?>
            <a href="?action=races&page=1<?php echo $queryStr; ?>">1</a>
            <?php if ($start_p > 2): ?><span class="ellipsis" style="padding:8px 6px;color:#9ca3af;">...</span><?php endif; ?>
            <?php endif;

            for ($i = $start_p; $i <= $end_p; $i++): ?>
            <?php if ($i == $page): ?>
            <span class="active"><?php echo $i; ?></span>
            <?php else: ?>
            <a href="?action=races&page=<?php echo $i . $queryStr; ?>"><?php echo $i; ?></a>
            <?php endif; ?>
            <?php endfor;

            if ($end_p < $totalPages): ?>
            <?php if ($end_p < $totalPages - 1): ?><span class="ellipsis" style="padding:8px 6px;color:#9ca3af;">...</span><?php endif; ?>
            <a href="?action=races&page=<?php echo $totalPages . $queryStr; ?>"><?php echo $totalPages; ?></a>
            <?php endif;

            if ($page < $totalPages): ?>
            <a href="?action=races&page=<?php echo $page+1 . $queryStr; ?>">下一页</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- 赛事编辑模态框 -->
        <div class="modal-overlay" id="raceModal">
            <div class="modal" style="max-width:560px;">
                <div class="modal-header">
                    <h3 id="raceModalTitle">新增赛事</h3>
                    <button class="modal-close" onclick="closeModal('raceModal')">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="raceForm">
                        <input type="hidden" id="raceId" name="id" value="0">
                        <div class="form-group">
                            <label class="form-label">赛事名称 <span style="color:#ef4444;">*</span></label>
                            <input type="text" id="raceName" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">所属公棚</label>
                            <input type="text" id="raceLoft" name="loft_name_display" class="form-control" placeholder="输入公棚名称搜索..." autocomplete="off">
                            <input type="hidden" id="raceLoftId" name="loft_id" value="">
                            <div id="loftSuggestions" style="display:none;position:absolute;z-index:999;background:#fff;border:1px solid #d1d5db;border-radius:6px;max-height:180px;overflow-y:auto;width:100%;box-shadow:0 4px 12px rgba(0,0,0,0.1);"></div>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                            <div class="form-group">
                                <label class="form-label">赛事类别</label>
                                <select id="raceCategory" name="race_category" class="form-control">
                                    <option value="final">决赛</option>
                                    <option value="pre">预赛</option>
                                    <option value="train">训放</option>
                                    <option value="toll">收费站</option>
                                    <option value="other">其他</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">状态</label>
                                <select id="raceStatus" name="status" class="form-control">
                                    <option value="1">报名中</option>
                                    <option value="2">进行中</option>
                                    <option value="3">已结束</option>
                                    <option value="0">已取消</option>
                                </select>
                            </div>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                            <div class="form-group">
                                <label class="form-label">赛季年份</label>
                                <input type="number" id="raceSeasonYear" name="season_year" class="form-control" min="2000" max="2099" placeholder="如 2025">
                            </div>
                            <div class="form-group">
                                <label class="form-label">赛季类型</label>
                                <select id="raceSeasonType" name="season_type" class="form-control">
                                    <option value="spring">春赛</option>
                                    <option value="autumn">秋赛</option>
                                    <option value="other">其他</option>
                                </select>
                            </div>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                            <div class="form-group">
                                <label class="form-label">放飞时间</label>
                                <input type="datetime-local" id="raceReleaseTime" name="release_time" class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="form-label">放飞地点</label>
                                <input type="text" id="raceLocation" name="release_location" class="form-control">
                            </div>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                            <div class="form-group">
                                <label class="form-label">空距 (km)</label>
                                <input type="number" step="0.01" id="raceDistance" name="distance_km" class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="form-label">参赛羽数</label>
                                <input type="number" id="raceEntryCount" name="entry_count" class="form-control">
                            </div>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                            <div class="form-group">
                                <label class="form-label">归巢羽数</label>
                                <input type="number" id="raceReturnedCount" name="returned_count" class="form-control">
                            </div>
                        </div>
                        <div class="modal-footer" style="padding:0;border:none;margin-top:20px;">
                            <button type="button" class="btn btn-secondary" onclick="closeModal('raceModal')">取消</button>
                            <button type="button" class="btn btn-primary" onclick="saveRace()">保存</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>
<?php include __DIR__ . '/_scripts.php'; ?>
<script>
var raceListData = <?php echo json_encode($races ?? []); ?>;
var loftListData = <?php echo json_encode($lofts ?? []); ?>;

function openRaceModal(id) {
    var titleEl = document.getElementById('raceModalTitle');
    if (id > 0) {
        titleEl.textContent = '编辑赛事';
        var race = raceListData.find(function(r) { return r.id == id; });
        if (race) {
            document.getElementById('raceId').value = race.id;
            document.getElementById('raceName').value = race.name || '';
            document.getElementById('raceCategory').value = race.race_category || 'other';
            document.getElementById('raceStatus').value = race.status || 1;
            document.getElementById('raceSeasonYear').value = race.season_year || '';
            document.getElementById('raceSeasonType').value = race.season_type || 'other';
            document.getElementById('raceReleaseTime').value = (race.release_time || '').replace(' ', 'T');
            document.getElementById('raceLocation').value = race.release_location || '';
            document.getElementById('raceDistance').value = race.distance_km || '';
            document.getElementById('raceEntryCount').value = race.entry_count || '';
            document.getElementById('raceReturnedCount').value = race.returned_count || '';
            document.getElementById('raceLoftId').value = race.loft_id || '';
            document.getElementById('raceLoft').value = race.loft_name || '';
        }
    } else {
        titleEl.textContent = '新增赛事';
        document.getElementById('raceForm').reset();
        document.getElementById('raceId').value = 0;
        document.getElementById('raceLoftId').value = '';
        document.getElementById('raceLoft').value = '';
    }
    openModal('raceModal');
}

// 公棚模糊搜索
var loftSearchTimeout;
document.getElementById('raceLoft').addEventListener('input', function() {
    clearTimeout(loftSearchTimeout);
    var q = this.value.trim();
    var sug = document.getElementById('loftSuggestions');
    if (q.length < 1) { sug.style.display = 'none'; return; }
    loftSearchTimeout = setTimeout(function() {
        var matches = loftListData.filter(function(l) {
            return l.name && l.name.toLowerCase().indexOf(q.toLowerCase()) >= 0;
        }).slice(0, 6);
        if (matches.length) {
            sug.innerHTML = matches.map(function(l) {
                return '<div style="padding:8px 12px;cursor:pointer;border-bottom:1px solid #f3f4f6;" ' +
                    'onmouseover="this.style.background=\'#f0f9ff\'" onmouseout="this.style.background=\'\'" ' +
                    'onclick="selectLoft(' + l.id + ',\'' + (l.name || '').replace(/'/g, "\\'") + '\')">' +
                    (l.name || l.id) + '</div>';
            }).join('');
            sug.style.display = 'block';
        } else {
            sug.style.display = 'none';
        }
    }, 200);
});

document.addEventListener('click', function(e) {
    var sug = document.getElementById('loftSuggestions');
    var loft = document.getElementById('raceLoft');
    if (e.target !== loft && e.target !== sug && !sug.contains(e.target)) {
        sug.style.display = 'none';
    }
});

function selectLoft(id, name) {
    document.getElementById('raceLoftId').value = id;
    document.getElementById('raceLoft').value = name;
    document.getElementById('loftSuggestions').style.display = 'none';
}

function saveRace() {
    var formData = new FormData(document.getElementById('raceForm'));
    fetch('/admin.php?action=save-race', { method: 'POST', body: formData })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(function() { location.reload(); }, 1000);
        } else { showToast(data.message, 'error'); }
    })
    .catch(function() { showToast('保存失败', 'error'); });
}
</script>
<style>
/* 公棚搜索下拉 */
#raceLoft + #loftSuggestions { margin-top:2px; }
.admin-table td { vertical-align:middle; }
</style>
</body>
</html>
