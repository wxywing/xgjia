# Geo Sitemap 实现 — 2026-07-03

## 任务目标
为公棚详情页生成 Geo Sitemap（Google Geo Sitemap 格式），让 Google 地图能索引公棚地理位置数据。

## 完成内容

### sitemap.php 修改（7处）

1. **buckets init**（line 42）：添加 `'geo' => []`

2. **geo sitemap 查询**（lines 193-215）：在 lofts 桶之后新增查询
   - 筛选：`status=1 AND lat/lng 非空非零`
   - 坐标范围校验：lat ∈ [-90,90]、lng ∈ [-180,180]
   - 每条记录存入 `$buckets['geo'][]`（含 loc/lat/lng/lastmod/priority）

3. **build_geo_xml() 函数**（line 260）：新增 geo sitemap XML 构建函数
   - 命名空间：`xmlns:geo="http://www.google.com/geo/schemas/sitemap/1.0"`
   - 输出：`<geo:lat>` + `<geo:lng>` 子元素

4. **build_index() foreach 跳过 geo**（line 307）：`if ($name === 'geo') continue;`

5. **build_index() standalone geo 入口**（lines 318-323）：在常规 sitemap 入口之后单独添加 geo 入口

6. **generate block 跳过 geo + 调用 build_geo_xml()**（lines 344, 354-358）：
   - 常规 `foreach` 跳过 geo：`if ($name === 'geo') continue;`
   - geo sitemap 单独写入 `sitemap_geo.xml`

7. **动态 geo 输出**（lines 394-395）：`type=geo` 时调用 `build_geo_xml()` 而非 `build_xml()`

### 数据库
- `lofts` 表已有 `lat` (decimal(10,6)) 和 `lng` (decimal(10,6)) 列，无需迁移

### 无需 nginx 配置
- geo sitemap 为静态 XML 文件，路径 `/sitemap_geo.xml`，直接由 Nginx 静态文件服务
- sitemap_index.xml 自动包含 geo sitemap 入口

### 输出文件
- `sitemap_geo.xml` — Geo Sitemap（公棚坐标列表）
- `sitemap_index.xml` — 更新后包含 geo 入口

## 部署方式
```bash
php sitemap.php --generate
# 或
curl "https://www.xgjia.com/sitemap.php?generate=1&key=xgjia_sitemap_2026"
```

## 搜索引擎提交
Geo Sitemap 无需特殊提交渠道，Google 会自动从 `sitemap_index.xml` 读取。
如需手动告知 Google：
- Google Search Console → Sitemaps → 提交 `sitemap_geo.xml`
