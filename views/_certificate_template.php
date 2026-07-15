<?php
/**
 * 血统证书 - 打印模板（移动端适配版）
 * 设计为 A4 纸打印 + 移动端友好展示
 */
$c = $cert; // shorthand
$certNo = "XGJIA-" . date("Ymd") . str_pad($c["cert_id"] ?? random_int(1000, 9999), 4, "0", STR_PAD_LEFT);
?>
<div class="cert-body">
  <!-- 顶部装饰 -->
  <div class="cert-header">
    <div class="cert-header-line"></div>
    <div class="cert-title-area">
      <div class="cert-sub"><?php echo SITE_NAME; ?></div>
      <h1 class="cert-main-title">血 统 证 书</h1>
      <div class="cert-sub-en">PEDIGREE CERTIFICATE</div>
    </div>
    <div class="cert-header-line"></div>
  </div>

  <!-- 本鸽信息 -->
  <table class="cert-info-table">
    <tr>
      <td class="cert-label">足环号</td>
      <td class="cert-value cert-ring"><?php echo htmlspecialchars($c["ring_number"]); ?></td>
      <td class="cert-label">鸽名</td>
      <td class="cert-value"><?php echo htmlspecialchars($c["bird_name"] ?: "—"); ?></td>
    </tr>
    <tr>
      <td class="cert-label">性别</td>
      <td class="cert-value"><?php echo htmlspecialchars($c["gender"] ?: "—"); ?></td>
      <td class="cert-label">羽色</td>
      <td class="cert-value"><?php echo htmlspecialchars($c["color"] ?: "—"); ?></td>
    </tr>
    <tr>
      <td class="cert-label">眼砂</td>
      <td class="cert-value"><?php echo htmlspecialchars($c["eye_color"] ?: "—"); ?></td>
      <td class="cert-label">出生日期</td>
      <td class="cert-value"><?php echo htmlspecialchars($c["birth_date"] ?: "—"); ?></td>
    </tr>
  </table>

  <!-- 父母信息 -->
  <table class="cert-parents-table">
    <tr>
      <th class="cert-father">父 鸽 <?php echo htmlspecialchars($c["father_strain"] ? "(" . $c["father_strain"] . ")" : ""); ?></th>
      <th class="cert-mother">母 鸽 <?php echo htmlspecialchars($c["mother_strain"] ? "(" . $c["mother_strain"] . ")" : ""); ?></th>
    </tr>
    <tr>
      <td class="cert-father">
        <div>足环号：<?php echo htmlspecialchars($c["father_ring"] ?: "—"); ?></div>
        <div>鸽 名：<?php echo htmlspecialchars($c["father_name"] ?: "—"); ?></div>
        <?php if ($c["father_achievements"]): ?>
        <div>赛 绩：<?php echo htmlspecialchars($c["father_achievements"]); ?></div>
        <?php endif; ?>
      </td>
      <td class="cert-mother">
        <div>足环号：<?php echo htmlspecialchars($c["mother_ring"] ?: "—"); ?></div>
        <div>鸽 名：<?php echo htmlspecialchars($c["mother_name"] ?: "—"); ?></div>
        <?php if ($c["mother_achievements"]): ?>
        <div>赛 绩：<?php echo htmlspecialchars($c["mother_achievements"]); ?></div>
        <?php endif; ?>
      </td>
    </tr>
  </table>

  <!-- 祖辈谱系 -->
  <table class="cert-grand-table">
    <tr>
      <th colspan="2" class="cert-father-th">父鸽之父</th>
      <th colspan="2" class="cert-father-th">父鸽之母</th>
      <th colspan="2" class="cert-mother-th">母鸽之父</th>
      <th colspan="2" class="cert-mother-th">母鸽之母</th>
    </tr>
    <tr>
      <td class="cert-sm"><?php echo htmlspecialchars($c["grand_fa_father_ring"] ?: "—"); ?></td>
      <td class="cert-sm"><?php echo htmlspecialchars($c["grand_fa_father_name"] ?: "—"); ?></td>
      <td class="cert-sm"><?php echo htmlspecialchars($c["grand_fa_mother_ring"] ?: "—"); ?></td>
      <td class="cert-sm"><?php echo htmlspecialchars($c["grand_fa_mother_name"] ?: "—"); ?></td>
      <td class="cert-sm"><?php echo htmlspecialchars($c["grand_mo_father_ring"] ?: "—"); ?></td>
      <td class="cert-sm"><?php echo htmlspecialchars($c["grand_mo_father_name"] ?: "—"); ?></td>
      <td class="cert-sm"><?php echo htmlspecialchars($c["grand_mo_mother_ring"] ?: "—"); ?></td>
      <td class="cert-sm"><?php echo htmlspecialchars($c["grand_mo_mother_name"] ?: "—"); ?></td>
    </tr>
  </table>

  <!-- 育鸽者信息 -->
  <div class="cert-breeder">
    <div class="cert-breeder-row">
      <span class="cert-label">育鸽者：</span>
      <span class="cert-value"><?php echo htmlspecialchars($c["breeder_name"] ?: "—"); ?></span>
      <span class="cert-label cert-label-spacer">鸽 舍：</span>
      <span class="cert-value"><?php echo htmlspecialchars($c["breeder_loft"] ?: "—"); ?></span>
      <span class="cert-label cert-label-spacer">电 话：</span>
      <span class="cert-value"><?php echo htmlspecialchars($c["breeder_phone"] ?: "—"); ?></span>
    </div>
  </div>

  <!-- 底部 -->
  <div class="cert-footer">
    <div class="cert-footer-left">
      <div>证书编号：<?php echo $certNo; ?></div>
      <div>生成日期：<?php echo date("Y-m-d"); ?></div>
    </div>
    <div class="cert-footer-right">
      <div class="cert-seal">信鸽之家</div>
      <div class="cert-seal-url">www.xgjia.com</div>
    </div>
  </div>
