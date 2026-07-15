<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="在信鸽之家发布赛鸽数据分析、养鸽知识、行业解读等专业内容。">
    <meta name="keywords" content="发布文章,赛鸽知识,信鸽之家">
    <meta property="og:title" content="发布文章 - 信鸽之家">
    <meta property="og:description" content="在信鸽之家发布赛鸽数据分析、养鸽知识、行业解读等专业内容。">
    <meta property="og:image" content="https://www.xgjia.com/public/images/og-cover.png">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="信鸽之家">
    <link rel="canonical" href="https://www.xgjia.com/article/create">

    <title><?= $pageTitle ?? '发布文章 | ' . SITE_NAME ?></title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/b-scheme.css">

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

    </style>
</head>
<body>
    <?php include __DIR__ . '/_head.php'; ?>

    <div class="container" style="padding: 30px 0;">
        <div class="publish-page">
            <h1 class="page-title">发布文章</h1>

            <?php if (isset($publish_check) && !$publish_check['allowed']): ?>
            <!-- 配额不足提示 -->
            <div class="quota-blocked" style="background: #fef3c7; border: 1px solid #f59e0b; border-radius: 12px; padding: 30px; text-align: center; margin-bottom: 20px;">
                <i class="fas fa-lock" style="font-size: 48px; color: #f59e0b; margin-bottom: 15px;"></i>
                <h3 style="color: #92400e; margin-bottom: 10px;"><?php echo h($publish_check['message']); ?></h3>
                <p style="color: #78350f; margin-bottom: 20px;">升级会员即可解锁更多发布额度</p>
                <a href="/user/membership" class="btn btn-primary" style="background: #f59e0b; border-color: #f59e0b; padding: 10px 30px;">
                    <i class="fas fa-crown mr-1"></i>立即升级会员
                </a>
            </div>
            <?php elseif (isset($publish_check) && $publish_check['allowed'] && isset($publish_check['remaining']) && $publish_check['remaining'] !== 'unlimited' && $publish_check['remaining'] <= 3): ?>
            <!-- 额度即将用完提醒 -->
            <div class="quota-warning" style="background: #eff6ff; border: 1px solid #3b82f6; border-radius: 8px; padding: 12px 16px; margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-info-circle" style="color: #3b82f6;"></i>
                <span style="color: #1e40af; font-size: 14px;">本月剩余发布额度:<strong><?php echo $publish_check['remaining']; ?></strong> 次</span>
                <a href="/user/membership" style="color: #2563eb; font-size: 13px; margin-left: auto;">升级额度 →</a>
            </div>
            <?php endif; ?>

            <?php if (!isset($publish_check) || $publish_check['allowed']): ?>
            <form id="articleForm" class="publish-form">
                <div class="form-section">
                    <div class="form-group">
                        <label>文章标题 <span class="required">*</span></label>
                        <input type="text" name="title" required placeholder="请输入文章标题">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>文章分类</label>
                            <select name="category_id">
                                <option value="">请选择分类</option>
                                <?php if (!empty($categories)): ?>
                                    <?php foreach ($categories as $cat): ?>
                                        <?php if ($cat['type'] == 1): ?>
                                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>来源</label>
                            <input type="text" name="source" placeholder="文章来源(可选)">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>封面图</label>
                        <div class="cover-upload">
                            <div class="upload-box" id="coverUpload">
                                <i class="fas fa-image"></i>
                                <span>上传封面</span>
                            </div>
                            <input type="file" accept="image/*" id="coverInput" style="display:none">
                            <img loading="lazy" id="coverPreview" src="" alt="封面预览" style="display:none; max-width: 200px; margin-top: 10px;">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-group">
                        <label>文章摘要</label>
                        <textarea name="summary" rows="2" placeholder="简短描述文章内容(可选)"></textarea>
                    </div>

                    <div class="form-group">
                        <label>文章内容 <span class="required">*</span></label>
                        <textarea name="content" rows="15" required placeholder="请输入文章内容,支持换行"></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-default" onclick="history.back()">取消</button>
                    <button type="button" class="btn btn-default" onclick="saveDraft()">保存草稿</button>
                    <button type="submit" class="btn btn-primary">提交发布</button>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- 草稿保存 JavaScript -->
    <script>
        function saveDraft() {
            var title = document.querySelector('input[name="title"]').value;
            var content = document.querySelector('textarea[name="content"]').value;
            var draft = {
                title: title,
                content: content,
                saved_at: new Date().toISOString()
            };
            localStorage.setItem('article_draft', JSON.stringify(draft));
            alert('草稿已保存！');
        }
    </script>

    <?php include __DIR__ . '/_footer.php'; ?>
