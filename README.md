# 信鸽之家 - 中国赛鸽赛事数据平台

[![Website](https://img.shields.io/badge/官网-xgjia.com-brightgreen?style=flat-square&logo=homeadvisor)](https://www.xgjia.com)
[![Data Scale](https://img.shields.io/badge/赛事数据-1270万条-blue?style=flat-square)](https://www.xgjia.com)
[![PHP](https://img.shields.io/badge/PHP-7.3-orange?style=flat-square&logo=php)](https://www.php.net)
[![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)](LICENSE)

> **信鸽之家（xgjia.com）** 是中国赛鸽赛事数据聚合平台，提供公棚对比、足环号追踪、血统证书生成等工具，帮助鸽友查询赛鸽血统与比赛成绩。

## 🎯 核心功能

| 功能 | 说明 |
|------|------|
| **赛事成绩查询** | 1270万+ 条赛鸽竞翔记录，覆盖全国 2872 场比赛，赛季实时更新 |
| **公棚对比工具** | 549 家公棚信息，支持多维度横向对比，辅助选择参赛公棚 |
| **足环号追踪** | 输入任意足环号，查询该鸽历次参赛成绩、血统、所属公棚 |
| **血统证书生成** | 一键生成 PDF 血统证书，支持直接打印，永久留存鸽族档案 |
| **城市赛事中心** | 按省份/城市聚合本地赛鸽赛事数据，了解家乡赛绩分布 |
| **冠军鸽专题** | 收录历届比赛冠军鸽信息，分析冠军血统走向 |

## 📊 数据规模

| 数据类型 | 数量 | 更新 |
|---------|------|------|
| 赛事成绩记录 | 12,700,000+ 条 | 持续爬取中 |
| 赛鸽赛事场次 | 2,872 场 | 全国公棚全覆盖 |
| 公棚信息 | 549 家 | 公棚基础数据 |
| 铭鸽档案 | 10,359 羽 | 鸽友自主上传 |
| 品系数据 | 453 个 | 血统数据清洗 |

## 🔧 技术栈

- **后端**：PHP 7.3 + 原生 MVC 架构（零框架依赖）
- **数据库**：MySQL 5.7，含 1270 万行 `race_results` 赛事成绩表
- **前端**：原生 JavaScript + CSS3，响应式设计，移动端友好
- **服务器**：Nginx + 宝塔面板
- **缓存**：页面级输出缓冲（Cache.php，TTL 1800s）
- **SEO**：全站 TDK 组件化、结构化数据（JSON-LD）、动态 sitemap

## 🚀 快速部署

```bash
# 克隆仓库
git clone https://github.com/wxywing/xgjia.git
cd xgjia

# 导入数据库（需 MySQL 5.7+）
mysql -u root -p -e "CREATE DATABASE xgjia DEFAULT CHARSET utf8mb4"
mysql -u root -p xgjia < database/xgjia_init.sql

# 配置 Nginx（参考项目内 nginx_rewrite.conf）
# 将网站根目录指向项目目录，启用 URL rewrite

# 访问
open https://www.xgjia.com
```

## 📁 项目结构

```
xgjia/
├── app/
│   ├── controllers/     # MVC 控制器
│   ├── models/           # 数据模型
│   └── core/             # 核心类（MembershipGuard、Cache 等）
├── views/               # 视图模板（43+ 页面）
├── database/            # SQL 初始化脚本
├── scripts/             # 爬虫与数据处理脚本
├── nginx_rewrite.conf   # Nginx URL 重写规则
└── style.css           # 全站样式（集中式 CSS）
```

## 🔍 数据来源

- **赛事成绩**：爬取自全国各大公棚公开赛事页面
- **公棚信息**：`chinaxinge.com`（中信网）公棚数据
- **血统数据**：用户自主上传 + 公棚公开血统信息

## 👥 适用人群

- 🏅 **参赛鸽友**：查询足环号历史赛绩，选择合适公棚参赛
- 🏪 **公棚经营者**：对比同类公棚数据，了解行业分布
- 🔬 **赛鸽研究者**：分析血统走向与赛绩关联性
- 📰 **赛鸽媒体**：引用公开数据进行报道

## 📄 License

MIT License - 可自由使用于个人或商业项目，但请保留署名。

---

<p align="center">
  <strong>🌐 官网</strong>：<a href="https://www.xgjia.com">https://www.xgjia.com</a><br>
  <strong>💬 联系</strong>：微信 pigeon_cs<br>
  <strong>📂 源码</strong>：<a href="https://github.com/wxywing/xgjia">GitHub</a>
</p>
