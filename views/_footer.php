<?php
/**
 * 信鸽之家 - 统一页脚（B方案）
 * 所有页面在 </body> 前引用：<?php include __DIR__ . '/_footer.php'; ?>
 */
?>

<div style="--primary:#1a5fa8;--primary-color:#1a5fa8;--accent:#c9a84c;--secondary-color:#c9a84c;--white:#ffffff;--border:#e8ecf0;--text:#2c3e50;--text-light:#6c7a89;">
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <h3><i class="fas fa-dove"></i> <?php echo h(SITE_NAME); ?></h3>
                <p>专业赛鸽数据平台，为鸽友提供足环查询、血统证书、公棚对比、赛事资讯等服务。收录全国 <?php echo number_format($stats['lofts'] ?? 0); ?> 家公棚 · <?php echo number_format($stats['races'] ?? 0); ?> 场赛事数据。</p>
                <div class="friend-links" style="margin-top:10px;font-size:13px;padding-top:10px;border-top:1px dashed #e8ecf0;">
                    <span style="color:#6c7a89;">实用链接：</span>
                    <a href="/geohub/" style="color:#1a5fa8;text-decoration:none;">GeoHub</a>
                    <span style="color:#ccc;"> · </span>
                    <a href="/tags/" style="color:#1a5fa8;text-decoration:none;">标签</a>
                    <span style="color:#ccc;"> · </span>
                    <a href="/pigeon/create/" style="color:#1a5fa8;text-decoration:none;">发布铭鸽</a>
                </div>
            </div>

            <div class="footer-col">
                <h4>赛鸽数据</h4>
                <ul>
                    <li><a href="/race/champion/">冠军鸽专题</a></li>
                    <li><a href="/race/city/">城市赛事</a></li>
                    <li><a href="/race/province/">省份聚合</a></li>
                    <li><a href="/tools/top100/">分速 TOP100</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>关于</h4>
                <ul>
                    <li><a href="/pages/about/">关于我们</a></li>
                    <li><a href="/pages/contact/">联系我们</a></li>
                    <li><a href="/pages/ad/">广告合作</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>帮助支持</h4>
                <ul>
                    <li><a href="/pages/help/">帮助中心</a></li>
                    <li><a href="/pages/faq/">常见问题</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> <?php echo h(SITE_NAME); ?>. All Rights Reserved.
            <?php if (defined('SITE_ICP') && SITE_ICP): ?>
            <br><a href="https://beian.miit.gov.cn/" target="_blank" rel="nofollow"><?php echo h(SITE_ICP); ?></a>
            <?php endif; ?>
            </p>
        </div>
    </div>
</footer>

<?php if (defined('BAIDU_TONGJI_ID') && BAIDU_TONGJI_ID): ?>
<!-- 百度统计 -->
<script>
var _hmt = _hmt || [];
(function() {
  var hm = document.createElement("script");
  hm.src = "https://hm.baidu.com/hm.js?<?php echo h(BAIDU_TONGJI_ID); ?>";
  var s = document.getElementsByTagName("script")[0]; 
  s.parentNode.insertBefore(hm, s);
})();
</script>
<?php endif; ?>
</div>