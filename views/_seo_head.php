<?php
/**
 * SEO 头部元信息辅助文件（专业版）
 * 
 * SEO最佳实践：
 * - 标题：30-60字符，品牌+核心关键词，重点在前25字符
 * - 描述：120-160字符，包含关键词，有吸引力，引导点击
 * - 关键词：3-5个核心词，不堆砌，符合搜索意图
 * 
 * 使用方法：
 * 在页面顶部设置以下变量后引入：
 *   $page_title - 页面标题（不含站点名）
 *   $page_title_full - 完整标题（覆盖默认格式）
 *   $meta_description - 页面描述
 *   $meta_keywords - 页面关键词
 *   $og_type - Open Graph 类型
 *   $og_image - 分享图片 URL
 *   $canonical_url - 规范链接
 *   $robots - robots 指令（完全自定义，覆盖默认值）
 *   $noindex - 是否禁止索引
 *   $ld_json - 结构化数据数组
 */

// 默认值设置
$page_title = $page_title ?? '';
$page_title_full = $page_title_full ?? null;
$meta_description = $meta_description ?? SITE_DESCRIPTION;
$meta_keywords = $meta_keywords ?? SITE_KEYWORDS;
$og_type = $og_type ?? 'website';
$og_image = $og_image ?? 'https://www.xgjia.com/public/images/og-cover.png';
$canonical_url = $canonical_url ?? ('https://www.xgjia.com' . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$noindex = $noindex ?? false;
$robots = $robots ?? null;
$ld_json = $ld_json ?? null;

// 标题格式化：核心内容 | 品牌（控制在60字符内）
$final_title = $page_title_full ?? ($page_title ? $page_title . ' | 信鸽之家' : '信鸽之家 - 公棚查询·铭鸽展厅·血统图谱');

// 描述长度控制（120-160字符最佳）
if (mb_strlen($meta_description) > 160) {
    $meta_description = mb_substr($meta_description, 0, 157) . '...';
}
?>
    <title><?php echo h($final_title); ?></title>
    
    <!-- SEO Core Meta -->
    <meta name="description" content="<?php echo h($meta_description); ?>">
    <meta name="keywords" content="<?php echo h($meta_keywords); ?>">
    <link rel="canonical" href="<?php echo h($canonical_url); ?>">
    <link rel="search" type="application/opensearchdescription+xml" title="信鸽之家" href="/opensearch.xml">
    
    <?php if ($robots !== null): ?>
    <meta name="robots" content="<?php echo h($robots); ?>">
    <?php elseif ($noindex): ?>
    <meta name="robots" content="noindex, follow">
    <?php else: ?>
    <meta name="robots" content="index, follow">
    <?php endif; ?>
    
    <!-- Open Graph (Facebook/微信分享) -->
    <meta property="og:type" content="<?php echo h($og_type); ?>">
    <meta property="og:title" content="<?php echo h($final_title); ?>">
    <meta property="og:description" content="<?php echo h($meta_description); ?>">
    <meta property="og:url" content="<?php echo h($canonical_url); ?>">
    <meta property="og:site_name" content="信鸽之家">
    <meta property="og:locale" content="zh_CN">
    <meta property="og:image" content="<?php echo h($og_image); ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@xgjia">
    <meta name="twitter:title" content="<?php echo h($final_title); ?>">
    <meta name="twitter:description" content="<?php echo h($meta_description); ?>">
    <meta name="twitter:image" content="<?php echo h($og_image); ?>">
    
    <!-- JSON-LD 结构化数据 -->
    <?php if ($ld_json): ?>
    <script type="application/ld+json"><?php echo json_encode($ld_json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
    <?php endif; ?>
