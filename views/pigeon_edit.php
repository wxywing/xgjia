<?php
// 增加POST数据大小限制，支持多张base64图片上传
@ini_set('post_max_size', '50M');
@ini_set('upload_max_filesize', '50M');
@ini_set('memory_limit', '256M');
@ini_set('max_execution_time', '120');
@ini_set('max_input_time', '120');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="编辑您在信鸽之家发布的铭鸽信息，更新详情、照片和联系方式。">
    <meta name="keywords" content="编辑铭鸽,铭鸽管理,信鸽之家">
    <meta property="og:title" content="编辑铭鸽 - 信鸽之家">
    <meta property="og:description" content="编辑您在信鸽之家发布的铭鸽信息，更新详情、照片和联系方式。">
    <meta property="og:image" content="https://www.xgjia.com/public/images/og-cover.png">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="信鸽之家">
    <link rel="canonical" href="https://www.xgjia.com/pigeon/edit">

    <title><?= $pageTitle ?? '编辑铭鸽 | ' . SITE_NAME ?></title>
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

    .publish-page {
        max-width: 800px;
        margin: 0 auto;
    }
    
    .page-title {
        font-size: 24px;
        margin-bottom: 30px;
    }
    
    .publish-form {
        background: #fff;
        padding: 30px;
        border-radius: 8px;
    }
    
    .form-section {
        margin-bottom: 30px;
        padding-bottom: 30px;
        border-bottom: 1px solid #eee;
    }
    
    .form-section:last-of-type {
        border-bottom: none;
    }
    
    .form-section h3 {
        font-size: 18px;
        margin-bottom: 20px;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
    }
    
    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
    }
    
    .form-row {
        display: flex;
        gap: 20px;
    }
    
    .form-row .form-group {
        flex: 1;
    }
    
    .required {
        color: #f44336;
    }
    
    .upload-area {
        border: 2px dashed #ddd;
        border-radius: 8px;
        padding: 40px 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .upload-area:hover {
        border-color: #1890ff;
        color: #1890ff;
    }
    
    .upload-area.dragover {
        border-color: #1890ff;
        background: #f0f7ff;
    }
    
    .upload-area i {
        font-size: 48px;
        color: #ddd;
        margin-bottom: 10px;
    }
    
    .upload-area p {
        margin: 5px 0;
        color: #666;
    }
    
    .image-preview {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 20px;
    }
    
    .preview-item {
        width: 100px;
        height: 100px;
        position: relative;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .preview-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .preview-item .remove-btn {
        position: absolute;
        top: 5px;
        right: 5px;
        width: 24px;
        height: 24px;
        background: rgba(239, 68, 68, 0.9);
        color: white;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        font-size: 14px;
        line-height: 1;
    }
    
    .preview-item .remove-btn:hover {
        background: #dc2626;
    }
    
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
        margin-top: 30px;
    }
    
    @media (max-width: 768px) {
        .form-row {
            flex-direction: column;
            gap: 0;
        }
    }
    </style>
</head>
<body>
    <?php include __DIR__ . '/_head.php'; ?>

    <div class="container" style="padding: 30px 0;">
        <div class="publish-page">
            <h1 class="page-title">编辑铭鸽</h1>

            <?php if (!empty($error)): ?>
            <div class="alert alert-danger" style="background: #fef2f2; border: 1px solid #ef4444; color: #991b1b; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px;">
                <?php echo h($error); ?>
            </div>
            <?php endif; ?>

            <form id="pigeonForm" class="publish-form">
                <input type="hidden" name="id" value="<?php echo $pigeon['id']; ?>">

                <div class="form-section">
                    <h3>基本信息</h3>

                    <div class="form-group">
                        <label>鸽子名称 <span class="required">*</span></label>
                        <input type="text" name="name" required value="<?php echo h($pigeon['name']); ?>" placeholder="请输入鸽子名称">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>足环号</label>
                            <input type="text" name="ring_number" value="<?php echo h($pigeon['ring_number'] ?? ''); ?>" placeholder="例如：CHN-2024-123456">
                        </div>
                        <div class="form-group">
                            <label>血统</label>
                            <input type="text" name="bloodline" value="<?php echo h($pigeon['bloodline'] ?? ''); ?>" placeholder="例如：杨阿腾、詹森">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>性别</label>
                            <select name="gender">
                                <option value="0" <?php echo ($pigeon['gender'] ?? 0) == 0 ? 'selected' : ''; ?>>未知</option>
                                <option value="1" <?php echo ($pigeon['gender'] ?? 0) == 1 ? 'selected' : ''; ?>>雄</option>
                                <option value="2" <?php echo ($pigeon['gender'] ?? 0) == 2 ? 'selected' : ''; ?>>雌</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>分类</label>
                            <select name="category_id">
                                <option value="">-- 选择分类 --</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo ($pigeon['category_id'] ?? '') == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo h($cat['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>详细介绍</h3>

                    <div class="form-group">
                        <label>描述</label>
                        <textarea name="description" rows="6" placeholder="请输入鸽子的详细描述，包括外观特征、性格特点等"><?php echo h($pigeon['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>比赛成绩</label>
                        <textarea name="achievements" rows="4" placeholder="请输入鸽子的比赛成绩，如：2024年春赛500公里冠军"><?php echo h($pigeon['achievements'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="form-section">
                    <h3>照片上传</h3>

                    <div class="form-group">
                        <label>鸽舍照片</label>
                        <div class="upload-area" id="uploadArea">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>点击或拖拽图片到这里上传</p>
                            <p class="text-muted">支持 JPG、PNG 格式，单张不超过 5MB</p>
                            <input type="file" id="imageInput" accept="image/*" multiple style="display: none;">
                        </div>
                        <div class="image-preview" id="imagePreview">
                            <?php if (!empty($pigeon['images'])): ?>
                                <?php
                                $images = json_decode($pigeon['images'], true) ?: [];
                                foreach ($images as $index => $imgUrl):
                                ?>
                                <div class="preview-item" data-url="<?php echo h($imgUrl); ?>">
                                    <img loading="lazy" src="<?php echo h($imgUrl); ?>" alt="照片 <?php echo $index + 1; ?>">
                                    <button type="button" class="remove-btn" onclick="removeImage(this)">×</button>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>视频链接</h3>

                    <div class="form-group">
                        <label>视频URL</label>
                        <input type="url" name="video" value="<?php echo h($pigeon['video'] ?? ''); ?>" placeholder="请输入视频链接（选填）">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">保存修改</button>
                    <a href="/user/my_pigeons" class="btn btn-secondary">取消</a>
                </div>
            </form>
        </div>
    </div>

    <?php include __DIR__ . '/_footer.php'; ?>

    <script>
    // 图片上传处理
    const uploadArea = document.getElementById('uploadArea');
    const imageInput = document.getElementById('imageInput');
    const imagePreview = document.getElementById('imagePreview');
    let uploadedImages = <?php echo json_encode(!empty($pigeon['images']) ? json_decode($pigeon['images'], true) ?: [] : []); ?>;

    uploadArea.addEventListener('click', () => imageInput.click());

    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });

    uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('dragover');
    });

    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        const files = e.dataTransfer.files;
        handleFiles(files);
    });

    imageInput.addEventListener('change', (e) => {
        handleFiles(e.target.files);
    });

    function handleFiles(files) {
        Array.from(files).forEach(file => {
            if (file.type.startsWith('image/')) {
                uploadImage(file);
            }
        });
    }

    function uploadImage(file) {
        // 限制单张图片 5MB
        const MAX_SIZE = 5 * 1024 * 1024;
        if (file.size > MAX_SIZE) {
            alert('单张图片不能超过 5MB，请选择更小的图片');
            return;
        }
        const reader = new FileReader();
        reader.onload = function(e) {
            const base64 = e.target.result;
            // 直接 base64 存入 uploadedImages，后端表单提交时附带
            uploadedImages.push(base64);
            renderPreviews();
        };
        reader.readAsDataURL(file);
    }

    function removeImage(index) {
        uploadedImages.splice(index, 1);
        renderPreviews();
    }

    function renderPreviews() {
        imagePreview.innerHTML = uploadedImages.map((url, index) => `
            <div class="preview-item" data-index="${index}">
                <img loading="lazy" src="${url}" alt="照片 ${index + 1}">
                <button type="button" class="remove-btn" onclick="removeImage(${index})">×</button>
            </div>
        `).join('');
    }

    // 表单提交
    document.getElementById('pigeonForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        formData.set('images', JSON.stringify(uploadedImages));

        fetch('/pigeon_edit.php?id=<?php echo $pigeon['id']; ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('更新成功！');
                window.location.href = '/user/my_pigeons';
            } else {
                alert('更新失败：' + data.message);
            }
        })
        .catch(error => {
            console.error('提交错误:', error);
            alert('提交失败，请重试');
        });
    });

    // 菜单切换
    function toggleMenu() {
        const menu = document.getElementById('navbarMenu');
        menu.classList.toggle('active');
    }
    </script>
</body>
</html>
