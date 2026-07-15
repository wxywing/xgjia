# 版本标记 — v20260529-navbar-unified

**标记时间**：2026-05-29 18:16 (Asia/Shanghai)
**备份文件**：`xgjia-website-v20260529-navbar-unified.zip` (2.4MB)

---

## 本版本主要改动

### 架构改进：导航栏/页脚组件化变量作用域

**核心方案**：在 `_head.php` 和 `_footer.php` 中使用 wrapper `<div style="--var:value">` 包裹，将 B方案 CSS 变量作用域限定在导航栏和页脚内。CSS 自定义属性按元素继承层级解析——wrapper 内的 `var(--primary)` 取 B方案值，外层页面内容取各自 `:root` 的值。

### 文件变更清单

| 文件 | 操作 | 说明 |
|------|------|------|
| `views/_head.php` | 修改 | +搜索框(搜索/分享/copy) + wrapper div(B方案变量scope) |
| `views/_footer.php` | 修改 | +wrapper div(B方案变量scope) |
| `views/article.php` | 重写 | B方案丰富版：进度条+作者卡+标签+分享+目录+相关推荐 |
| `views/pigeon.php` | 修改 | 修复布局错乱：补 detail-layout 网格容器+left-col/right-col |
| `views/loft.php` | 重写 | B方案丰富版：hero banner+快捷操作+倒计时+相册+丰富侧边栏 |
| `views/dynamics.php` | 重写 | B方案两栏布局+热门话题/推荐用户/标签侧边栏 |
| `views/shop.php` | 恢复 | `:root` 保持展厅原始变量，不依赖 B方案变量 |

### B方案 CSS 变量标准

```css
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
```

### 导航栏 wrapper scope

`_head.php` 内 wrapper div 设置全部 9 个 B方案变量：
`--primary`, `--primary-dark`, `--accent`, `--white`, `--bg`, `--text`, `--text-light`, `--border`, `--shadow`

`_footer.php` 内 wrapper div 设置 6 个 B方案变量：
`--primary`, `--accent`, `--white`, `--border`, `--text`, `--text-light`

### 待服务器同步

所有修改的文件需上传至 `www.xgjia.com` 并 reload nginx：
- `views/_head.php`
- `views/_footer.php`
- `views/article.php`
- `views/pigeon.php`
- `views/loft.php`
- `views/dynamics.php`
