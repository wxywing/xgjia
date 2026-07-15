<?php
/**
 * 信鸽之家 - 关于我们
 */
require_once __DIR__ . '/../../app/config/config.php';

$page_title = '关于我们';
$meta_description = '信鸽之家是专业的赛鸽数据查询平台，提供足环号赛绩查询、血统证书生成、公棚数据对比、深度赛事分析。基于1200万+条真实赛事记录。';
$meta_keywords = '关于信鸽之家,赛鸽数据平台,足环查询,血统证书,公棚对比,深度报告,信鸽,赛鸽';
$canonical_url = 'https://www.xgjia.com/about/';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include __DIR__ . '/../_seo_head.php'; ?>
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/b-scheme.css">
    <style>
        .page-hero {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 50%, #3b82f6 100%);
            padding: 48px 0 36px;
            color: #fff;
            text-align: center;
        }
        .page-hero h1 { font-size: 30px; font-weight: 700; margin-bottom: 8px; }
        .page-hero p { font-size: 16px; opacity: .8; }

        .about-section {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,.06);
            padding: 36px 40px;
            margin: 30px auto;
            max-width: 900px;
        }
        .about-section h2 {
            font-size: 22px;
            font-weight: 700;
            color: #1e3a8a;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .about-section h2 i { color: #f97316; }
        .about-section p {
            font-size: 15px;
            line-height: 1.85;
            color: #374151;
            margin-bottom: 14px;
        }
        .about-section ul {
            padding-left: 20px;
            margin-bottom: 14px;
        }
        .about-section li {
            font-size: 15px;
            line-height: 2;
            color: #374151;
        }
        .about-section li i { color: #10b981; margin-right: 6px; }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin: 24px 0;
        }
        .stat-card {
            text-align: center;
            padding: 20px 12px;
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            border-radius: 10px;
        }
        .stat-card .stat-number {
            font-size: 28px;
            font-weight: 800;
            color: #1e3a8a;
        }
        .stat-card .stat-label {
            font-size: 13px;
            color: #6b7280;
            margin-top: 4px;
        }

        .timeline {
            position: relative;
            padding-left: 30px;
            margin: 20px 0;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 8px;
            top: 0;
            bottom: 0;
            width: 3px;
            background: linear-gradient(180deg, #1e3a8a, #3b82f6);
            border-radius: 2px;
        }
        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -26px;
            top: 6px;
            width: 12px;
            height: 12px;
            background: #f97316;
            border-radius: 50%;
            border: 2px solid #fff;
            box-shadow: 0 0 0 3px rgba(249,115,22,.2);
        }
        .timeline-item .time {
            font-size: 13px;
            color: #6b7280;
            font-weight: 600;
        }
        .timeline-item .event {
            font-size: 15px;
            color: #374151;
            margin-top: 2px;
        }

        .tool-cards {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
            margin-top: 16px;
        }
        .tool-card {
            padding: 22px 20px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            transition: border-color .2s, box-shadow .2s;
        }
        .tool-card:hover {
            border-color: #1e3a8a;
            box-shadow: 0 4px 12px rgba(30,58,138,.1);
        }
        .tool-card i {
            font-size: 24px;
            color: #1e3a8a;
            display: block;
            margin-bottom: 8px;
        }
        .tool-card h4 { font-size: 16px; color: #1e3a8a; margin-bottom: 6px; }
        .tool-card p { font-size: 13px; color: #6b7280; margin: 0; line-height: 1.6; }

        @media (max-width: 768px) {
            .stat-grid { grid-template-columns: repeat(2, 1fr); }
            .tool-cards { grid-template-columns: 1fr; }
            .about-section { padding: 24px 20px; margin: 16px 12px; }
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/../_head.php'; ?>

<div class="page-hero">
    <div class="container">
        <h1><i class="fas fa-info-circle"></i> 关于我们</h1>
        <p>赛鸽数据查询平台 — 用数据看懂每一羽鸽子</p>
    </div>
</div>

<div class="container" style="padding-bottom:60px;">

    <div class="about-section">
        <h2><i class="fas fa-home"></i> 平台介绍</h2>
        <p>信鸽之家（www.xgjia.com）是一个专注于赛鸽数据查询与分析的工具平台。我们不生产内容——我们整合、清洗、呈现最全面的赛鸽数据，帮助鸽友用数据做决策。</p>
        <p>平台核心能力：<strong>足环号深度赛绩查询</strong>（一键获取鸽子全部参赛记录）、<strong>血统证书自动生成</strong>（HTML模板即时生成可打印证书）、<strong>公棚数据对比</strong>（并排比较全国550+公棚的关键指标）。</p>
        <p>所有数据来源于公开赛事记录，经过清洗和结构化处理。从预赛到决赛，从单关到多关，从赛季统计到同批次对比——你关心的数据，我们都整理好了。</p>
    </div>

    <div class="about-section">
        <h2><i class="fas fa-chart-bar"></i> 数据规模</h2>
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-number">1,270万+</div>
                <div class="stat-label">赛绩记录</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">550+</div>
                <div class="stat-label">收录公棚</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">10,000+</div>
                <div class="stat-label">铭鸽数据</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">450+</div>
                <div class="stat-label">品系分类</div>
            </div>
        </div>
    </div>

    <div class="about-section">
        <h2><i class="fas fa-th-large"></i> 核心功能</h2>
        <div class="tool-cards">
            <div class="tool-card">
                <i class="fas fa-search"></i>
                <h4>足环号深度报告</h4>
                <p>输入足环号，一键生成完整赛绩报告：赛季统计、关赛明细、分速分析、同批次对比、鸽主成绩。部分免费，完整报告 ¥9.9。</p>
            </div>
            <div class="tool-card">
                <i class="fas fa-certificate"></i>
                <h4>血统证书生成</h4>
                <p>选一只鸽子，填写足环号，自动生成可打印的血统证书。含基本信息、最佳成绩、品系归属，支持 PDF 打印导出。</p>
            </div>
            <div class="tool-card">
                <i class="fas fa-balance-scale"></i>
                <h4>公棚数据对比</h4>
                <p>任选多个公棚并排比较：参赛费用、收鸽数量、归巢率、奖金池等关键指标一目了然，帮你在选棚时做出明智决策。</p>
            </div>
            <div class="tool-card">
                <i class="fas fa-trophy"></i>
                <h4>赛事数据看板</h4>
                <p>按赛季、地区、类别浏览赛事数据，查看冠军鸽专题、城市赛事中心、赛季总结。所有数据均可按分速、排名多维度筛选。</p>
            </div>
            <div class="tool-card">
                <i class="fas fa-project-diagram"></i>
                <h4>品系成绩分析</h4>
                <p>按品系查看该品种赛鸽的历史成绩表现，了解不同品系的竞赛特点，辅助引种和配对决策。</p>
            </div>
            <div class="tool-card">
                <i class="fas fa-dove"></i>
                <h4>铭鸽展厅</h4>
                <p>浏览 10,000+ 羽铭鸽的详细信息：足环号、羽色、眼砂、品系、血统。支持按品系、鸽主、足环号多维度搜索。</p>
            </div>
        </div>
    </div>

    <div class="about-section">
        <h2><i class="fas fa-road"></i> 发展历程</h2>
        <div class="timeline">
            <div class="timeline-item">
                <div class="time">2026年6月</div>
                <div class="event">完成 1,270 万条赛事成绩数据导入与中文编码修复，上线公棚对比工具、足环深度报告付费版</div>
            </div>
            <div class="timeline-item">
                <div class="time">2026年5月</div>
                <div class="event">信鸽之家正式上线，完成公棚 550+ 条、铭鸽 10,000+ 条数据采集，上线血统证书生成器</div>
            </div>
            <div class="timeline-item">
                <div class="time">2026年4月</div>
                <div class="event">完成赛事数据全量爬虫开发（2,872 场），足环号标准化处理，品系数据库建立（450+）</div>
            </div>
            <div class="timeline-item">
                <div class="time">2026年1月</div>
                <div class="event">项目启动，确定"数据工具平台"的产品方向——让数据服务于每一位鸽友</div>
            </div>
        </div>
    </div>

    <div class="about-section">
        <h2><i class="fas fa-bullseye"></i> 我们的定位</h2>
        <p>信鸽之家不是一个内容门户，也不是一个论坛社区。<strong>我们是一个数据工具。</strong></p>
        <p>在赛鸽行业，信息散落在数百个公棚网站、赛事公告、Excel 表格中。鸽友要查询一只鸽子的成绩，需要翻遍多个网站，拼凑零散数据。我们做的事情很简单：<strong>把所有公开的赛鸽数据收拢到一个地方，让你搜一下就知道。</strong></p>
        <p>如果你需要查成绩、做对比、出证书——来这里。如果你需要聊天、刷帖、看资讯——那些平台更合适。我们只做工具，并且把工具做好。</p>
    </div>

</div>

<?php include __DIR__ . "/../_footer.php"; ?>
</body>
</html>