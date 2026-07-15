<?php
/**
 * 血统证书 - 表单 + 生成（含站点头尾 + 移动端适配）
 */
$cert = $cert ?? null;
$isGenerated = $cert !== null;
$unlocked = $unlocked ?? true; // 从控制器传入
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <?php
    $page_title = '血统证书生成器';
    $meta_description = '免费在线生成赛鸽血统证书，输入足环号一键搜索数据库自动填充。支持打印导出PDF，卖鸽必备、育种记录。';
    $meta_keywords = '血统证书,赛鸽血统,信鸽血统书,鸽子血统证书生成器,足环号查询';
    $og_type = 'website';
    include __DIR__ . '/_seo_head.php';
    ?>
    <link rel="stylesheet" href="/public/css/style.css">
    <link rel="stylesheet" href="/public/css/b-scheme.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<style>
/* ===== 页面整体 ===== */
.certificate-page { max-width: 1200px; margin: 0 auto; padding: 20px 16px; }
.cert-breadcrumb { font-size: 13px; color: #94a3b8; margin-bottom: 12px; }
.cert-breadcrumb a { color: #1a5fa8; text-decoration: none; }

/* ===== 搜索框 ===== */
.cert-search-box { background: linear-gradient(135deg, #f0f4ff 0%, #e8f0fe 100%); border-radius: 10px; padding: 16px 20px; margin-bottom: 16px; border: 1px dashed #b3d4ff; }
.cert-search-inner { display: flex; gap: 10px; align-items: center; }
.cert-search-icon { color: #1a5fa8; font-size: 18px; flex-shrink: 0; }
.cert-search-inner input { flex: 1; padding: 10px 14px; border: 1px solid #d0d7e2; border-radius: 8px; font-size: 15px; outline: none; min-width: 0; }
.cert-search-inner input:focus { border-color: #1a5fa8; box-shadow: 0 0 0 3px rgba(26,95,168,0.1); }
.cert-search-inner .btn-sm { padding: 10px 20px; font-size: 14px; white-space: nowrap; flex-shrink: 0; }
.cert-search-results { margin-top: 10px; background: #fff; border-radius: 8px; box-shadow: 0 4px 16px rgba(0,0,0,0.1); overflow: hidden; }
.cert-search-hit { padding: 10px 0; }
.cert-hit-row { padding: 10px 14px; display: flex; align-items: center; gap: 8px; border-bottom: 1px solid #f0f0f0; flex-wrap: wrap; }
.cert-hit-row:last-child { border-bottom: none; }
.cert-hit-row:hover { background: #f0f4ff; }
.cert-hit-tag { font-size: 11px; background: #e8f5e9; color: #2e7d32; padding: 2px 8px; border-radius: 10px; font-weight: 600; flex-shrink: 0; }
.cert-hit-tag-race { background: #fff3e0; color: #e65100; }
.cert-status-loading { color: #666; font-size: 13px; }
.cert-status-error { color: #e53e3e; font-size: 13px; }
.cert-status-ok { color: #1a5fa8; font-size: 14px; font-weight: 600; }

/* ===== 表单 ===== */
.cert-form { background: #fff; border-radius: 8px; padding: 24px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); }

/* ===== 折叠式 fieldset ===== */
.cert-fieldset { border: 1px solid #e2e8f0; border-radius: 8px; padding: 0; margin-bottom: 14px; overflow: hidden; }
.cert-legend { display: flex; align-items: center; gap: 8px; width: 100%; padding: 12px 16px; font-size: 15px; font-weight: 700; color: #1a5fa8; background: #f8fafc; cursor: default; margin: 0; border-bottom: 1px solid #e2e8f0; box-sizing: border-box; }
.cert-collapsible .cert-legend { cursor: pointer; user-select: none; -webkit-user-select: none; transition: background .15s; }
.cert-collapsible .cert-legend:hover { background: #eef2f7; }
.cert-arrow { margin-left: auto; font-size: 12px; color: #94a3b8; transition: transform .2s; }
.cert-body-inner { padding: 16px; }

/* ===== 表单元素 ===== */
.cert-form .form-row { display: flex; gap: 14px; margin-bottom: 10px; }
.cert-form .form-row .form-group { flex: 1; min-width: 0; }
.cert-form .form-row-3 .form-group { flex: 1; min-width: 0; }
.cert-form .form-group { margin-bottom: 10px; }
.cert-form .form-group label { display: block; font-weight: 600; margin-bottom: 4px; font-size: 13px; color: #333; }
.cert-form .form-group input, .cert-form .form-group select { width: 100%; padding: 9px 12px; border: 1px solid #cbd5e0; border-radius: 6px; font-size: 14px; box-sizing: border-box; background: #fff; }
.cert-form .form-group input:focus, .cert-form .form-group select:focus { border-color: #1a5fa8; outline: none; box-shadow: 0 0 0 3px rgba(26,95,168,0.1); }
.required { color: #e53e3e; }
.optional-tag { font-size: 11px; color: #999; font-weight: 400; margin-left: 4px; }

/* ===== 页面标题 ===== */
.cert-page-title { font-size: 24px; font-weight: 800; color: #1e3a8a; margin: 0 0 6px; display: flex; align-items: center; gap: 10px; }
.cert-page-desc { font-size: 14px; color: #6c7a89; margin: 0 0 20px; line-height: 1.6; }

/* ===== 父母双栏 ===== */
.parent-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.parent-label { font-size: 14px; margin: 0 0 12px; padding-bottom: 8px; border-bottom: 2px solid; }
.father-label { color: #1a5fa8; border-color: #b3d4ff; }
.mother-label { color: #c53070; border-color: #fcc0d3; }

/* ===== 祖辈4栏 ===== */
.grand-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
.grand-col h4 { font-size: 12px; color: #666; margin: 0 0 8px; }
.grand-col input { width: 100%; padding: 7px 10px; border: 1px solid #cbd5e0; border-radius: 5px; font-size: 13px; box-sizing: border-box; margin-bottom: 5px; }

/* ===== 按钮 ===== */
.form-actions { text-align: center; margin-top: 20px; }
.form-actions .btn-lg { padding: 12px 36px; font-size: 16px; border-radius: 8px; }

/* ===== 证书工具条 ===== */
.cert-toolbar { text-align: center; margin-bottom: 20px; }
.cert-toolbar-bottom { margin-top: 24px; margin-bottom: 16px; }
.cert-hint { font-size: 13px; color: #999; margin-top: 8px; }
.btn-outline { background: #fff; color: #1a5fa8; border: 2px solid #1a5fa8; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; }
.btn-outline:hover { background: #f0f4ff; }

/* ===== 生成结果区域 ===== */
.cert-result-header { text-align: center; margin-bottom: 24px; }
.cert-result-header h1 { font-size: 24px; color: #1e3a8a; margin: 0 0 4px; }
.cert-result-header p { color: #6c7a89; font-size: 14px; }

/* ===== 侧边栏 ===== */
.cert-sidebar-card { background: #fff; border-radius: 8px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); margin-bottom: 14px; }
.cert-sidebar-card h3 { font-size: 15px; color: #1e3a8a; margin: 0 0 12px; }
.cert-sidebar-card ol, .cert-sidebar-card ul { padding-left: 18px; margin: 0; font-size: 13px; color: #555; line-height: 1.8; }
.cert-sidebar-card li { margin-bottom: 4px; }

/* ===== 打印样式 ===== */
@media print {
  body * { visibility: hidden; }
  #certificate-print, #certificate-print * { visibility: visible; }
  #certificate-print { position: absolute; left: 0; top: 0; width: 100%; }
  .cert-toolbar, .cert-toolbar-bottom, .cert-hint, #cert-breadcrumb-area { display: none; }
  .cert-page-title, .cert-page-desc, .cert-form, .cert-sidebar, .cert-footer, nav, .cert-result-header { display: none !important; }
  .certificate-page { padding: 0; max-width: none; }
  ._head, ._footer, .footer, .nav-links, .nav-actions { display: none !important; }
}

/* ===== 移动端适配 ===== */
@media (max-width: 768px) {
  .certificate-page { padding: 10px 8px; }
  .content-with-sidebar { flex-direction: column; }
  .cert-sidebar { width: 100%; margin-top: 20px; }

  /* 搜索框移动端 */
  .cert-search-box { padding: 12px 14px; }
  .cert-search-inner { flex-wrap: wrap; }
  .cert-search-inner input { width: 100%; font-size: 16px; }
  .cert-search-inner .btn-sm { width: 100%; margin-top: 6px; }
  .cert-hit-row { font-size: 13px; padding: 10px 12px; }
  .cert-hit-row strong { display: block; margin-bottom: 2px; }

  /* 表单移动端 */
  .cert-form { padding: 14px 12px; }
  .cert-form .form-row { flex-direction: column; gap: 0; }
  .cert-form .form-group input, .cert-form .form-group select { font-size: 16px; padding: 10px; }
  .cert-page-title { font-size: 20px; }
  .cert-page-desc { font-size: 13px; }

  /* 折叠区块标题 */
  .cert-legend { font-size: 14px; padding: 10px 14px; }
  .cert-body-inner { padding: 12px; }

  /* 父母栏 → 单列 */
  .parent-grid { grid-template-columns: 1fr; gap: 12px; }

  /* 祖辈栏 → 2列 */
  .grand-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
  .grand-col h4 { font-size: 11px; }
  .grand-col input { font-size: 14px; padding: 8px; }

  /* 按钮全宽 */
  .form-actions .btn-lg { width: 100%; padding: 14px; font-size: 16px; }

  /* 证书工具条移动端 */
  .cert-toolbar { display: flex; flex-direction: column; gap: 8px; }
  .cert-toolbar .btn { width: 100%; padding: 12px; font-size: 15px; }
  .cert-hint { font-size: 12px; }

  /* 证书结果头部 */
  .cert-result-header h1 { font-size: 20px; }

  /* 侧边栏卡片 */
  .cert-sidebar-card { padding: 14px; }
  .cert-sidebar-card h3 { font-size: 14px; }
  .cert-sidebar-card ol, .cert-sidebar-card ul { font-size: 12px; }
}

@media (max-width: 480px) {
  .cert-page-title { font-size: 18px; flex-wrap: wrap; }
  .cert-legend { font-size: 13px; padding: 8px 12px; }
  .cert-body-inner { padding: 10px; }
  .grand-grid { grid-template-columns: 1fr; gap: 8px; }
  .cert-toolbar .btn { font-size: 14px; padding: 10px; }
}
</style>
</head>
<body>
<?php include __DIR__ . '/_head.php'; ?>

<div class="certificate-page">

<?php if ($isGenerated): ?>
    <!-- ===== 已生成证书 ===== -->
    <div class="cert-result-header">
        <h1><i class="fas fa-certificate"></i> 血统证书</h1>
        <p>打印或另存为PDF即可保存</p>
    </div>
    <div class="cert-toolbar">
        <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print"></i> 打印 / 导出PDF</button>
        <button onclick="location.reload()" class="btn btn-outline"><i class="fas fa-redo"></i> 重新生成</button>
    </div>
    <div class="certificate-wrapper" id="certificate-print">
        <?php require __DIR__ . "/_certificate_template.php"; ?>
    </div>
    <div class="cert-toolbar cert-toolbar-bottom">
        <button onclick="window.print()" class="btn btn-primary btn-lg"><i class="fas fa-print"></i> 打印 / 导出PDF</button>
        <p class="cert-hint">💡 点击打印，在打印对话框中选择"另存为PDF"即可保存到本地</p>
    </div>

<?php else: ?>
    <!-- ===== 填写表单 ===== -->
    <h1 class="cert-page-title"><i class="fas fa-certificate"></i> 血统证书生成器</h1>
    <p class="cert-page-desc">输入<b>足环号</b>一键搜索数据库，自动填充鸽子信息。祖辈/育鸽者信息可展开补充。</p>

    <!-- 数据库搜索 -->
    <div class="cert-search-box">
        <div class="cert-search-inner">
            <i class="fas fa-search cert-search-icon"></i>
            <input type="text" id="cert-ring-search" placeholder="输入足环号搜索，如 CHN2024-01-1234567" autocomplete="off">
            <button type="button" id="cert-search-btn" class="btn btn-primary btn-sm">搜索</button>
        </div>
        <div class="cert-search-results" id="cert-search-results" style="display:none"></div>
        <div class="cert-search-status" id="cert-search-status"></div>
    </div>

    <form method="post" class="cert-form" id="cert-form">
        <input type="hidden" name="action" value="certificate">

        <!-- 本鸽信息（始终展开） -->
        <fieldset class="cert-fieldset cert-fieldset-open">
            <legend class="cert-legend"><i class="fas fa-dove"></i> 本鸽信息</legend>
            <div class="cert-body-inner">
                <div class="form-row">
                    <div class="form-group">
                        <label>足环号 <span class="required">*</span></label>
                        <input type="text" name="ring_number" required placeholder="如 CHN2024-01-1234567">
                    </div>
                    <div class="form-group">
                        <label>鸽名</label>
                        <input type="text" name="bird_name" placeholder="如 闪电号">
                    </div>
                </div>
                <div class="form-row form-row-3">
                    <div class="form-group">
                        <label>性别 <span class="required">*</span></label>
                        <select name="gender" required>
                            <option value="">请选择</option>
                            <option value="雄">雄</option>
                            <option value="雌">雌</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>羽色</label>
                        <input type="text" name="color" placeholder="如 雨点">
                    </div>
                    <div class="form-group">
                        <label>眼砂</label>
                        <input type="text" name="eye_color" placeholder="如 黄眼">
                    </div>
                </div>
                <div class="form-group">
                    <label>出生日期</label>
                    <input type="date" name="birth_date">
                </div>
            </div>
        </fieldset>

        <!-- 父母信息（可折叠） -->
        <fieldset class="cert-fieldset cert-collapsible">
            <legend class="cert-legend" onclick="toggleFieldset(this)">
                <i class="fas fa-venus-mars"></i> 父母信息 <i class="fas fa-chevron-down cert-arrow"></i>
            </legend>
            <div class="cert-body-inner" style="display:none">
                <div class="parent-grid">
                    <div class="parent-col">
                        <h4 class="parent-label father-label"><i class="fas fa-mars"></i> 父鸽</h4>
                        <div class="form-group"><label>足环号</label><input type="text" name="father_ring" placeholder="如 CHN2023-01-8888888"></div>
                        <div class="form-group"><label>鸽名</label><input type="text" name="father_name" placeholder="如 冠军号"></div>
                        <div class="form-group"><label>品系</label><input type="text" name="father_strain" placeholder="如 詹森"></div>
                        <div class="form-group"><label>主要赛绩</label><input type="text" name="father_achievements" placeholder="如 2024年决赛冠军"></div>
                    </div>
                    <div class="parent-col">
                        <h4 class="parent-label mother-label"><i class="fas fa-venus"></i> 母鸽</h4>
                        <div class="form-group"><label>足环号</label><input type="text" name="mother_ring" placeholder="如 CHN2023-01-9999999"></div>
                        <div class="form-group"><label>鸽名</label><input type="text" name="mother_name" placeholder="如 花木兰"></div>
                        <div class="form-group"><label>品系</label><input type="text" name="mother_strain" placeholder="如 盖比"></div>
                        <div class="form-group"><label>主要赛绩</label><input type="text" name="mother_achievements" placeholder="如 2024年决赛亚军"></div>
                    </div>
                </div>
            </div>
        </fieldset>

        <!-- 祖辈信息（可折叠） -->
        <fieldset class="cert-fieldset cert-collapsible">
            <legend class="cert-legend" onclick="toggleFieldset(this)">
                <i class="fas fa-sitemap"></i> 祖辈信息 <span class="optional-tag">选填</span> <i class="fas fa-chevron-down cert-arrow"></i>
            </legend>
            <div class="cert-body-inner" style="display:none">
                <div class="grand-grid">
                    <div class="grand-col">
                        <h4>父鸽之父</h4>
                        <input type="text" name="grand_fa_father_ring" placeholder="足环号">
                        <input type="text" name="grand_fa_father_name" placeholder="鸽名" style="margin-top:6px">
                    </div>
                    <div class="grand-col">
                        <h4>父鸽之母</h4>
                        <input type="text" name="grand_fa_mother_ring" placeholder="足环号">
                        <input type="text" name="grand_fa_mother_name" placeholder="鸽名" style="margin-top:6px">
                    </div>
                    <div class="grand-col">
                        <h4>母鸽之父</h4>
                        <input type="text" name="grand_mo_father_ring" placeholder="足环号">
                        <input type="text" name="grand_mo_father_name" placeholder="鸽名" style="margin-top:6px">
                    </div>
                    <div class="grand-col">
                        <h4>母鸽之母</h4>
                        <input type="text" name="grand_mo_mother_ring" placeholder="足环号">
                        <input type="text" name="grand_mo_mother_name" placeholder="鸽名" style="margin-top:6px">
                    </div>
                </div>
            </div>
        </fieldset>

        <!-- 育鸽者信息（可折叠） -->
        <fieldset class="cert-fieldset cert-collapsible">
            <legend class="cert-legend" onclick="toggleFieldset(this)">
                <i class="fas fa-user"></i> 育鸽者信息 <span class="optional-tag">选填</span> <i class="fas fa-chevron-down cert-arrow"></i>
            </legend>
            <div class="cert-body-inner" style="display:none">
                <div class="form-row form-row-3">
                    <div class="form-group"><label>姓名</label><input type="text" name="breeder_name" placeholder="育鸽者姓名"></div>
                    <div class="form-group"><label>鸽舍</label><input type="text" name="breeder_loft" placeholder="鸽舍名称"></div>
                    <div class="form-group"><label>电话</label><input type="text" name="breeder_phone" placeholder="联系电话"></div>
                </div>
            </div>
        </fieldset>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-certificate"></i> 生成血统证书</button>
        </div>
    </form>

    <!-- 侧边栏 -->
    <div class="cert-sidebar" style="margin-top:20px">
        <div class="cert-sidebar-card">
            <h3><i class="fas fa-lightbulb"></i> 快速上手</h3>
            <ol>
                <li>填<b>足环号</b>和<b>性别</b>（仅此必填）</li>
                <li>展开父母/祖辈卡片补充信息</li>
                <li>点击生成 → 打印 → 另存为PDF</li>
            </ol>
        </div>
        <div class="cert-sidebar-card">
            <h3><i class="fas fa-crown"></i> 证书价值</h3>
            <ul>
                <li><b>卖鸽必备</b> — 提升买家信任度</li>
                <li><b>血统记录</b> — 科学育种不盲目</li>
                <li><b>身份象征</b> — 铭鸽身价凭证</li>
            </ul>
        </div>
    </div>
<?php endif; ?>

</div>

<?php include __DIR__ . '/_footer.php'; ?>

<script>
function toggleFieldset(legend) {
  var fs = legend.parentElement;
  var body = fs.querySelector(".cert-body-inner");
  var arrow = legend.querySelector(".cert-arrow");
  if (!body) return;
  if (body.style.display === "none") {
    body.style.display = "block";
    if (arrow) { arrow.classList.remove("fa-chevron-down"); arrow.classList.add("fa-chevron-up"); }
  } else {
    body.style.display = "none";
    if (arrow) { arrow.classList.remove("fa-chevron-up"); arrow.classList.add("fa-chevron-down"); }
  }
}

// ---- 足环号搜索自动填充 ----
(function() {
  var searchInput = document.getElementById('cert-ring-search');
  var searchBtn = document.getElementById('cert-search-btn');
  var resultsBox = document.getElementById('cert-search-results');
  var statusBox = document.getElementById('cert-search-status');

  function setFormValue(name, value) {
    var el = document.getElementsByName(name)[0];
    if (el) {
      if (el.tagName === 'SELECT') {
        for (var i = 0; i < el.options.length; i++) {
          if (el.options[i].value === value) { el.value = value; return; }
        }
      } else {
        el.value = value || '';
      }
    }
  }

  function autoFillForm(data) {
    if (typeof data === 'string') data = JSON.parse(data);
    setFormValue('ring_number', data.ring_number);
    setFormValue('bird_name', data.bird_name);
    setFormValue('gender', data.gender);
    setFormValue('color', data.color);
    setFormValue('eye_color', data.eye_color);
    setFormValue('birth_date', data.birth_date);

    if (data.father) {
      setFormValue('father_ring', data.father.ring_number);
      setFormValue('father_name', data.father.name);
      setFormValue('father_strain', data.father.bloodline);
    }
    if (data.mother) {
      setFormValue('mother_ring', data.mother.ring_number);
      setFormValue('mother_name', data.mother.name);
      setFormValue('mother_strain', data.mother.bloodline);
    }

    if (data.father || data.mother) {
      var parentFs = document.querySelector('.cert-collapsible:nth-of-type(1)');
      if (parentFs) {
        var body = parentFs.querySelector('.cert-body-inner');
        var arrow = parentFs.querySelector('.cert-arrow');
        if (body && body.style.display === 'none') {
          body.style.display = 'block';
          if (arrow) { arrow.classList.remove('fa-chevron-down'); arrow.classList.add('fa-chevron-up'); }
        }
      }
    }

    if (data.owner_name) setFormValue('breeder_name', data.owner_name);
    if (data.region) setFormValue('breeder_address', data.region);

    var form = document.getElementById('cert-form');
    if (form) { form.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
  }

  function showStatus(msg, type) {
    statusBox.innerHTML = '<span class="cert-status-' + type + '">' + msg + '</span>';
    resultsBox.style.display = 'none';
  }

  function search() {
    var ring = searchInput.value.trim();
    if (!ring) { showStatus('请输入足环号', 'error'); return; }
    showStatus('搜索中...', 'loading');
    searchBtn.disabled = true;

    fetch('/pedigree/?action=certificate_search_pigeon&ring=' + encodeURIComponent(ring))
      .then(function(r) { return r.json(); })
      .then(function(res) {
        searchBtn.disabled = false;
        if (!res.success) { showStatus(res.message || '未找到', 'error'); return; }
        var d = res.data;
        var isRace = d.source === 'race_results';
        var html = '<div class="cert-search-hit" onclick="document.getElementById(\'cert-search-results\').style.display=\'none\'">';
        html += '<div class="cert-hit-row" style="cursor:pointer" onclick="event.stopPropagation();autoFillForm(' + JSON.stringify(d).replace(/"/g, '&quot;') + ');document.getElementById(\'cert-search-results\').style.display=\'none\';document.getElementById(\'cert-search-status\').innerHTML=\'<span class=cert-status-ok>✅ 已自动填充 ' + (d.bird_name || d.ring_number) + (d.father || d.owner_name ? '（含附加信息）' : '') + '</span>\'">';
        html += '<strong>' + (d.bird_name || d.ring_number) + '</strong>';
        if (isRace) {
          html += ' <span class="cert-hit-tag cert-hit-tag-race">赛事数据库</span>';
          html += ' <span style="color:#666;font-size:13px">' + [d.color, '参赛' + d.race_count + '场', d.owner_name ? '鸽主:' + d.owner_name : ''].filter(Boolean).join(' · ') + '</span>';
        } else {
          html += ' <span style="color:#666;font-size:13px">' + [d.gender, d.bloodline, d.color, d.eye_color].filter(Boolean).join(' · ') + '</span>';
          if (d.father || d.mother) html += ' <span class="cert-hit-tag">含父母</span>';
        }
        html += '<span style="margin-left:auto;font-size:12px;color:#1a5fa8" class="cert-fill-hint">点击填充 →</span>';
        html += '</div></div>';
        resultsBox.innerHTML = html;
        resultsBox.style.display = 'block';
        statusBox.innerHTML = '';
      })
      .catch(function() {
        searchBtn.disabled = false;
        showStatus('网络错误，请重试', 'error');
      });
  }

  searchBtn.addEventListener('click', search);
  searchInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); search(); }
  });
  document.addEventListener('click', function(e) {
    if (!e.target.closest('.cert-search-box')) { resultsBox.style.display = 'none'; }
  });
  window.autoFillForm = autoFillForm;
})();


</script>
</body>
</html>