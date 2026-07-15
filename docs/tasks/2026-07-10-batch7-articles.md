# 2026-07-10: Batch 7 SEO 文章生成

## 产出文件
- **SQL**: `scripts/_gen_articles_batch7.sql` (6102 bytes)
- **封面**:
  - `public/images/articles/article_b7_51.svg` - 幼鸽开家最全攻略
  - `public/images/articles/article_b7_52.svg` - 呼吸道问题全解析
  - `public/images/articles/article_b7_53.svg` - 赛鸽配对实战经验

## 文章列表
| 编号 | 标题 | 分类 | 推荐首页 |
|------|------|------|----------|
| 51 | 幼鸽开家最全攻略-什么时候开家、怎么开家、开家丢鸽怎么办 | 养鸽知识(19) | ✅ |
| 52 | 赛鸽呼吸道问题全解析-症状识别、用药选择、预防要点 | 养鸽知识(19) | - |
| 53 | 赛鸽配对实战经验-选配原则、配对方法、育雏管理 | 养鸽知识(19) | ✅ |

## 部署步骤
1. 上传 SQL 文件到服务器
2. 执行 SQL：`mysql -u root -p xgjia < _gen_articles_batch7.sql`
3. 上传 3 个 SVG 封面到 `/www/wwwroot/xgjia.com/images/articles/`
4. 运行 `_setup_tags.php` 关联标签
5. 验证文章列表页和详情页

## 累计文章统计
- Batch 1-6: 50 篇
- Batch 7: 3 篇
- **总计**: 53 篇
