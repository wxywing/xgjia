<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="在信鸽之家发布您的铭鸽信息，填写血统、羽色、足环号等详情，触达全国鸽友买家。">
    <meta name="keywords" content="发布铭鸽,铭鸽出售,信鸽之家">
    <meta property="og:title" content="发布铭鸽 - 信鸽之家">
    <meta property="og:description" content="在信鸽之家发布您的铭鸽信息，填写血统、羽色、足环号等详情，触达全国鸽友买家。">
    <meta property="og:image" content="https://www.xgjia.com/public/images/og-cover.png">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="信鸽之家">
    <link rel="canonical" href="https://www.xgjia.com/pigeon/create">

    <title><?= $pageTitle ?? '发布铭鸽 | ' . SITE_NAME ?></title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/b-scheme.css">
</head>
<body>
    <?php include __DIR__ . '/_head.php'; ?>
    
    <div class="container" style="padding: 30px 0;">
        <div class="publish-page">
            <h1 class="page-title">发布铭鸽</h1>
            
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
            <div class="quota-warning" style="background: #eff6ff; border: 1px solid #3b82f6; border-radius: 8px; padding: 12px 16px; margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-info-circle" style="color: #3b82f6;"></i>
                <span style="color: #1e40af; font-size: 14px;">本月剩余发布额度：<strong><?php echo $publish_check['remaining']; ?></strong> 次</span>
                <a href="/user/membership" style="color: #2563eb; font-size: 13px; margin-left: auto;">升级额度 →</a>
            </div>
            <?php endif; ?>
            
            <?php if (!isset($publish_check) || $publish_check['allowed']): ?>
            <form id="pigeonForm" class="publish-form">
                <div class="form-section">
                    <h3>基本信息</h3>
                    
                    <div class="form-group">
                        <label>鸽子名称 <span class="required">*</span></label>
                        <input type="text" name="name" required placeholder="请输入鸽子名称">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>足环号</label>
                            <input type="text" name="ring_number" placeholder="例如：CHN-2024-123456">
                        </div>
                        <div class="form-group">
                            <label>血统</label>
                            <input type="text" name="bloodline" placeholder="例如：杨阿腾、詹森">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>性别</label>
                            <select name="gender">
                                <option value="0">未知</option>
                                <option value="1">雄</option>
                                <option value="2">雌</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>出生日期</label>
                            <input type="date" name="birth_date">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>羽色</label>
                            <input type="text" name="color" placeholder="例如：灰、雨点">
                        </div>
                        <div class="form-group">
                            <label>眼砂</label>
                            <input type="text" name="eye_color" placeholder="例如：黄眼、砂眼">
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3>描述与成绩</h3>
                    
                    <div class="form-group">
                        <label>鸽子描述</label>
                        <textarea name="description" rows="4" placeholder="详细描述这只鸽子的情况"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>比赛成绩</label>
                        <textarea name="achievements" rows="3" placeholder="列出主要比赛成绩，每行一条"></textarea>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3>图片上传</h3>
                    
                    <div class="form-group">
                        <label>鸽子照片（最多9张）</label>
                        <div class="image-upload-area" id="imageUpload">
                            <div class="upload-btn">
                                <i class="fas fa-plus"></i>
                                <span>添加图片</span>
                            </div>
                            <input type="file" accept="image/*" multiple style="display:none">
                        </div>
                        <div class="image-preview" id="imagePreview"></div>
                    </div>
                    
                    <div class="form-group">
                        <label>视频链接（可选）</label>
                        <input type="url" name="video" placeholder="支持B站、优酷等视频链接">
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-default" onclick="history.back()">取消</button>
                    <button type="submit" class="btn btn-primary">提交发布</button>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
    
    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <div>
                    <h3 class="footer-title">关于我们</h3>
                    <ul class="footer-links">
                        <li><a href="/pages/about/">网站介绍</a></li>
                        <li><a href="/pages/contact/">联系方式</a></li>
                        <li><a href="/pages/ad/">广告合作</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="footer-title">帮助中心</h3>
                    <ul class="footer-links">
                        <li><a href="/pages/help/">新手指南</a></li>
                        <li><a href="/pages/faq/">常见问题</a></li>
                        <li><a href="/pages/agreement/">用户协议</a></li>
                        <li><a href="/pages/privacy/">隐私政策</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="footer-title">联系我们</h3>
                    <ul class="footer-links">
                        <li><i class="fas fa-envelope mr-2"></i>admin@xgjia.com</li>
                        <li><i class="fas fa-clock mr-2"></i>9:00-18:00</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 <?php echo SITE_NAME; ?> 版权所有</p>
            </div>
        </div>
    </footer>
    
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
    
    .image-upload-area {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .upload-btn {
        width: 100px;
        height: 100px;
        border: 2px dashed #ddd;
        border-radius: 8px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: #999;
    }
    
    .upload-btn:hover {
        border-color: #1890ff;
        color: #1890ff;
    }
    
    .upload-btn i {
        font-size: 24px;
        margin-bottom: 5px;
    }
    
    .upload-btn span {
        font-size: 12px;
    }
    
    .image-preview {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 10px;
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
    
    .preview-item .delete-btn {
        position: absolute;
        top: 5px;
        right: 5px;
        width: 20px;
        height: 20px;
        background: rgba(0,0,0,0.5);
        color: #fff;
        border: none;
        border-radius: 50%;
        cursor: pointer;
    }
    
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
    }
    
    @media (max-width: 768px) {
        .form-row {
            flex-direction: column;
            gap: 0;
        }
    }
    </style>
    
    <script>
    let uploadedImages = [];
    
    // 图片上传
    document.querySelector('#imageUpload input').addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        if (uploadedImages.length + files.length > 9) {
            alert('最多上传9张图片');
            return;
        }
        
        files.forEach(file => {
            const reader = new FileReader();
            reader.onload = function(e) {
                uploadedImages.push(e.target.result);
                updateImagePreview();
            };
            reader.readAsDataURL(file);
        });
    });
    
    function updateImagePreview() {
        const preview = document.getElementById('imagePreview');
        preview.innerHTML = uploadedImages.map((img, index) => `
            <div class="preview-item">
                <img loading="lazy" src="${img}" alt="预览图">
                <button type="button" class="delete-btn" onclick="removeImage(${index})">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `).join('');
    }
    
    function removeImage(index) {
        uploadedImages.splice(index, 1);
        updateImagePreview();
    }
    
    // 表单提交
    document.getElementById('pigeonForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('images', JSON.stringify(uploadedImages));
        
        fetch('/pigeon/create', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.href = '/user?action=my_pigeons';
            } else {
                alert(data.message);
            }
        });
    });
    </script>
    <nav class="mobile-bottom-nav">
        <div class="nav-items">
            <div class="nav-item" onclick="location.href='/'"><i class="fas fa-home"></i><span>首页</span></div>
            <div class="nav-item" onclick="location.href='/article/'"><i class="fas fa-newspaper"></i><span>资讯</span></div>
            <div class="nav-item" onclick="location.href='/shop/'"><i class="fas fa-dove"></i><span>铭鸽</span></div>
            <div class="nav-item" onclick="location.href='/loft/'"><i class="fas fa-building"></i><span>公棚</span></div>
            <div class="nav-item" onclick="location.href='/dynamics/'"><i class="fas fa-comments"></i><span>鸽友圈</span></div>
        </div>
    </nav>

    <!-- JavaScript -->

    <?php include __DIR__ . '/_footer.php'; ?>
</body>
</html>
