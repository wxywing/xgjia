<?php
/**
 * 信鸽之家 - 帮助中心
 */
require_once __DIR__ . '/../../app/config/config.php';

$page_title = '帮助中心';
$meta_description = '信鸽之家帮助中心：如何使用足环号查询赛绩？怎么生成血统证书？公棚对比怎么用？快速上手指南和常见问题解答。';
$meta_keywords = '信鸽之家帮助,使用教程,足环查询,血统证书,公棚对比,深度报告,信鸽,赛鸽';
$canonical_url = 'https://www.xgjia.com/help/';
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

        .help-container {
            max-width: 900px;
            margin: 30px auto;
            padding-bottom: 60px;
        }

        .quick-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
            margin-bottom: 30px;
        }
        .quick-card {
            background: #fff;
            border-radius: 10px;
            padding: 20px 14px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
            transition: transform .2s, box-shadow .2s;
            text-decoration: none;
            color: inherit;
        }
        .quick-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(30,58,138,.12);
        }
        .quick-card i {
            font-size: 28px;
            color: #1e3a8a;
            margin-bottom: 8px;
            display: block;
        }
        .quick-card .quick-title {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
        }
        .quick-card .quick-desc {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }

        .faq-section {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,.06);
            margin-bottom: 24px;
            overflow: hidden;
        }
        .faq-section-header {
            padding: 20px 28px;
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            font-size: 18px;
            font-weight: 700;
            color: #1e3a8a;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .faq-section-header i { color: #f97316; }

        .faq-item {
            border-bottom: 1px solid #f3f4f6;
        }
        .faq-item:last-child { border-bottom: none; }
        .faq-question {
            padding: 16px 28px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 15px;
            font-weight: 500;
            color: #374151;
            transition: background .2s;
        }
        .faq-question:hover { background: #f9fafb; }
        .faq-question i { color: #9ca3af; transition: transform .3s; font-size: 12px; }
        .faq-question.open i { transform: rotate(180deg); }
        .faq-answer {
            padding: 0 28px;
            max-height: 0;
            overflow: hidden;
            transition: max-height .3s ease, padding .3s ease;
            font-size: 14px;
            line-height: 1.8;
            color: #6b7280;
        }
        .faq-answer.open {
            max-height: 500px;
            padding: 0 28px 18px;
        }

        .guide-steps {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            margin: 20px 0;
        }
        .guide-step {
            background: #fff;
            border-radius: 10px;
            padding: 24px 18px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
            position: relative;
        }
        .guide-step .step-number {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            color: #fff;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .guide-step h4 { font-size: 15px; color: #374151; margin-bottom: 6px; }
        .guide-step p { font-size: 13px; color: #6b7280; line-height: 1.6; }

        .support-box {
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            border-radius: 12px;
            padding: 36px;
            color: #fff;
            text-align: center;
            margin-top: 30px;
        }
        .support-box h3 { font-size: 20px; margin-bottom: 8px; }
        .support-box p { opacity: .85; margin-bottom: 18px; }
        .support-box .support-link {
            display: inline-block;
            padding: 10px 30px;
            background: rgba(255,255,255,.2);
            border: 1px solid rgba(255,255,255,.4);
            border-radius: 30px;
            color: #fff;
            font-size: 16px;
            text-decoration: none;
            transition: background .2s;
        }
        .support-box .support-link:hover { background: rgba(255,255,255,.3); }

        @media (max-width: 768px) {
            .quick-grid { grid-template-columns: repeat(2, 1fr); }
            .guide-steps { grid-template-columns: 1fr; }
            .help-container { margin: 16px 12px; }
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/../_head.php'; ?>

<div class="page-hero">
    <div class="container">
        <h1><i class="fas fa-question-circle"></i> 帮助中心</h1>
        <p>快速上手，掌握每一个数据查询工具</p>
    </div>
</div>

<div class="container help-container">

    <div class="quick-grid">
        <a href="/race/report/" class="quick-card">
            <i class="fas fa-search"></i>
            <div class="quick-title">查鸽子</div>
            <div class="quick-desc">输入足环号查赛绩</div>
        </a>
        <a href="/pedigree/certificate/" class="quick-card">
            <i class="fas fa-certificate"></i>
            <div class="quick-title">血统证书</div>
            <div class="quick-desc">免费生成可打印证书</div>
        </a>
        <a href="/loft/compare/" class="quick-card">
            <i class="fas fa-balance-scale"></i>
            <div class="quick-title">公棚对比</div>
            <div class="quick-desc">多公棚并排比较</div>
        </a>
        <a href="/pages/about/" class="quick-card">
            <i class="fas fa-info-circle"></i>
            <div class="quick-title">关于我们</div>
            <div class="quick-desc">了解平台数据规模</div>
        </a>
    </div>

    <div class="faq-section">
        <div class="faq-section-header">
            <i class="fas fa-rocket"></i> 快速上手
        </div>
        <div style="padding: 24px 28px;">
            <div class="guide-steps">
                <div class="guide-step">
                    <div class="step-number">1</div>
                    <h4>打开首页</h4>
                    <p>进入 www.xgjia.com，在搜索框输入 7 位足环号</p>
                </div>
                <div class="guide-step">
                    <div class="step-number">2</div>
                    <h4>查看报告</h4>
                    <p>自动生成赛绩报告：赛季统计、关赛明细、分速趋势</p>
                </div>
                <div class="guide-step">
                    <div class="step-number">3</div>
                    <h4>探索更多</h4>
                    <p>生成血统证书、对比公棚、按品系查成绩</p>
                </div>
            </div>
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-section-header">
            <i class="fas fa-search"></i> 足环号查询
        </div>
        <div class="faq-item">
            <div class="faq-question" onclick="toggleFaq(this)">
                怎么用足环号查鸽子成绩？
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                在首页搜索框输入鸽子的 7 位足环号（纯数字），点击"查鸽子"按钮，系统会自动跳转到深度报告页面，展示该鸽子的所有参赛记录。支持格式如："2068912"。
            </div>
        </div>
        <div class="faq-item">
            <div class="faq-question" onclick="toggleFaq(this)">
                深度报告里包含哪些内容？
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                报告包含：赛季参赛统计（场次/公里/分速）、全部关赛明细（时间/公棚/空距/归巢/排名/分速）、分速分布图、同批次鸽子对比、同一鸽主名下的其他鸽子成绩。第一部分免费，完整报告 ¥9.9 解锁。
            </div>
        </div>
        <div class="faq-item">
            <div class="faq-question" onclick="toggleFaq(this)">
                搜不到怎么办？
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                目前数据覆盖 2026 年赛季 1,444 场赛事。如果你查询的足环号未参赛或属于更早期的赛事，可能暂时查不到。数据持续更新中，可联系客服反馈。
            </div>
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-section-header">
            <i class="fas fa-certificate"></i> 血统证书
        </div>
        <div class="faq-item">
            <div class="faq-question" onclick="toggleFaq(this)">
                血统证书怎么生成？
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                进入血统证书页面，输入足环号，系统自动从数据库填充该鸽子的基本信息（包括品系、羽色、眼砂、最佳成绩等）。确认无误后点击"生成证书"，即可在浏览器中查看和打印（Ctrl+P / Cmd+P）。
            </div>
        </div>
        <div class="faq-item">
            <div class="faq-question" onclick="toggleFaq(this)">
                证书需要收费吗？
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                血统证书生成当前完全免费。你可以自由生成、打印、保存为 PDF。证书采用 HTML 格式，在任何浏览器中都能正常预览和打印。
            </div>
        </div>
        <div class="faq-item">
            <div class="faq-question" onclick="toggleFaq(this)">
                证书上的数据准确吗？
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                证书数据来源于公开赛事记录数据库。如果鸽子在数据库中有记录，会自动填入。如果缺少某些信息（如父母血统），系统会标注"数据缺失"。证书仅供参考，正式比赛以主办方要求为准。
            </div>
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-section-header">
            <i class="fas fa-balance-scale"></i> 公棚对比
        </div>
        <div class="faq-item">
            <div class="faq-question" onclick="toggleFaq(this)">
                公棚对比工具怎么用？
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                进入公棚对比页面，搜索你要比较的公棚名称，点击"加入对比"。最多可选 4 个公棚并排比较，指标包括：参赛费、收鸽数量、归巢率、奖金总额等。支持一键重置和重新选择。
            </div>
        </div>
        <div class="faq-item">
            <div class="faq-question" onclick="toggleFaq(this)">
                对比数据从哪里来？
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                数据来自平台爬取的 550+ 公棚公开信息以及关联的赛事成绩统计。公棚详情页有完整的收费、规程、联系方式等信息。
            </div>
        </div>
    </div>

    <div class="faq-section">
        <div class="faq-section-header">
            <i class="fas fa-credit-card"></i> 付费相关
        </div>
        <div class="faq-item">
            <div class="faq-question" onclick="toggleFaq(this)">
                哪些功能需要付费？
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                目前付费产品：<br>
                • <strong>足环号深度报告完整版</strong> ¥9.9 — 解锁全部 7 节分析内容<br>
                • <strong>公棚对比完整数据</strong> ¥19.9 — 解锁全部对比指标<br>
                血统证书生成、铭鸽搜索、公棚信息浏览均免费。
            </div>
        </div>
        <div class="faq-item">
            <div class="faq-question" onclick="toggleFaq(this)">
                怎么支付？
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                当前支持微信支付。购买时页面显示收款码，扫码支付后联系客服审核激活。我们正在对接 PAYJS 聚合支付，后续将支持自动到账。
            </div>
        </div>
        <div class="faq-item">
            <div class="faq-question" onclick="toggleFaq(this)">
                付费后多久能解锁？
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                支付后添加客服微信（pigeon_cs），发送支付截图，我们会在工作时间（周一至周五 9:00-18:00）1 小时内手动激活。非工作时间次日激活。
            </div>
        </div>
    </div>

    <div class="support-box">
        <h3><i class="fas fa-headset"></i> 没找到答案？</h3>
        <p>联系客服微信 pigeon_cs，工作时间 1 小时内回复</p>
        <a href="/pages/contact/" class="support-link">
            <i class="fas fa-phone"></i> 联系方式
        </a>
    </div>

</div>

<?php include __DIR__ . "/../_footer.php"; ?>
<script>
function toggleFaq(el) {
    const answer = el.nextElementSibling;
    const isOpen = answer.classList.contains('open');
    answer.classList.toggle('open');
    el.classList.toggle('open');
}
</script>
</body>
</html>