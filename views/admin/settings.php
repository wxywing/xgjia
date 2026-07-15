<?php
extract($data); $activeMenu = 'settings';
$s = $settings ?? [];
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
        <div class="content-card">
            <div class="card-header">
                <h3><i class="fas fa-cog" style="margin-right:8px;"></i>系统设置</h3>
            </div>
            <div style="padding:30px;">
                <form id="settingsForm" onsubmit="saveSettings(event)">
                    <!-- 基本设置 -->
                    <h4 style="color:#1f2937;font-size:16px;margin-bottom:20px;border-bottom:2px solid #3b82f6;padding-bottom:8px;">
                        <i class="fas fa-globe" style="margin-right:6px;color:#3b82f6;"></i>基本设置
                    </h4>
                    
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                        <div class="form-group">
                            <label class="form-label">网站名称</label>
                            <input type="text" name="settings[site_name]" class="form-control" value="<?php echo h($s['site_name'] ?? '信鸽之家'); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">联系邮箱</label>
                            <input type="email" name="settings[contact_email]" class="form-control" value="<?php echo h($s['contact_email'] ?? 'admin@xgjia.com'); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">网站描述</label>
                        <textarea name="settings[site_desc]" class="form-control" rows="2"><?php echo h($s['site_desc'] ?? '信鸽爱好者信息发布平台'); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">网站关键词</label>
                        <input type="text" name="settings[site_keywords]" class="form-control" value="<?php echo h($s['site_keywords'] ?? '信鸽,赛鸽,铭鸽,鸽友'); ?>">
                        <div class="form-hint">多个关键词用逗号分隔</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">ICP备案号</label>
                        <input type="text" name="settings[site_icp]" class="form-control" value="<?php echo h($s['site_icp'] ?? ''); ?>" placeholder="如：京ICP备XXXXXXXX号">
                    </div>
                    
                    <!-- 会员设置 -->
                    <h4 style="color:#1f2937;font-size:16px;margin:30px 0 20px;border-bottom:2px solid #f59e0b;padding-bottom:8px;">
                        <i class="fas fa-crown" style="margin-right:6px;color:#f59e0b;"></i>会员设置
                    </h4>
                    
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                        <div class="form-group">
                            <label class="form-label">VIP月费（元）</label>
                            <input type="number" step="0.01" name="settings[vip_monthly_price]" class="form-control" value="<?php echo h($s['vip_monthly_price'] ?? '29.00'); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">VIP年费（元）</label>
                            <input type="number" step="0.01" name="settings[vip_yearly_price]" class="form-control" value="<?php echo h($s['vip_yearly_price'] ?? '299.00'); ?>">
                        </div>
                    </div>
                    
                    <!-- 功能开关 -->
                    <h4 style="color:#1f2937;font-size:16px;margin:30px 0 20px;border-bottom:2px solid #10b981;padding-bottom:8px;">
                        <i class="fas fa-toggle-on" style="margin-right:6px;color:#10b981;"></i>功能开关
                    </h4>
                    
                    <div style="display:flex;flex-direction:column;gap:15px;">
                        <div style="display:flex;align-items:center;gap:12px;padding:12px;background:#f9fafb;border-radius:8px;">
                            <input type="checkbox" name="settings[enable_register]" value="1" id="enable_register" <?php echo ($s['enable_register'] ?? '1') == '1' ? 'checked' : ''; ?> style="width:18px;height:18px;">
                            <label for="enable_register" style="flex:1;">
                                <strong>开启注册</strong>
                                <span style="color:#9ca3af;font-size:13px;display:block;">允许新用户注册账号</span>
                            </label>
                        </div>
                        <div style="display:flex;align-items:center;gap:12px;padding:12px;background:#f9fafb;border-radius:8px;">
                            <input type="checkbox" name="settings[enable_audit]" value="1" id="enable_audit" <?php echo ($s['enable_audit'] ?? '0') == '1' ? 'checked' : ''; ?> style="width:18px;height:18px;">
                            <label for="enable_audit" style="flex:1;">
                                <strong>开启审核</strong>
                                <span style="color:#9ca3af;font-size:13px;display:block;">用户发布内容需管理员审核后才能显示</span>
                            </label>
                        </div>
                    </div>
                    
                    <div style="margin-top:30px;display:flex;justify-content:flex-end;gap:10px;">
                        <button type="button" class="btn btn-secondary" onclick="location.reload()">重置</button>
                        <button type="submit" class="btn btn-primary" style="padding:10px 30px;font-size:15px;"><i class="fas fa-save"></i> 保存设置</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
<?php include __DIR__ . '/_scripts.php'; ?>
<script>
function saveSettings(e) {
    e.preventDefault();
    var form = document.getElementById('settingsForm');
    var formData = new FormData(form);
    
    // 处理checkbox：未勾选时FormData不包含该字段
    if (!document.getElementById('enable_register').checked) {
        formData.set('settings[enable_register]', '0');
    }
    if (!document.getElementById('enable_audit').checked) {
        formData.set('settings[enable_audit]', '0');
    }
    
    fetch('/admin.php?action=settings', { method: 'POST', body: formData })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            showToast(data.message, 'success');
        } else { showToast(data.message, 'error'); }
    })
    .catch(function() { showToast('保存失败', 'error'); });
}
</script>
</body>
</html>