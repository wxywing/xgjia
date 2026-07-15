# 2026-07-09 P1完成 + Batch 6 赛事新闻文章生成

## P1 功能实现（已完成 3/4）

| 功能 | 文件 | 状态 |
|------|------|------|
| 赛事页一键复制足环号 | `views/race_detail.php` | ✅ |
| 文章详情上下篇导航 | `app/controllers/ArticleController.php` | ✅ |
| 手机端铭鸽详情页排版 | `views/pigeon.php` | ✅ |

## Batch 6 — 赛事新闻方向 × 3篇

**分类**: 赛事新闻 (category_id=1, slug=saishi)
**内容风格**: 数据驱动、信息密度高、平台内链

| 文章 | 标题 | 概述 |
|------|------|------|
| 1 | 2026年全国公棚赛程一览-各大赛区春棚秋棚时间表 | 四大区赛程节奏分析，内链平台赛事库 |
| 2 | 公棚成绩单怎么看？用足环号三步查清鸽子全部赛绩 | 教程型，推广足环号查询+鸽主专辑功能 |
| 3 | 公棚竞争激烈排名-参赛羽数与录取率全面分析 | 数据型，分析各区竞争烈度差异 |

**文件**: `scripts/_gen_articles_batch6.sql` + 3个SVG封面

## 待上传服务器

1. `views/race_detail.php` — 复制足环号
2. `app/controllers/ArticleController.php` — 文章上下篇
3. `views/pigeon.php` — 手机端优化
4. `scripts/_gen_articles_batch6.sql` — 3篇新文章
5. `public/images/articles/article_b6_{01..03}.svg` — 封面
