/**
 * 百度统计代码
 * 
 * 使用方法：<script src="/public/js/baidu_tongji.js"></script>
 * 修改统计ID：修改 app/config/config.php 中的 BAIDU_TONGJI_ID
 */

// 百度统计异步代码
var _hmt = _hmt || [];
(function() {
    var hm = document.createElement("script");
    hm.src = "https://hm.baidu.com/hm.js?" + (typeof BAIDU_TONGJI_ID !== 'undefined' ? BAIDU_TONGJI_ID : '');
    var s = document.getElementsByTagName("script")[0];
    s.parentNode.insertBefore(hm, s);
})();