</div>

<style>
/* ===== 证书基础样式（PC默认A4） ===== */
.cert-body {
  width: 100%;
  max-width: 780px;
  margin: 0 auto;
  background: linear-gradient(135deg, #fffef9 0%, #fffdf5 50%, #fffef9 100%);
  border: 3px double #8b0000;
  padding: 40px 45px;
  font-family: "SimSun", "STSong", "Songti SC", "PingFang SC", "Microsoft YaHei", serif;
  color: #2c1810;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
  position: relative;
  box-sizing: border-box;
}
/* 装饰角标 */
.cert-body::before {
  content: "";
  position: absolute; top: 12px; right: 12px;
  width: 60px; height: 60px;
  border: 1px solid #c9a84c;
  border-left: none; border-bottom: none;
  opacity: 0.3;
}
.cert-body::after {
  content: "";
  position: absolute; bottom: 12px; left: 12px;
  width: 60px; height: 60px;
  border: 1px solid #c9a84c;
  border-right: none; border-top: none;
  opacity: 0.3;
}

.cert-header { text-align: center; margin-bottom: 28px; }
.cert-header-line { height: 2px; background: linear-gradient(90deg, transparent, #8b0000, transparent); }
.cert-title-area { padding: 14px 0; }
.cert-sub { font-size: 14px; color: #666; letter-spacing: 4px; }
.cert-main-title { font-size: 32px; font-weight: 900; color: #8b0000; letter-spacing: 12px; margin: 6px 0; }
.cert-sub-en { font-size: 11px; color: #999; letter-spacing: 3px; }

/* 本鸽信息表 */
.cert-info-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
.cert-info-table td { padding: 7px 12px; border: 1px solid #d4c5a0; font-size: 14px; }
.cert-label { background: #fdf8ec; width: 100px; font-weight: 700; text-align: center; color: #5c3d1e; }
.cert-value { color: #333; }
.cert-ring { font-weight: 700; color: #8b0000; letter-spacing: 1px; }

/* 父母表 */
.cert-parents-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
.cert-parents-table th { padding: 8px; font-size: 15px; font-weight: 800; border: 1px solid #d4c5a0; }
.cert-parents-table td { padding: 12px; border: 1px solid #d4c5a0; vertical-align: top; line-height: 1.8; }
.cert-father { background: #f0f7ff; }
.cert-father-th { background: #f0f7ff; text-align: center; }
.cert-mother { background: #fff0f5; }
.cert-mother-th { background: #fff0f5; text-align: center; }

/* 祖辈表 */
.cert-grand-table { width: 100%; border-collapse: collapse; margin-bottom: 28px; }
.cert-grand-table th { padding: 6px; font-size: 12px; font-weight: 700; border: 1px solid #d4c5a0; color: #5c3d1e; }
.cert-grand-table td { padding: 6px 8px; font-size: 12px; border: 1px solid #d4c5a0; }
.cert-sm { font-size: 12px; text-align: center; }

/* 育鸽者 */
.cert-breeder { margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px dashed #d4c5a0; }
.cert-breeder-row { display: flex; flex-wrap: wrap; gap: 8px 0; font-size: 14px; align-items: baseline; }
.cert-label-spacer { margin-left: 40px; }

/* 底部 */
.cert-footer { display: flex; justify-content: space-between; align-items: flex-end; font-size: 12px; color: #666; }
.cert-footer-right { text-align: center; }
.cert-seal { font-size: 16px; font-weight: 800; color: #8b0000; letter-spacing: 4px;
  border: 2px solid #8b0000; display: inline-block; padding: 4px 16px; border-radius: 4px; transform: rotate(-3deg); }
.cert-seal-url { font-size: 10px; color: #999; margin-top: 4px; }

/* ===== 移动端适配 — 证书缩放 ===== */
@media (max-width: 768px) {
  .cert-body {
    max-width: 100%;
    padding: 20px 16px;
    border-width: 2px;
  }
  .cert-body::before, .cert-body::after { width: 30px; height: 30px; top: 6px; right: 6px; }

  .cert-header { margin-bottom: 18px; }
  .cert-sub { font-size: 12px; letter-spacing: 2px; }
  .cert-main-title { font-size: 22px; letter-spacing: 6px; }
  .cert-sub-en { font-size: 10px; letter-spacing: 2px; }

  /* 信息表 — 标签变窄 */
  .cert-info-table td { padding: 5px 8px; font-size: 12px; word-break: break-all; }
  .cert-label { width: 56px; font-size: 11px; }

  /* 父母表 — 缩小字体 */
  .cert-parents-table th { font-size: 13px; padding: 6px; }
  .cert-parents-table td { padding: 8px; font-size: 12px; line-height: 1.6; }

  /* 祖辈表 — 8列太宽，用横向滚动 */
  .cert-grand-table { display: block; overflow-x: auto; -webkit-overflow-scrolling: touch; white-space: nowrap; }
  .cert-grand-table th { font-size: 10px; padding: 4px 6px; white-space: nowrap; }
  .cert-grand-table td { font-size: 10px; padding: 4px 6px; white-space: nowrap; }

  /* 育鸽者换行 */
  .cert-breeder-row { font-size: 12px; gap: 4px 0; }
  .cert-breeder-row .cert-label-spacer { margin-left: 0; }
  .cert-breeder-row span { display: inline; }

  .cert-footer { flex-direction: column; align-items: center; gap: 12px; text-align: center; }
  .cert-footer-left { text-align: center; }
  .cert-seal { font-size: 14px; padding: 3px 12px; }

  /* 嵌在移动端卡片中的证书去角标 */
  .cert-body::before, .cert-body::after { display: none; }
}

@media (max-width: 480px) {
  .cert-body { padding: 14px 10px; }
  .cert-main-title { font-size: 19px; letter-spacing: 4px; }
  .cert-info-table td { font-size: 11px; padding: 4px 6px; }
  .cert-label { width: 48px; font-size: 10px; }
  .cert-parents-table th { font-size: 12px; }
  .cert-parents-table td { font-size: 11px; padding: 6px; }
  .cert-breeder-row { font-size: 11px; }
  .cert-footer { font-size: 11px; }
}

/* ===== 打印优化 ===== */
@media print {
  @page { size: A4; margin: 8mm; }
  body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  .cert-body {
    border: 3px double #000; box-shadow: none;
    padding: 20px 25px; max-width: 100%;
  }
  .cert-body::before, .cert-body::after { border-color: #999; }
  .cert-info-table td, .cert-parents-table th, .cert-parents-table td,
  .cert-grand-table th, .cert-grand-table td { border-color: #999; }
  .cert-label { background: #f5f5f5; }
  .cert-father { background: #f8f8ff; }
  .cert-mother { background: #fff8f8; }
  .cert-grand-table { overflow-x: visible; white-space: normal; display: table; }
}
</style>