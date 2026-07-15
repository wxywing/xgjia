<?php extract($data); $activeMenu = 'orders'; ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($pageTitle); ?></title>
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php include __DIR__ . '/_styles.php'; ?>
    <style>
    .type-tabs { display:flex; gap:4px; margin-bottom:16px; }
    .type-tabs .tab { padding:8px 18px; border-radius:6px; cursor:pointer; font-size:14px; border:none; background:#f0f0f0; color:#666; text-decoration:none; transition:all .2s; }
    .type-tabs .tab:hover { background:#e0e0e0; }
    .type-tabs .tab.active { background:#1a5fa8; color:#fff; }
    .type-tabs .tab .count { background:rgba(255,255,255,.25); padding:1px 6px; border-radius:10px; font-size:12px; margin-left:6px; }
    .type-tabs .tab:not(.active) .count { background:#ccc; }
    </style>
</head>
<body>
<div class="admin-container">
    <?php include __DIR__ . '/_sidebar.php'; ?>
    <main class="main-content">
<?php include __DIR__ . '/_header.php'; ?>

        <!-- 产品类型 Tab -->
        <div class="type-tabs">
            <a href="?action=orders" class="tab <?php echo $productType === 'all' ? 'active' : ''; ?>">
                全部 <span class="count"><?php echo ($orderStats['membership_cnt'] ?? 0) + ($orderStats['product_cnt'] ?? 0); ?></span>
            </a>
            <a href="?action=orders&product_type=membership" class="tab <?php echo $productType === 'membership' ? 'active' : ''; ?>">
                会员升级 <span class="count"><?php echo $orderStats['membership_cnt'] ?? 0; ?></span>
            </a>
            <a href="?action=orders&product_type=product" class="tab <?php echo $productType === 'product' ? 'active' : ''; ?>">
                数字产品 <span class="count"><?php echo $orderStats['product_cnt'] ?? 0; ?></span>
            </a>
        </div>

        <!-- 筛选栏 & 统计卡片 -->
        <div style="display:grid;grid-template-columns:1fr auto;gap:16px;align-items:start;margin-bottom:20px;">
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;">
                <?php
                $membershipTotal = floatval($orderStats['membership_amount'] ?? 0);
                $productTotal = floatval($orderStats['product_amount'] ?? 0);
                $paidAmount = floatval($orderStats['paid_amount'] ?? 0);
                $pendingAmount = floatval($orderStats['pending_amount'] ?? 0);
                $approvedAmount = floatval($orderStats['approved_amount'] ?? 0);
                ?>
                <div class="content-card" style="text-align:center;padding:14px;">
                    <div style="font-size:12px;color:#888;">会员升级总额</div>
                    <div style="font-size:20px;font-weight:bold;color:#1a5fa8;">¥<?php echo number_format($membershipTotal, 2); ?></div>
                </div>
                <div class="content-card" style="text-align:center;padding:14px;">
                    <div style="font-size:12px;color:#888;">数字产品总额</div>
                    <div style="font-size:20px;font-weight:bold;color:#c9a84c;">¥<?php echo number_format($productTotal, 2); ?></div>
                </div>
                <div class="content-card" style="text-align:center;padding:14px;">
                    <div style="font-size:12px;color:#888;">已收款</div>
                    <div style="font-size:20px;font-weight:bold;color:#16a34a;">¥<?php echo number_format($paidAmount, 2); ?></div>
                </div>
                <div class="content-card" style="text-align:center;padding:14px;">
                    <div style="font-size:12px;color:#888;">待收款</div>
                    <div style="font-size:20px;font-weight:bold;color:#d97706;">¥<?php echo number_format($pendingAmount + $approvedAmount, 2); ?></div>
                </div>
            </div>
            <form method="GET" action="" style="display:flex;gap:8px;align-items:center;flex-shrink:0;">
                <input type="hidden" name="action" value="orders">
                <?php if ($productType !== 'all'): ?>
                <input type="hidden" name="product_type" value="<?php echo h($productType); ?>">
                <?php endif; ?>
                <select name="status" style="padding:8px 12px;border:1px solid #ddd;border-radius:6px;">
                    <option value="">全部状态</option>
                    <option value="0" <?php echo $status === '0' ? 'selected' : ''; ?>>待审核</option>
                    <option value="1" <?php echo $status === '1' ? 'selected' : ''; ?>>已支付</option>
                    <option value="2" <?php echo $status === '2' ? 'selected' : ''; ?>>已取消</option>
                    <option value="3" <?php echo $status === '3' ? 'selected' : ''; ?>>已审批(待支付)</option>
                </select>
                <input type="text" name="keyword" value="<?php echo h($keyword ?? ''); ?>" placeholder="订单号/用户名/邮箱" style="padding:8px 12px;border:1px solid #ddd;border-radius:6px;width:160px;">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
                <?php if (!empty($keyword) || $status !== ''): ?>
                <a href="?action=orders<?php echo $productType !== 'all' ? '&product_type=' . h($productType) : ''; ?>" class="btn btn-secondary btn-sm">清除</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- 数据表格 -->
        <div class="content-card">
            <div class="card-header">
                <h3>订单列表 <span style="font-size:14px;color:#999;font-weight:normal;">(共 <?php echo h($total); ?> 条)</span></h3>
            </div>
            <div class="card-body" style="padding:0;">
                <table class="admin-table" style="margin:0;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>订单号</th>
                            <th>用户</th>
                            <th>产品</th>
                            <th>金额</th>
                            <th>支付方式</th>
                            <th>状态</th>
                            <th>时间</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $statusMap = [0 => '待审核', 1 => '已支付', 2 => '已取消', 3 => '已审批(待支付)'];
                        $statusClass = [0 => 'badge-warning', 1 => 'badge-success', 2 => 'badge-secondary', 3 => 'badge-info'];
                        $levelMap = [0 => '免费', 1 => 'VIP', 2 => 'VIP', 3 => 'VIP'];
                        $planTypeLabel = [1 => '月付', 2 => '年付'];
                        $productTypeLabel = [
                            'membership' => '会员升级',
                            'report' => '足环报告',
                            'certificate' => '血统证书',
                            'compare' => '公棚对比',
                        ];
                        ?>
                        <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="9">
                                <div class="empty-state"><i class="fas fa-receipt"></i><p>暂无订单数据</p></div>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                        <?php
                            $pt = $order['product_type'] ?? 'membership';
                            $isMembership = ($pt === 'membership' || $pt === '' || $pt === null);
                        ?>
                        <tr>
                            <td><?php echo h($order['id']); ?></td>
                            <td>
                                <a href="javascript:;" onclick="showOrderDetail(<?php echo h($order['id']); ?>)"
                                   style="font-family:monospace;font-size:12px;color:#1a5fa8;text-decoration:none;"
                                   title="点击查看详情"><?php echo h($order['order_no']); ?></a>
                            </td>
                            <td>
                                <div><?php echo h($order['username']); ?></div>
                                <div style="font-size:12px;color:#999;"><?php echo h($order['email']); ?></div>
                            </td>
                            <td>
                                <?php if ($isMembership): ?>
                                <span class="badge badge-primary"><?php echo $planTypeLabel[intval($order['plan_type'])] ?? '会员'; ?></span>
                                <span style="font-size:12px;color:#888;margin-left:6px;">¥<?php echo number_format(floatval($order['amount']), 2); ?> / <?php echo intval($order['plan_type']) === 2 ? '年' : '月'; ?></span>
                                <?php else: ?>
                                <span class="badge badge-info"><?php echo $productTypeLabel[$pt] ?? h($pt); ?></span>
                                <?php if (!empty($order['product_ref'])): ?>
                                <span style="font-size:12px;color:#888;margin-left:4px;">#<?php echo h($order['product_ref']); ?></span>
                                <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td style="font-weight:bold;color:#16a34a;">¥<?php echo number_format(floatval($order['amount']), 2); ?></td>
                            <td><?php echo h($order['payment_method'] ?? '-'); ?></td>
                            <td><span class="badge <?php echo $statusClass[$order['status']] ?? 'badge-secondary'; ?>"><?php echo $statusMap[$order['status']] ?? '未知'; ?></span></td>
                            <td style="font-size:12px;color:#888;"><?php echo h(substr($order['created_at'], 0, 16)); ?></td>
                            <td class="actions">
                                <?php if ($order['status'] == 0): ?>
                                <button onclick="updateOrderStatus(<?php echo h($order['id']); ?>, 3)" class="btn btn-sm btn-info" title="审批"><i class="fas fa-clipboard-check"></i></button>
                                <button onclick="updateOrderStatus(<?php echo h($order['id']); ?>, 1)" class="btn btn-sm btn-success" title="标记已支付"><i class="fas fa-check"></i></button>
                                <button onclick="updateOrderStatus(<?php echo h($order['id']); ?>, 2)" class="btn btn-sm btn-warning" title="取消"><i class="fas fa-times"></i></button>
                                <?php elseif ($order['status'] == 3): ?>
                                <button onclick="updateOrderStatus(<?php echo h($order['id']); ?>, 1)" class="btn btn-sm btn-success" title="标记已支付"><i class="fas fa-check"></i></button>
                                <button onclick="updateOrderStatus(<?php echo h($order['id']); ?>, 2)" class="btn btn-sm btn-warning" title="取消"><i class="fas fa-times"></i></button>
                                <?php else: ?>
                                <span style="color:#ccc;font-size:12px;">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

                <?php if ($totalPages > 1): ?>
                <div class="pagination" style="padding:16px 20px;border-top:1px solid #f0f0f0;">
                    <?php
                    $range = 2;
                    $start = max(1, $page - $range);
                    $end = min($totalPages, $page + $range);
                    ?>
                    <?php if ($page > 1): ?>
                    <a href="?action=orders&page=<?php echo $page-1; ?><?php echo $productType !== 'all' ? '&product_type=' . h($productType) : ''; ?><?php echo $status !== '' ? '&status=' . h($status) : ''; ?><?php echo $keyword ? '&keyword=' . urlencode($keyword) : ''; ?>">上一页</a>
                    <?php endif; ?>
                    <?php if ($start > 1): ?>
                    <a href="?action=orders&page=1<?php echo $productType !== 'all' ? '&product_type=' . h($productType) : ''; ?><?php echo $status !== '' ? '&status=' . h($status) : ''; ?><?php echo $keyword ? '&keyword=' . urlencode($keyword) : ''; ?>">1</a>
                    <?php if ($start > 2): ?><span style="padding:8px 6px;color:#9ca3af;">…</span><?php endif; ?>
                    <?php endif; ?>
                    <?php for ($i = $start; $i <= $end; $i++): ?>
                    <?php if ($i == $page): ?>
                    <span class="active"><?php echo $i; ?></span>
                    <?php else: ?>
                    <a href="?action=orders&page=<?php echo $i; ?><?php echo $productType !== 'all' ? '&product_type=' . h($productType) : ''; ?><?php echo $status !== '' ? '&status=' . h($status) : ''; ?><?php echo $keyword ? '&keyword=' . urlencode($keyword) : ''; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                    <?php endfor; ?>
                    <?php if ($end < $totalPages): ?>
                    <?php if ($end < $totalPages - 1): ?><span style="padding:8px 6px;color:#9ca3af;">…</span><?php endif; ?>
                    <a href="?action=orders&page=<?php echo $totalPages; ?><?php echo $productType !== 'all' ? '&product_type=' . h($productType) : ''; ?><?php echo $status !== '' ? '&status=' . h($status) : ''; ?><?php echo $keyword ? '&keyword=' . urlencode($keyword) : ''; ?>"><?php echo $totalPages; ?></a>
                    <?php endif; ?>
                    <?php if ($page < $totalPages): ?>
                    <a href="?action=orders&page=<?php echo $page+1; ?><?php echo $productType !== 'all' ? '&product_type=' . h($productType) : ''; ?><?php echo $status !== '' ? '&status=' . h($status) : ''; ?><?php echo $keyword ? '&keyword=' . urlencode($keyword) : ''; ?>">下一页</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<!-- 订单详情弹窗 -->
<div id="orderModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.5);align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:12px;width:520px;max-width:95vw;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.2);">
        <div style="padding:20px 24px;border-bottom:1px solid #eee;display:flex;align-items:center;justify-content:space-between;">
            <h3 style="margin:0;font-size:16px;"><i class="fas fa-file-invoice" style="margin-right:8px;color:#1a5fa8;"></i>订单详情</h3>
            <button onclick="document.getElementById('orderModal').style.display='none'" style="border:none;background:none;font-size:20px;cursor:pointer;color:#999;">&times;</button>
        </div>
        <div id="modalContent" style="padding:24px;">
            <div style="text-align:center;padding:40px;color:#999;"><i class="fas fa-spinner fa-spin" style="font-size:24px;"></i></div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/_scripts.php'; ?>
<script>
function showOrderDetail(id) {
    var modal = document.getElementById('orderModal');
    var content = document.getElementById('modalContent');
    modal.style.display = 'flex';
    content.innerHTML = '<div style="text-align:center;padding:40px;color:#999;"><i class="fas fa-spinner fa-spin" style="font-size:24px;"></i></div>';

    fetch('/admin.php?action=orderDetail&id=' + id)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.success) {
                content.innerHTML = '<p style="color:#e53;color:#e53;">' + (data.message || '加载失败') + '</p>';
                return;
            }
            var o = data.order;
            var levelMap = {0:'免费',1:'VIP',2:'VIP',3:'VIP'};
            var planTypeLabels = {1:'月付',2:'年付'};
            var statusMap = {0:'待审核',1:'已支付',2:'已取消',3:'已审批(待支付)'};
            var statusCls = {0:'badge-warning',1:'badge-success',2:'badge-secondary',3:'badge-info'};
            var ptLabel = {membership:'会员升级',report:'足环报告',certificate:'血统证书',compare:'公棚对比'};
            var pt = o.product_type || 'membership';
            var isMember = (pt === 'membership' || pt === '' || pt === null);

            var html = '<table style="width:100%;border-collapse:collapse;font-size:14px;">';

            // 基础信息
            html += '<tr style="border-bottom:1px solid #f0f0f0;"><td style="padding:10px 0;color:#888;width:100px;">订单号</td><td style="padding:10px 0;font-family:monospace;color:#1a5fa8;">' + escHtml(o.order_no) + '</td></tr>';
            html += '<tr style="border-bottom:1px solid #f0f0f0;"><td style="padding:10px 0;color:#888;">用户名</td><td style="padding:10px 0;">' + escHtml(o.username || '-') + '</td></tr>';
            html += '<tr style="border-bottom:1px solid #f0f0f0;"><td style="padding:10px 0;color:#888;">邮箱</td><td style="padding:10px 0;">' + escHtml(o.email || '-') + '</td></tr>';

            // 产品
            html += '<tr style="border-bottom:1px solid #f0f0f0;"><td style="padding:10px 0;color:#888;vertical-align:top;">产品</td><td style="padding:10px 0;">';
            if (isMember) {
                html += '<span class="badge badge-primary">' + (planTypeLabels[parseInt(o.plan_type)] || '会员') + '</span>';
                html += ' <span style="font-size:12px;color:#888;margin-left:8px;">¥' + parseFloat(o.amount || 0).toFixed(2) + ' / ' + (parseInt(o.plan_type) === 2 ? '年' : '月') + '</span>';
            } else {
                html += '<span class="badge badge-info">' + (ptLabel[pt] || pt) + '</span>';
                if (o.product_ref) {
                    html += ' <span style="font-size:12px;color:#888;margin-left:4px;">#' + escHtml(o.product_ref) + '</span>';
                }
            }
            html += '</td></tr>';

            // 金额
            html += '<tr style="border-bottom:1px solid #f0f0f0;"><td style="padding:10px 0;color:#888;">金额</td><td style="padding:10px 0;font-weight:bold;color:#16a34a;font-size:18px;">¥' + parseFloat(o.amount || 0).toFixed(2) + '</td></tr>';

            // 支付方式
            html += '<tr style="border-bottom:1px solid #f0f0f0;"><td style="padding:10px 0;color:#888;">支付方式</td><td style="padding:10px 0;">' + escHtml(o.payment_method || '-') + '</td></tr>';

            // 状态
            var st = parseInt(o.status) || 0;
            html += '<tr style="border-bottom:1px solid #f0f0f0;"><td style="padding:10px 0;color:#888;">状态</td><td style="padding:10px 0;"><span class="badge ' + (statusCls[st] || 'badge-secondary') + '">' + (statusMap[st] || '未知') + '</span></td></tr>';

            // 用户当前等级
            if (isMember) {
                html += '<tr style="border-bottom:1px solid #f0f0f0;"><td style="padding:10px 0;color:#888;">用户当前状态</td><td style="padding:10px 0;"><span class="badge badge-secondary">' + (parseInt(o.user_level) > 0 ? 'VIP' : '免费') + '</span>';
                if (o.member_expire_at) {
                    html += ' <span style="font-size:12px;color:#888;margin-left:8px;">有效期至 ' + escHtml(o.member_expire_at.substr(0,10)) + '</span>';
                }
                html += '</td></tr>';
            }

            // 时间
            html += '<tr style="border-bottom:1px solid #f0f0f0;"><td style="padding:10px 0;color:#888;">下单时间</td><td style="padding:10px 0;">' + escHtml(o.created_at || '-') + '</td></tr>';
            html += '<tr style="border-bottom:1px solid #f0f0f0;"><td style="padding:10px 0;color:#888;">支付时间</td><td style="padding:10px 0;">' + escHtml(o.paid_at || '-') + '</td></tr>';

            // 管理员操作
            if (st === 0 || st === 3) {
                html += '<tr><td style="padding:16px 0 0;color:#888;vertical-align:top;">快捷操作</td><td style="padding:16px 0 0;">';
                if (st === 0) {
                    html += '<button onclick="updateOrderStatus(' + o.id + ',3)" class="btn btn-sm btn-info" style="margin-right:6px;"><i class="fas fa-clipboard-check"></i> 审批通过</button>';
                    html += '<button onclick="updateOrderStatus(' + o.id + ',1)" class="btn btn-sm btn-success" style="margin-right:6px;"><i class="fas fa-check"></i> 标记已支付</button>';
                }
                if (st === 3) {
                    html += '<button onclick="updateOrderStatus(' + o.id + ',1)" class="btn btn-sm btn-success" style="margin-right:6px;"><i class="fas fa-check"></i> 标记已支付</button>';
                }
                html += '<button onclick="updateOrderStatus(' + o.id + ',2)" class="btn btn-sm btn-warning"><i class="fas fa-times"></i> 取消</button>';
                html += '</td></tr>';
            }

            html += '</table>';
            content.innerHTML = html;
        })
        .catch(function() {
            content.innerHTML = '<p style="color:#e53;">加载失败，请重试</p>';
        });
}

function escHtml(s) {
    if (!s) return '';
    var div = document.createElement('div');
    div.textContent = s;
    return div.innerHTML;
}

function updateOrderStatus(id, status) {
    var map = {1:'标记为已支付',2:'取消订单',3:'退款'};
    if (!confirm('确认' + (map[status] || '操作') + '？')) return;
    fetch('/admin.php?action=update-order', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: 'id=' + id + '&status=' + status
    })
    .then(function(r){return r.json();})
    .then(function(data){
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || '操作失败');
        }
    })
    .catch(function(){ alert('网络错误'); });
}

// 点击遮罩关闭
document.getElementById('orderModal').addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
});
</script>
</body>
</html>
