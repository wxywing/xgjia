# P2 SEO 优化 #3~#5: 404增强 + ItemList全站 + b-scheme.css瘦身

## 目标
三项待办同时执行，2026-06-11 12:57 开始。

## 结果

### 1. 404 页面增强 ✅
**文件**: `views/404.php`

新增内容：
- JSON-LD WebSite schema（含 SearchAction）
- 搜索表单（`/search?q=`）
- 7 个推荐链接：首页/铭鸽/公棚/资讯/鸽舍/赛事/血统

### 2. ItemList Schema 全站覆盖 ✅
10/10 列表页全部就绪：

| 页面 | 状态 | Items 来源 |
|------|------|-----------|
| pigeons | 已有 | pigeons[0:10] |
| articles | 已有 | articles[0:10] |
| shops | 已有 | shops[0:10] |
| listings | 已有 | listings[0:10] |
| strains | 已有 | strains[0:10] |
| **lofts** | **新增** | lofts[0:10] |
| **showrooms** | **新增** | showrooms[0:10] |
| **dynamics** | **新增** | dynamics[0:10] |
| **search** | **新增** | results异构（×3类，各5条）|
| **races** | **新增** | recentRaces[0:10] |

search.php 特殊处理：`$results` 是 `['articles'=>[], 'pigeons'=>[], 'lofts'=>[]]` 三维数组，取每类前5条共15条，截断至10。

### 3. b-scheme.css 瘦身 ✅
**文件**: `public/css/b-scheme.css`
- 备份：`b-scheme.css.bak`
- 119KB → 76KB（**减 36%，490行**）
- 括号平衡：555/555 ✅

删除三段死代码：
| 段 | 行号 | 内容 | 行数 | 原因 |
|----|------|------|------|------|
| S1 | 764-929 | 首页 v6 增强样式 | 166 | 首页用 v7-home.css，不加载 b-scheme.css |
| S2 | 1115-1210 | `.page-lofts` 全套 | 96 | 0 views 使用（lofts.php 的 wrapper 无 page-lofts 类）|
| S3 | 2124-2351 | HOMEPAGE V6 LAYOUT OVERRIDES | 228 | 同上，纯 `.page-home` 规则 |

关键发现：line 2123 是 `.page-shop` 媒体查询的 `}`，删除时保留。

## 技术笔记
- b-scheme.css 用 Python 后→前删除保证行号不变
- search.php 的 `$results` 结构需先判断各键存在再 slice
- 404.php 原是委托模式（根目录 404.php → views/404.php），已保留
