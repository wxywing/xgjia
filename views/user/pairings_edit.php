<?php
$pageTitle = "编辑配对 | " . SITE_NAME;
$breadcrumbs = [["title" => "会员中心", "url" => "/user"], ["title" => "我的配对", "url" => "/user/pairings"], ["title" => "编辑配对"]];
include 'views/partials/header.php';
?>

<div class="container">
    <div class="main-content">
        <?php include 'views/user/sidebar.php'; ?>
        <div class="content-with-sidebar">
            <h1>编辑配对</h1>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="/user/pairings_edit?id=<?php echo $pairing['id']; ?>">
                <div class="form-group">
                    <label for="sire_id">父鸽（雄）</label>
                    <select name="sire_id" id="sire_id" class="form-control" required>
                        <option value="">-- 选择父鸽 --</option>
                        <?php foreach ($myPigeons as $pg): ?>
                            <?php if ($pg['gender'] == '雄' || $pg['gender'] == 'male'): ?>
                                <option value="<?php echo $pg['id']; ?>" <?php echo $pg['id'] == $pairing['sire_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($pg['name'] ?: $pg['ring_number']); ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="dam_id">母鸽（雌）</label>
                    <select name="dam_id" id="dam_id" class="form-control" required>
                        <option value="">-- 选择母鸽 --</option>
                        <?php foreach ($myPigeons as $pg): ?>
                            <?php if ($pg['gender'] == '雌' || $pg['gender'] == 'female'): ?>
                                <option value="<?php echo $pg['id']; ?>" <?php echo $pg['id'] == $pairing['dam_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($pg['name'] ?: $pg['ring_number']); ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="pairing_date">配对日期</label>
                    <input type="date" name="pairing_date" id="pairing_date" class="form-control" value="<?php echo $pairing['pairing_date']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="notes">备注</label>
                    <textarea name="notes" id="notes" class="form-control" rows="3"><?php echo htmlspecialchars($pairing['notes'] ?: ''); ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">保存修改</button>
                    <a href="/user/pairings" class="btn btn-secondary">取消</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'views/partials/footer.php'; ?>
