<?php
/**
 * 信鸽之家 - 编辑展厅
 */
require_once dirname(__DIR__) . '/app/config/config.php';

extract($data);

$page_title = '编辑展厅 | ' . SITE_NAME;
$noindex = true;
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($page_title); ?></title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/b-scheme.css">
    <style>
        :root {
            --primary: #1a5fa8;
            --primary-light: #2980b9;
            --primary-dark: #154360;
            --accent: #c9a84c;
            --bg: #f4f6f9;
            --white: #ffffff;
            --text: #2c3e50;
            --text-light: #6c7a89;
            --border: #e8ecf0;
            --radius: 12px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "PingFang SC", "Microsoft YaHei", sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
            min-height: 100vh;
        }
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }

        .page-header {
            background: linear-gradient(135deg, var(--primary) 0%, #1e40af 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 30px;
            text-align: center;
        }
        .page-header h1 { font-size: 28px; margin-bottom: 8px; }
        .page-header p { font-size: 14px; opacity: 0.9; }

        .edit-form {
            background: white;
            border-radius: var(--radius);
            box-shadow: 0 2px 12px rgba(26,95,168,0.08);
            padding: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text);
        }
        .form-group label .required {
            color: #e74c3c;
            margin-left: 4px;
        }
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(26,95,168,0.1);
        }
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        @media (max-width: 600px) {
            .form-row { grid-template-columns: 1fr; }
        }

        .btn-group {
            display: flex;
            gap: 12px;
            margin-top: 30px;
        }
        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        .btn-primary:hover {
            background: var(--primary-light);
        }
        .btn-secondary {
            background: var(--border);
            color: var(--text);
        }
        .btn-secondary:hover {
            background: #d1d5db;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/_head.php'; ?>

    <div class="page-header">
        <div class="container">
            <h1><i class="fas fa-edit"></i> 编辑展厅</h1>
            <p>修改展厅基本信息</p>
        </div>
    </div>

    <div class="container">
        <div class="alert alert-success" id="alertSuccess"></div>
        <div class="alert alert-error" id="alertError"></div>

        <form class="edit-form" id="editForm">
            <input type="hidden" name="id" value="<?php echo intval($shop['id']); ?>">

            <div class="form-group">
                <label>展厅名称 <span class="required">*</span></label>
                <input type="text" name="name" class="form-control" value="<?php echo h($shop['name'] ?? ''); ?>" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>省份</label>
                    <input type="text" name="province" class="form-control" value="<?php echo h($shop['province'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>城市</label>
                    <input type="text" name="city" class="form-control" value="<?php echo h($shop['city'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-group">
                <label>详细地址</label>
                <input type="text" name="address" class="form-control" value="<?php echo h($shop['address'] ?? ''); ?>">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>联系电话</label>
                    <input type="text" name="phone" class="form-control" value="<?php echo h($shop['contact_phone'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>微信号</label>
                    <input type="text" name="wechat" class="form-control" value="<?php echo h($shop['wechat'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-group">
                <label>展厅简介</label>
                <textarea name="intro" class="form-control" placeholder="介绍展厅的特色、主要血系、历年成绩等"><?php echo h($shop['description'] ?? ''); ?></textarea>
            </div>

            <div class="btn-group">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> 保存修改</button>
                <a href="/shop/<?php echo intval($shop['id']); ?>.html" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> 返回</a>
            </div>
        </form>
    </div>

    <?php include __DIR__ . '/_footer.php'; ?>

    <script>
        document.getElementById('editForm').addEventListener('submit', function(e) {
            e.preventDefault();

            var form = this;
            var formData = new FormData(form);

            fetch('/shop?action=update', {
                method: 'POST',
                body: formData
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                var alertSuccess = document.getElementById('alertSuccess');
                var alertError = document.getElementById('alertError');

                if (data.success) {
                    alertError.style.display = 'none';
                    alertSuccess.textContent = data.message;
                    alertSuccess.style.display = 'block';
                    setTimeout(function() {
                        alertSuccess.style.display = 'none';
                    }, 3000);
                } else {
                    alertSuccess.style.display = 'none';
                    alertError.textContent = data.message;
                    alertError.style.display = 'block';
                }
            })
            .catch(function() {
                var alertError = document.getElementById('alertError');
                alertError.textContent = '网络错误，请稍后重试';
                alertError.style.display = 'block';
            });
        });
    </script>
</body>
</html>
