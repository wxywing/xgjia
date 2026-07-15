<?php
/**
 * 公棚对比工具 v2
 * URL: /loft/compare/
 * - 前端JS管理选择状态（秒级响应）
 * - 搜索/选择分离：搜索走服务端，选择走客户端
 */
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? '公棚对比工具 - 信鸽之家'; ?></title>
    <meta name="description" content="横向对比全国赛鸽公棚，多维度数据（参赛费、奖金、规模、赛事成绩、鸽友评价）一目了然，帮您做出最佳决策。">
    <meta name="keywords" content="公棚对比,赛鸽公棚,公棚查询,公棚数据,信鸽之家">
    <meta property="og:title" content="<?php echo h($pageTitle ?? '公棚对比工具 - 信鸽之家'); ?>">
    <meta property="og:description" content="横向对比全国赛鸽公棚，多维度数据一目了然，帮您做出最佳决策。">
    <meta property="og:image" content="https://www.xgjia.com/public/images/og-cover.png">
    <link rel="canonical" href="https://www.xgjia.com/loft/compare/">
    <link rel="stylesheet" href="/public/css/b-scheme.css">
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="shortcut icon" href="/public/images/favicon.ico">
    <style>
        .compare-hero { background: linear-gradient(135deg, #1a5fa8 0%, #0d3b6e 100%); padding: 40px 0 32px; text-align: center; color: #fff; }
        .compare-hero h1 { font-size: 26px; margin: 0 0 8px; font-weight: 700; }
        .compare-hero p { font-size: 14px; color: rgba(255,255,255,0.65); margin: 0; }
        .compare-search { max-width: 600px; margin: 20px auto 0; position: relative; }
        .compare-search input { width: 100%; padding: 12px 48px 12px 16px; border: 2px solid rgba(255,255,255,0.2); border-radius: 8px; font-size: 14px; background: rgba(255,255,255,0.1); color: #fff; outline: none; transition: border-color .2s; }
        .compare-search input::placeholder { color: rgba(255,255,255,0.4); }
        .compare-search input:focus { border-color: rgba(255,255,255,0.5); }
        .compare-search button { position: absolute; right: 4px; top: 4px; bottom: 4px; padding: 0 16px; background: #c9a84c; color: #fff; border: none; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; }
        .compare-search button:hover { background: #b8943f; }

        .container { max-width: 1100px; margin: 0 auto; padding: 0 16px; }
        .compare-section { padding: 28px 0; }

        /* selected bar */
        .selected-bar { background: #f0f4f8; padding: 14px 0; border-bottom: 1px solid #e0e5ea; }
        .selected-bar .container { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
        .selected-label { font-size: 13px; color: #666; font-weight: 600; white-space: nowrap; }
        .selected-tag { display: inline-flex; align-items: center; gap: 6px; background: #1a5fa8; color: #fff; padding: 6px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; }
        .selected-tag .remove-tag { cursor: pointer; opacity: 0.7; font-size: 11px; color: #fff; text-decoration: none; }
        .selected-tag .remove-tag:hover { opacity: 1; }
        .compare-btn { padding: 8px 24px; background: #c9a84c; color: #fff; border: none; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block; }
        .compare-btn:disabled { background: #bbb; cursor: not-allowed; }
        .compare-btn:hover:not(:disabled) { background: #b8943f; }
        .selected-hint { font-size: 12px; color: #999; }

        /* search results - LIGHT background, DARK text */
        .search-results { max-width: 650px; margin: 0 auto; }
        .search-results-title { font-size: 14px; color: #666; margin-bottom: 10px; }
        .search-result-item { display: flex; align-items: center; padding: 12px 16px; background: #fff; border: 1px solid #e0e5ea; border-radius: 8px; margin-bottom: 8px; transition: all .15s; cursor: default; }
        .search-result-item:hover { border-color: #1a5fa8; box-shadow: 0 2px 8px rgba(26,95,168,0.08); }
        .sr-info { flex: 1; min-width: 0; }
        .sr-name { font-size: 15px; font-weight: 600; color: #2c3e50; }
        .sr-name a { color: #1a5fa8; text-decoration: none; }
        .sr-name a:hover { text-decoration: underline; }
        .sr-meta { font-size: 12px; color: #888; margin-top: 3px; }
        .sr-add { width: 30px; height: 30px; border-radius: 50%; background: #1a5fa8; color: #fff; border: none; font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-left: 12px; line-height: 1; transition: all .2s; }
        .sr-add:hover { background: #154360; transform: scale(1.05); }
        .sr-add.selected { background: #27ae60; cursor: pointer; }
        .sr-add.selected:hover { background: #e74c3c; }
        .sr-add:disabled { background: #ccc; cursor: not-allowed; }

        /* comparison grid */
        #compare-result { scroll-margin-top: 80px; }
        .compare-grid { display: grid; grid-template-columns: 180px repeat(var(--cols, 3), 1fr); gap: 1px; background: #e0e5ea; border: 1px solid #e0e5ea; border-radius: 8px; overflow: hidden; margin: 20px 0; }
        .compare-grid .col-header { background: #fff; padding: 18px 14px; text-align: center; }
        .compare-grid .col-header:first-child { background: #f8f9fb; text-align: right; font-size: 12px; color: #888; font-weight: 600; padding-right: 16px; display: flex; align-items: center; justify-content: flex-end; }
        .col-header .loft-name { font-size: 16px; font-weight: 700; color: #1a5fa8; margin-bottom: 4px; }
        .col-header .loft-name a { color: #1a5fa8; text-decoration: none; }
        .col-header .loft-name a:hover { text-decoration: underline; }
        .col-header .loft-loc { font-size: 12px; color: #888; }
        .compare-row { display: contents; }
        .compare-cell { background: #fff; padding: 12px 14px; font-size: 13px; text-align: center; color: #2c3e50; }
        .compare-cell.label { background: #f8f9fb; text-align: right; font-weight: 600; color: #555; padding-right: 16px; font-size: 12px; }
        .compare-cell.best { font-weight: 700; color: #27ae60; }
        .cell-highlight { background: #f0faf3; }

        .empty-state { text-align: center; padding: 40px 20px 60px; color: #888; }
        .empty-state i { font-size: 48px; margin-bottom: 16px; color: #ddd; }
        .empty-state h3 { font-size: 18px; margin-bottom: 8px; color: #555; }
        .empty-state p { font-size: 14px; max-width: 480px; margin: 0 auto; line-height: 1.6; }

        @media (max-width: 768px) {
            .compare-grid { grid-template-columns: 1fr; }
            .compare-grid .col-header:first-child { display: none; }
            .compare-row { display: block; margin-bottom: 12px; }
            .compare-cell.label { text-align: left; background: #eef2f7; padding: 6px 14px; font-size: 11px; }
            .compare-cell { padding: 8px 14px; font-size: 13px; }
            .compare-hero { padding: 28px 0 24px; }
            .compare-hero h1 { font-size: 20px; }
        }

        /* ===== 打印样式 ===== */
        @media print {
            body { background: #fff; font-size: 12px; }
            .compare-hero, .selected-bar, #searchForm, .search-results,
            .empty-state, .compare-btn, .reset-btn, .selected-hint,
            footer, header, nav, .breadcrumb, .paywall-card,
            .paywall-section, .print-hide, #compareBtn, .paywall-box,
            .btn, .top-bar, .site-nav { display: none !important; }

            .print-header { display: block !important; }
            .compare-section { padding: 10px 0; page-break-inside: avoid; }
            .container { max-width: 100%; padding: 0; margin: 0; }
            #compare-result h2 { font-size: 16px; margin-bottom: 2px; }
            #compare-result > .container > p { font-size: 11px; margin-bottom: 10px; }

            .compare-grid {
                display: grid !important;
                grid-template-columns: 120px repeat(var(--cols, 2), 1fr) !important;
                gap: 1px; background: #ccc; border: 1px solid #ccc; font-size: 11px;
            }
            .compare-grid .col-header:first-child { display: flex !important; background: #f0f4f8; font-weight: 700; }
            .compare-cell.label {
                background: #f5f7fa !important; font-size: 11px;
                text-align: right !important; font-weight: 600; color: #555;
            }
            .compare-cell { padding: 6px 10px; font-size: 11px; background: #fff; }
            .compare-cell.best { color: #27ae60; font-weight: 700; }
            .loft-name a { font-size: 13px; font-weight: 700; color: #1a1a1a; text-decoration: none; }
            .loft-loc { font-size: 10px; color: #888; }
            .col-header:not(:first-child) { background: #f8fafc; text-align: center; }

            .print-footer { display: block !important; font-size: 10px; color: #aaa; text-align: center; padding-top: 10px; border-top: 1px solid #eee; margin-top: 10px; }

            @page { margin: 15mm 12mm; size: A4 landscape; }
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/_head.php'; ?>

<!-- Hero Search -->
<section class="compare-hero">
  <div class="container">
    <h1>🏠 公棚对比工具</h1>
    <p>最多选择 3 家公棚，横向对比参赛费、奖金、规模、成绩数据</p>
    <form class="compare-search" method="get" action="/loft/compare/" id="searchForm">
      <input type="text" name="q" placeholder="输入公棚名称搜索..." value="<?php echo htmlspecialchars($query ?? ''); ?>" autocomplete="off">
      <input type="hidden" name="ids" id="idsHidden" value="<?php echo htmlspecialchars(implode(',', $ids ?? [])); ?>">
      <button type="submit"><i class="fas fa-search"></i> 搜索</button>
    </form>
  </div>
</section>

<?php
$lofts = $lofts ?? [];
$searchResults = $searchResults ?? [];
$query = $query ?? '';
$hasComparison = !empty($lofts);
$unlocked = $unlocked ?? true;
?>

<!-- Selected Bar (client-side) -->
<section class="selected-bar" id="selectedBar" style="display:<?php echo $hasComparison ? 'block' : 'none'; ?>;">
  <div class="container">
    <span class="selected-label">已选择：</span>
    <span id="selectedTags">
      <?php if ($hasComparison): ?>
        <?php foreach ($lofts as $loft): ?>
        <span class="selected-tag" data-lid="<?php echo $loft['id']; ?>">
          <?php echo htmlspecialchars($loft['name']); ?>
          <span class="remove-tag" onclick="removeLoft(<?php echo $loft['id']; ?>)">✕</span>
        </span>
        <?php endforeach; ?>
      <?php endif; ?>
    </span>
    <button class="compare-btn" id="compareBtn" onclick="doCompare()" <?php echo count($lofts ?? []) < 2 ? 'disabled' : ''; ?>>
      <i class="fas fa-balance-scale"></i> 开始对比
    </button>
    <?php if ($hasComparison || !empty($ids)): ?>
    <button class="reset-btn" onclick="resetCompare()" style="margin-left:8px;background:#fff;color:#e74c3c;border:1px solid #e74c3c;padding:8px 16px;border-radius:6px;cursor:pointer;font-size:14px;transition:all 0.2s;" onmouseover="this.style.background='#fee'" onmouseout="this.style.background='#fff'">
      <i class="fas fa-undo"></i> 重置对比
    </button>
    <?php endif; ?>
    <span class="selected-hint" id="selectedHint">（至少选择 2 家公棚开始对比）</span>
  </div>
</section>

<!-- Search Results (only when searching) -->
<?php if (!empty($searchResults)): ?>
<section class="compare-section">
  <div class="container">
    <div class="search-results">
      <div class="search-results-title">
        搜索「<strong><?php echo htmlspecialchars($query); ?></strong>」找到 <?php echo count($searchResults); ?> 个公棚
      </div>
      <?php foreach ($searchResults as $sr): 
        $lid = $sr['id'];
        $isSelected = $hasComparison && in_array($lid, array_column($lofts, 'id'));
        $canAdd = (count($lofts ?? []) < 3) || $isSelected;
      ?>
      <div class="search-result-item" id="result-<?php echo $lid; ?>">
        <div class="sr-info">
          <div class="sr-name">
            <a href="/loft/<?php echo $lid; ?>.html" target="_blank"><?php echo htmlspecialchars($sr['name']); ?></a>
          </div>
          <div class="sr-meta">
            <?php echo htmlspecialchars($sr['province'] . ' ' . $sr['city']); ?> 
            · <?php echo htmlspecialchars($sr['race_type'] ?? ''); ?> 
            <?php if ($sr['entry_fee'] > 0): ?>· 参赛费 ¥<?php echo number_format($sr['entry_fee']); endif; ?>
            <?php if ($sr['prize_pool'] > 0): ?>· 奖金 ¥<?php echo number_format($sr['prize_pool']/10000, 0); ?>万<?php endif; ?>
            <?php if ($sr['capacity'] > 0): ?>· <?php echo number_format($sr['capacity']); ?>羽<?php endif; ?>
          </div>
        </div>
        <button class="sr-add <?php echo $isSelected ? 'selected' : ''; ?>" 
                id="add-<?php echo $lid; ?>"
                data-id="<?php echo $lid; ?>"
                data-name="<?php echo htmlspecialchars($sr['name']); ?>"
                data-province="<?php echo htmlspecialchars($sr['province']); ?>"
                onclick="toggleLoft(this, <?php echo $lid; ?>, '<?php echo htmlspecialchars(addslashes($sr['name'])); ?>')"
                <?php echo !$canAdd ? 'disabled' : ''; ?>>
          <?php echo $isSelected ? '✓' : '+'; ?>
        </button>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- Comparison Table -->
<?php if ($hasComparison): 
  $cols = min(count($lofts), 3);
  
  $bestValue = function($vals, $higherBetter = true) {
    $filtered = array_filter($vals, function($v) { return $v !== null && $v !== '' && $v > 0; });
    if (empty($filtered)) return null;
    return $higherBetter ? max($filtered) : min($filtered);
  };
  
  $rows = [
    ['label' => '省份', 'key' => 'province', 'type' => 'text'],
    ['label' => '城市', 'key' => 'city', 'type' => 'text'],
    ['label' => '类型', 'key' => 'race_type', 'type' => 'text'],
    ['label' => '可容纳', 'key' => 'capacity', 'type' => 'number', 'unit' => ' 羽', 'higherBetter' => true],
    ['label' => '当前存棚', 'key' => 'current_count', 'type' => 'number', 'unit' => ' 羽', 'higherBetter' => true],
    ['label' => '参赛费', 'key' => 'entry_fee', 'type' => 'money', 'higherBetter' => false],
    ['label' => '管理费', 'key' => 'management_fee', 'type' => 'money', 'higherBetter' => false],
    ['label' => '总奖金池', 'key' => 'prize_pool', 'type' => 'moneyBig', 'higherBetter' => true],
    ['label' => '赛事场次', 'key' => 'race_count', 'type' => 'number', 'unit' => ' 场', 'higherBetter' => true],
    ['label' => '成绩记录', 'key' => 'result_count', 'type' => 'bigNumber', 'higherBetter' => true],
    ['label' => '历史最高分速', 'key' => 'best_speed', 'type' => 'speed', 'higherBetter' => true],
    ['label' => '平均分速', 'key' => 'avg_speed', 'type' => 'speed', 'higherBetter' => true],
    ['label' => '评分', 'key' => 'rating', 'type' => 'stars', 'higherBetter' => true],
    ['label' => '浏览量', 'key' => 'views', 'type' => 'number', 'unit' => ' 次', 'higherBetter' => true],
    ['label' => '地址', 'key' => 'address', 'type' => 'text'],
    ['label' => '电话', 'key' => 'contact_phone', 'type' => 'text'],
  ];

  $highlights = [];
  foreach ($rows as $row) {
    if ($row['type'] === 'text' || $row['type'] === 'link') continue;
    $vals = array_column($lofts, $row['key']);
    $highlights[$row['key']] = $bestValue($vals, $row['higherBetter'] ?? true);
  }
?>

<section class="compare-section" id="compare-result">
  <div class="container">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px;">
      <h2 style="font-size:18px;font-weight:700;color:#1a5fa8;margin:0;">📊 对比结果</h2>
      <button class="print-btn" onclick="window.print()" style="
        background:#fff;border:1px solid #1a5fa8;color:#1a5fa8;
        padding:6px 16px;border-radius:6px;font-size:13px;font-weight:600;
        cursor:pointer;display:inline-flex;align-items:center;gap:6px;
        transition:all 0.2s;
      " onmouseover="this.style.background='#1a5fa8';this.style.color='#fff'"
         onmouseout="this.style.background='#fff';this.style.color='#1a5fa8'">
        <i class="fas fa-print"></i> 打印对比单
      </button>
    </div>
    <p style="font-size:13px;color:#888;margin-bottom:16px;">绿色加粗 = 该项最优 · 点击「打印对比单」可导出 PDF</p>

    <!-- 打印头部（仅打印时显示） -->
    <div class="print-header" style="display:none;margin-bottom:16px;border-bottom:2px solid #1a5fa8;padding-bottom:10px;">
      <div style="font-size:20px;font-weight:900;color:#1a5fa8;">🏠 公棚对比报告</div>
      <div style="font-size:12px;color:#888;margin-top:4px;">信鸽之家 xgjia.com · <?php echo date('Y年m月d日 H:i'); ?> 生成</div>
    </div>
    
    <div class="compare-grid" style="--cols:<?php echo $cols; ?>">
      <div class="col-header">项目</div>
      <?php foreach ($lofts as $loft): ?>
      <div class="col-header">
        <div class="loft-name">
          <a href="/loft/<?php echo $loft['id']; ?>.html" target="_blank"><?php echo htmlspecialchars($loft['name']); ?></a>
        </div>
        <div class="loft-loc"><?php echo htmlspecialchars($loft['province']); ?></div>
      </div>
      <?php endforeach; ?>

      <?php foreach ($rows as $row): ?>
      <div class="compare-row">
        <div class="compare-cell label"><?php echo $row['label']; ?></div>
        <?php foreach ($lofts as $loft): 
          $val = $loft[$row['key']] ?? '';
          $isBest = false;
          if (isset($highlights[$row['key']]) && $val !== null && $val !== '' && $val > 0) {
            $isBest = ($highlights[$row['key']] === $val);
          }
          $display = '';
          switch ($row['type']) {
            case 'money': $display = $val > 0 ? '¥' . number_format($val) : '—'; break;
            case 'moneyBig': $display = $val > 0 ? '¥' . number_format($val/10000, 0) . ' 万' : '—'; break;
            case 'number': case 'bigNumber': $display = $val > 0 ? number_format($val) . ($row['unit'] ?? '') : '—'; break;
            case 'speed': $display = $val > 0 ? number_format($val, 1) . ' m/min' : '—'; break;
            case 'stars': $display = $val > 0 ? str_repeat('⭐', intval($val)) . ' ' . $val : '—'; break;
            case 'link': $display = $val ? '<a href="' . htmlspecialchars($val) . '" target="_blank" rel="nofollow" style="font-size:12px;">访问 <i class="fas fa-external-link-alt"></i></a>' : '—'; break;
            default: $display = $val ? htmlspecialchars($val) : '—';
          }
          $cellClass = 'compare-cell';
          if ($isBest) $cellClass .= ' best cell-highlight';
        ?>
        <div class="<?php echo $cellClass; ?>"><?php echo $display; ?></div>
        <?php endforeach; ?>
      </div>
      <?php endforeach; ?>

      <div class="compare-row">
        <div class="compare-cell label">简介</div>
        <?php foreach ($lofts as $loft): ?>
        <div class="compare-cell" style="font-size:12px;text-align:left;line-height:1.5;">
          <?php echo $loft['description'] ? htmlspecialchars(mb_substr($loft['description'], 0, 200)) . (mb_strlen($loft['description']) > 200 ? '...' : '') : '暂无简介'; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- 打印页脚（仅打印时显示） -->
    <div class="print-footer" style="display:none;margin-top:12px;padding-top:10px;border-top:1px solid #eee;text-align:center;">
      <span style="color:#1a5fa8;font-weight:700;">信鸽之家 xgjia.com</span>
      <span style="color:#aaa;margin:0 8px;">|</span>
      <span style="color:#888;">公棚数据对比 · 赛事决策参考</span>
      <span style="color:#aaa;margin:0 8px;">|</span>
      <span style="color:#bbb;font-size:10px;">数据更新于 <?php echo date('Y-m'); ?></span>
    </div>
  </div>

  <?php if (!$unlocked && count($ids ?? []) > 2): ?>
  <!-- 付费解锁卡（3公棚对比） -->
  <div class="container" style="margin-top:28px;">
    <div style="background:linear-gradient(135deg,#f8f9fb,#e8f2fc);border:1px dashed #1a5fa8;border-radius:12px;padding:36px 24px;text-align:center;max-width:480px;margin:0 auto;">
      <div style="font-size:36px;margin-bottom:10px;">🔒</div>
      <h3 style="font-size:20px;color:#333;margin-bottom:8px;">3 公棚对比需解锁</h3>
      <p style="color:#888;font-size:14px;margin-bottom:6px;">免费对比 ≤ 2 家 · 解锁后可对比 3 家</p>
      <div style="margin:18px 0;padding:16px;background:#fff;border-radius:10px;border:1px solid #e8ecf0;">
        <div style="font-size:13px;color:#888;margin-bottom:4px;">解锁费用</div>
        <div style="font-size:42px;font-weight:900;color:#1a5fa8;line-height:1;"><span style="font-size:22px;">¥</span>19<span style="font-size:16px;">.9</span></div>
        <div style="font-size:12px;color:#999;margin-top:4px;">一次购买，该组对比永久可查</div>
      </div>
      <button onclick="payCompare()" style="width:100%;max-width:300px;padding:14px;background:#1a5fa8;color:#fff;border:none;border-radius:8px;font-size:16px;font-weight:700;cursor:pointer;">
        <i class="fas fa-lock-open" style="margin-right:6px;"></i> 立即解锁 ¥19.9
      </button>
      <p style="font-size:12px;color:#bbb;margin-top:12px;">微信 / 支付宝 均可支付</p>
    </div>
  </div>
  <?php endif; ?>
</section>

<?php elseif (!empty($query)): ?>
<section class="compare-section">
  <div class="container">
    <div class="empty-state">
      <i class="fas fa-search"></i>
      <h3>未找到匹配的公棚</h3>
      <p>试试其他关键词，或浏览 <a href="/loft/" style="color:#1a5fa8;">公棚列表</a></p>
    </div>
  </div>
</section>
<?php else: ?>
<section class="compare-section">
  <div class="container">
    <div class="empty-state">
      <i class="fas fa-balance-scale"></i>
      <h3>选择要对比的公棚</h3>
      <p>在上方搜索框输入公棚名称（如"开尔""翔友""神州辉煌"），<br>选中 2~3 家公棚即可开始对比</p>
      <div style="margin-top:20px;">
        <a href="/loft/" class="compare-btn" style="font-size:14px;">浏览全部公棚</a>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (!empty($hotLofts)): ?>
<!-- 🔥 热门公棚 -->
<section class="compare-section" style="background:#f8fafc;padding:28px 0;">
  <div class="container">
    <div class="search-results-title" style="margin-bottom:12px;">🔥 热门公棚（点击 + 加入对比）</div>
    <div class="search-results">
      <?php foreach ($hotLofts as $sr): 
        $lid = $sr['id'];
        $isSelected = $hasComparison && in_array($lid, array_column($lofts, 'id'));
        $canAdd = (count($lofts ?? []) < 3) || $isSelected;
      ?>
      <div class="search-result-item" id="hot-<?php echo $lid; ?>">
        <div class="sr-info">
          <div class="sr-name">
            <a href="/loft/<?php echo $lid; ?>.html" target="_blank"><?php echo htmlspecialchars($sr['name']); ?></a>
          </div>
          <div class="sr-meta">
            <?php echo htmlspecialchars(($sr['province'] ?? '') . ' ' . ($sr['city'] ?? '')); ?> 
            · <?php echo htmlspecialchars($sr['race_type'] ?? ''); ?> 
            <?php if (($sr['entry_fee'] ?? 0) > 0): ?>· 参赛费 ¥<?php echo number_format($sr['entry_fee']); endif; ?>
            <?php if (($sr['prize_pool'] ?? 0) > 0): ?>· 奖金 ¥<?php echo number_format($sr['prize_pool']/10000, 0); ?>万<?php endif; ?>
            <?php if (($sr['capacity'] ?? 0) > 0): ?>· <?php echo number_format($sr['capacity']); ?>羽<?php endif; ?>
          </div>
        </div>
        <button class="sr-add <?php echo $isSelected ? 'selected' : ''; ?>" 
                id="hot-add-<?php echo $lid; ?>"
                data-id="<?php echo $lid; ?>"
                data-name="<?php echo htmlspecialchars($sr['name']); ?>"
                data-province="<?php echo htmlspecialchars($sr['province'] ?? ''); ?>"
                onclick="toggleLoft(this, <?php echo $lid; ?>, '<?php echo htmlspecialchars(addslashes($sr['name'])); ?>')"
                <?php echo !$canAdd ? 'disabled' : ''; ?>>
          <?php echo $isSelected ? '✓' : '+'; ?>
        </button>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/_footer.php'; ?>

<script>
// Client-side selection management
var selectedLofts = [];

<?php if ($hasComparison): ?>
// Pre-populate from comparison data
selectedLofts = <?php echo json_encode(array_map(function($l) { return ['id' => (int)$l['id'], 'name' => $l['name']]; }, $lofts ?? []), JSON_UNESCAPED_UNICODE); ?>;
<?php endif; ?>

function updateUI() {
    var bar = document.getElementById('selectedBar');
    var tags = document.getElementById('selectedTags');
    var btn = document.getElementById('compareBtn');
    var hint = document.getElementById('selectedHint');
    var idsInput = document.getElementById('idsHidden');
    
    if (selectedLofts.length === 0) {
        bar.style.display = 'none';
    } else {
        bar.style.display = 'block';
        tags.innerHTML = selectedLofts.map(function(l) {
            return '<span class="selected-tag" data-lid="' + l.id + '">' +
                   l.name +
                   '<span class="remove-tag" onclick="removeLoft(' + l.id + ')" style="cursor:pointer;"> ✕</span>' +
                   '</span>';
        }).join('');
        
        if (selectedLofts.length >= 2) {
            btn.disabled = false;
            hint.style.display = 'none';
        } else {
            btn.disabled = true;
            hint.style.display = 'inline';
        }
    }
    
    // Update hidden input
    idsInput.value = selectedLofts.map(function(l) { return l.id; }).join(',');
    
    // Update all sr-add buttons state
    var allBtns = document.querySelectorAll('.sr-add');
    var full = selectedLofts.length >= 3;
    allBtns.forEach(function(btn) {
        var lid = parseInt(btn.getAttribute('data-id'));
        var isSelected = selectedLofts.some(function(l) { return l.id === lid; });
        
        if (isSelected) {
            btn.classList.add('selected');
            btn.textContent = '✓';
            btn.disabled = false;
        } else {
            btn.classList.remove('selected');
            btn.textContent = '+';
            btn.disabled = full;
        }
    });
}

function toggleLoft(btn, id, name) {
    var idx = selectedLofts.findIndex(function(l) { return l.id === id; });
    
    if (idx >= 0) {
        // Remove
        selectedLofts.splice(idx, 1);
    } else {
        // Add
        if (selectedLofts.length >= 3) return;
        selectedLofts.push({ id: id, name: name });
    }
    
    updateUI();
}

function removeLoft(id) {
    selectedLofts = selectedLofts.filter(function(l) { return l.id !== id; });
    updateUI();
}

function doCompare() {
    if (selectedLofts.length < 2) return;
    var ids = selectedLofts.map(function(l) { return l.id; }).join(',');
    window.location.href = '/loft/compare/?ids=' + ids;
}

function payCompare() {
    var ids = selectedLofts.map(function(l) { return l.id; }).join(',');
    if (!ids) return;
    <?php if (empty($_SESSION['user_id'])): ?>
    alert('请先登录后再支付');
    window.location.href = '/user/login.php';
    return;
    <?php endif; ?>
    var payBtn = document.querySelector('[onclick="payCompare()"]');
    payBtn.disabled = true;
    payBtn.textContent = '创建订单中...';

    fetch('/user/payment/create', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'product_type=compare&loft_ids=' + encodeURIComponent(ids) + '&pay_method=wechat'
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            if (data.unlocked) {
                location.reload();
            } else if (data.pending) {
                alert('订单已创建（订单号：' + data.order_no + '），请等待管理员审核。审核通过后将自动解锁。');
            }
        } else {
            alert('支付失败：' + (data.message || '请重试'));
        }
    })
    .catch(function(e) {
        alert('网络错误，请重试');
    })
    .finally(function() {
        payBtn.disabled = false;
        payBtn.innerHTML = '<i class="fas fa-lock-open" style="margin-right:6px;"></i> 立即解锁 ¥19.9';
    });
}

function resetCompare() {
    window.location.href = '/loft/compare/';
}

// Init
updateUI();

// Auto-scroll to comparison
<?php if ($hasComparison): ?>
(function() {
    var el = document.getElementById('compare-result');
    if (el) {
        setTimeout(function() { el.scrollIntoView({ behavior: 'smooth', block: 'start' }); }, 200);
    }
})();
<?php endif; ?>
</script>

</body>
</html>
