# 信鸽之家 - 中国赛鸽数据平台

> 官网：https://www.xgjia.com

赛鸽赛事成绩查询 | 公棚对比 | 足环号追踪 | 血统证书生成

## 功能

- **赛事成绩查询**：1270万+ 条赛事数据，覆盖全国 2872 场比赛
- **公棚对比工具**：549 家公棚信息，支持多维度对比分析
- **足环号追踪**：输入足环号查询赛绩、血统、参赛记录
- **血统证书生成**：一键生成 PDF 血统证书，支持打印
- **城市赛事中心**：按省份/城市聚合本地赛事数据

## 数据规模

| 数据类型 | 数量 |
|---------|------|
| 赛事成绩 | 12,700,000+ |
| 赛事场次 | 2,872 |
| 公棚信息 | 549 |
| 铭鸽档案 | 10,359 |
| 品系数据 | 453 |

## 技术栈

- 后端：PHP 7.3 + MySQL 5.7
- 前端：原生 JavaScript + CSS3
- 服务器：Nginx + 宝塔面板
- 架构：手搓 MVC（无框架）

## 快速开始

```bash
# 克隆仓库
git clone https://github.com/wxywing/xgjia.git

# 导入数据库
mysql -u root -p xgjia < database/xgjia.sql

# 配置 Nginx
# 参考 nginx_rewrite.conf
```

## License

MIT License

---

**官网**：https://www.xgjia.com  
**GitHub**：https://github.com/wxywing/xgjia
