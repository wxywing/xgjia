<?php
/**
 * 信鸽之家 - 发布分类信息
 */

require_once dirname(__DIR__) . '/app/config/config.php';

// 检查登录
if (!isset($_SESSION['user_id'])) {
    header('Location: /login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// $data 由 Controller::loadView() 提取（如果有）
if (isset($data)) {
    extract($data);
}

$page_title = $pageTitle ?? '发布分类信息 | ' . SITE_NAME;
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta property="og:title" content="发布信息 - 信鸽之家">
    <meta property="og:description" content="在信鸽之家发布出售、求购、转让等分类信息，快速触达全国鸽友。">
    <meta property="og:image" content="https://www.xgjia.com/public/images/og-cover.png">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="信鸽之家">
    <link rel="canonical" href="https://www.xgjia.com/listing/create">

    <title><?php echo h($page_title); ?></title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="description" content="<?php echo SITE_DESCRIPTION; ?>">
    <meta name="keywords" content="<?php echo SITE_KEYWORDS; ?>">
    
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/b-scheme.css">
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="/public/images/favicon.ico">
    
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

        /* 发布页样式 */
        .publish-header {
            background: linear-gradient(135deg, var(--primary) 0%, #1e40af 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 30px;
        }
        
        .publish-form {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: var(--gray-700);
        }
        
        .form-label .required {
            color: var(--danger-color);
        }
        
        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--gray-300);
            border-radius: var(--border-radius);
            font-size: 15px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }
        
        .form-hint {
            font-size: 13px;
            color: var(--gray-500);
            margin-top: 5px;
        }
        
        .image-upload-area {
            border: 2px dashed var(--gray-300);
            border-radius: var(--border-radius);
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .image-upload-area:hover {
            border-color: var(--primary);
            background-color: var(--gray-50);
        }
        
        .image-upload-area i {
            font-size: 48px;
            color: var(--gray-400);
            margin-bottom: 15px;
        }
        
        .image-preview-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }
        
        .image-preview {
            position: relative;
            width: 100px;
            height: 100px;
        }
        
        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: var(--border-radius);
        }
        
        .image-preview .remove-btn {
            position: absolute;
            top: -5px;
            right: -5px;
            width: 24px;
            height: 24px;
            background-color: var(--danger-color);
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            font-size: 12px;
        }
        
        .price-input-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .price-input-group input {
            flex: 1;
        }
        
        .price-input-group span {
            color: var(--gray-600);
        }
        
        .submit-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--gray-200);
        }
        
        @media (max-width: 768px) {
            .submit-actions {
                flex-direction: column;
                gap: 15px;
            }
        }
        
        .vip-notice {
            background-color: #fef3c7;
            border: 1px solid #fbbf24;
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/_head.php'; ?>
<!-- 发布头部 -->
    <div class="publish-header">
        <div class="container">
            <h1 class="text-3xl font-bold"><i class="fas fa-plus-circle mr-2"></i>发布分类信息</h1>
            <p class="mt-2">发布您的信鸽转让、鸽具用品等信息</p>
        </div>
    </div>

    <!-- 主内容区 -->
    <div class="container">
        <div class="publish-form">
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
                <span style="color: #1e40af; font-size: 14px;">本月剩余发布额度：<strong><?php echo $publish_check['remaining']; ?></strong> 次</span>
                <a href="/user/membership" style="color: #2563eb; font-size: 13px; margin-left: auto;">升级额度 →</a>
            </div>
            <?php else: ?>
            <!-- VIP提示 -->
            <div class="vip-notice">
                <i class="fas fa-crown mr-2"></i>
                <strong>VIP会员特权</strong>
                <p class="text-sm mt-1">升级VIP会员可发布更多信息，享受更多特权</p>
                <a href="/user/membership" class="btn btn-warning btn-sm mt-2">
                    <i class="fas fa-arrow-up mr-1"></i>立即升级
                </a>
            </div>
            <?php endif; ?>

            <?php if (!isset($publish_check) || $publish_check['allowed']): ?>
            <form id="publishForm" method="POST" action="/listing/?action=create" enctype="multipart/form-data">
                <!-- 信息类型 -->
                <div class="form-group">
                    <label class="form-label">
                        信息类型 <span class="required">*</span>
                    </label>
                    <select name="type" class="form-control" required>
                        <option value="">请选择类型</option>
                        <option value="1">出售</option>
                        <option value="2">求购</option>
                        <option value="3">转让</option>
                        <option value="4">配对</option>
                    </select>
                </div>

                <!-- 标题 -->
                <div class="form-group">
                    <label class="form-label">
                        标题 <span class="required">*</span>
                    </label>
                    <input type="text" name="title" class="form-control" 
                           placeholder="请输入信息标题，例如：优质信鸽转让" 
                           maxlength="100" required>
                    <div class="form-hint">最多100个字符</div>
                </div>

                <!-- 图片上传 -->
                <div class="form-group">
                    <label class="form-label">上传图片</label>
                    <div class="image-upload-area" onclick="document.getElementById('imageInput').click()">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p>点击上传图片</p>
                        <p class="text-sm text-gray">支持 jpg、png 格式，最多5张</p>
                    </div>
                    <input type="file" id="imageInput" name="images[]" 
                           accept="image/jpeg,image/png" multiple style="display: none;">
                    <div class="image-preview-list" id="imagePreviewList"></div>
                </div>

                <!-- 地区 -->
                <div class="form-group">
                    <label class="form-label">
                        所在地区 <span class="required">*</span>
                    </label>
                    <input type="text" name="location" class="form-control" 
                           placeholder="例如：广东省广州市" required>
                </div>

                <!-- 价格 -->
                <div class="form-group">
                    <label class="form-label">价格</label>
                    <div class="price-input-group">
                        <input type="number" name="price" class="form-control" 
                               placeholder="请输入价格" min="0" step="0.01">
                        <span>元</span>
                    </div>
                    <div class="form-hint">留空表示价格面议</div>
                </div>

                <!-- 联系方式 -->
                <div class="form-group">
                    <label class="form-label">
                        联系人 <span class="required">*</span>
                    </label>
                    <input type="text" name="contact_name" class="form-control" 
                           placeholder="请输入联系人姓名" required>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        联系电话 <span class="required">*</span>
                    </label>
                    <input type="tel" name="contact_phone" class="form-control" 
                           placeholder="请输入联系电话" required>
                </div>

                <div class="form-group">
                    <label class="form-label">微信号</label>
                    <input type="text" name="contact_wechat" class="form-control" 
                           placeholder="请输入微信号（选填）">
                </div>

                <!-- 详细描述 -->
                <div class="form-group">
                    <label class="form-label">详细描述</label>
                    <textarea name="description" class="form-control" 
                              placeholder="请详细描述您的信息，例如信鸽的血统、成绩、健康情况等"></textarea>
                </div>

                <!-- 提交按钮 -->
                <div class="submit-actions">
                    <a href="/listing/" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i>返回
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane mr-1"></i>发布信息
                    </button>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- 图片预览 JavaScript -->
    <script>
        document.getElementById('imageInput').addEventListener('change', function(e) {
            var files = e.target.files;
            var previewList = document.getElementById('imagePreviewList');
            
            for (var i = 0; i < files.length; i++) {
                var file = files[i];
                var reader = new FileReader();
                
                reader.onload = function(e) {
                    var div = document.createElement('div');
                    div.className = 'image-preview';
                    div.innerHTML = '<img loading="lazy" src="' + e.target.result + '" alt="预览图"><button class="remove-btn" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>';
                    previewList.appendChild(div);
                };
                
                reader.readAsDataURL(file);
            }
        });
    </script>

    <?php include __DIR__ . '/_footer.php'; ?>
