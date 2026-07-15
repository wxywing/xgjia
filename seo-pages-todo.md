# SEO 内容页入口 — 待实施

> 2026-06-12 制定，择期执行

## 背景

`previews/` 下 6 个 SEO 预览页面质量较高，需安排入口上线。

## 嵌入式增强（P0 — 改动最小）

| 页面 | 实施方式 |
|------|----------|
| loft-detail-enhanced | 将赛事列表+历年冠军+说明文模块嵌入 `/loft/{id}.html` |
| ring-trace | `/race/` 搜索框加「足环号查询」→ 跳足环成绩时间线页 |
| owner-champion | 赛绩表中鸽主名改为可点击链接，跳鸽主专辑页 |

## 独立入口（P1-P2）

| 页面 | 入口位置 |
|------|----------|
| loft-summary | 公棚详情页底部加「查看赛季总结 →」链接 |
| region-hebei | 公棚列表页顶部省份 tabs（选河北→地区聚合页） |
| race-analysis | 首页精选推荐位 |

## 建议 URL 结构

```
/page/analysis/{loft_id}     → 赛事深度分析
/page/summary/{loft_id}      → 公棚赛季总结
/page/region/{province}      → 地区聚合
/page/owner/{owner_name}     → 鸽主专辑
```

走 nginx rewrite 统一路由，sitemap 自动收录。

## 相关文件

- 预览源码：`previews/*.html`（6 个）
- 方案文档：`seo-html-previews-20260612_task.md`
