<?php
$pageTitle = "我的配对 | " . SITE_NAME;
$breadcrumbs = [["title" => "会员中心", "url" => "/user"], ["title" => "我的配对"]];
include 'views/partials/header.php';
?>

<div class="container">
    <div class="main-content">
        <?php include 'views/user/sidebar.php'; ?>
        <div class="content-with-sidebar">
            <div class="page-header">
                <h1>我的配对</h1>
                <a href="/user/pairings_create" class="btn btn-primary">创建新配对</a>
            </div>

            <?php if (empty($pairings)): ?>
            <div class="empty-state">
                <p>暂无配对记录，点击上方按钮创建第一条配对。</p>
            </div>
            <?php else: ?>
            <div class="pairings-list">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>父鸽（雄）</th>
                            <th>母鸽（雌）</th>
                            <th>配对日期</th>
                            <th>备注</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pairings as $p): ?>
                        <tr>
                            <td>
                                <a href="/pigeon/<?php echo $p['sire_id']; ?>" target="_blank">
                                    <?php echo htmlspecialchars($p['sire_name'] ?: '未知'); ?>
                                </a>
                            </td>
                            <td>
                                <a href="/pigeon/<?php echo $p['dam_id']; ?>" target="_blank">
                                    <?php echo htmlspecialchars($p['dam_name'] ?: '未知'); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($p['pairing_date']); ?></td>
                            <td><?php echo htmlspecialchars($p['notes'] ?: '-'); ?></td>
                            <td>
                                <a href="/user/pairings_edit?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-secondary">编辑</a>
                                <a href="javascript:;" onclick="if(confirm('确定删除此配对？')) window.location='/user/pairings_delete?id=<?php echo $p['id']; ?>'" class="btn btn-sm btn-danger">删除</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'views/partials/footer.php'; ?>
