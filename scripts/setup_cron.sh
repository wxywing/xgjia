#!/bin/bash
# 信鸽之家 - 自动化 Cron 配置脚本
# 
# 用法：
#   bash scripts/setup_cron.sh          # 查看当前 cron
#   bash scripts/setup_cron.sh --install # 安装/更新 cron 任务
#   bash scripts/setup_cron.sh --remove  # 移除所有 cron 任务

SITE="xgjia-website"
SCRIPT_DIR="$(cd "$(dirname "$0")/.." && pwd)"

# PHP CLI 路径（服务器上可能不同，需要确认）
PHP_BIN=$(which php || which php8.1 || which php74 || echo "php")

echo "使用 PHP: $PHP_BIN"
echo "脚本目录: $SCRIPT_DIR"
echo ""

# 当前 cron 任务
echo "=== 当前 cron 任务 ==="
crontab -l 2>/dev/null | grep -v "^#" | grep -v "^$" || echo "（无）"
echo ""

if [[ "$1" == "--remove" ]]; then
    echo "🗑️  移除所有 xgjia 相关 cron 任务..."
    (crontab -l 2>/dev/null | grep -v "$SITE") | crontab -
    echo "✅ 已移除"
    exit 0
fi

if [[ "$1" != "--install" ]]; then
    echo "用法："
    echo "  bash setup_cron.sh --install   # 安装 cron 任务"
    echo "  bash setup_cron.sh --remove   # 移除 cron 任务"
    echo ""
    echo "⚠️  安装前请先确认："
    echo "  1. 服务器 PHP 路径：$PHP_BIN"
    echo "  2. 百度 Token 已配置（scripts/.baidu_token 或环境变量 BAIDU_TOKEN）"
    exit 0
fi

echo "📦 安装 cron 任务..."
echo ""

# 读取现有 crontab
CURRENT=$(crontab -l 2>/dev/null || echo "")

# 新的 cron 任务（使用临时文件避免 heredoc 问题）
TEMP=$(mktemp)

cat > "$TEMP" << 'CRONEOF'
# === 信鸽之家自动化任务 ===
# 每周一凌晨 2:00 生成上周赛事周报文章
0 2 * * 1 cd /path/to/xgjia-website && php scripts/auto_article_weekly.php --run >> logs/weekly_article.log 2>&1

# 每天凌晨 3:00 推送最近 24 小时新产生的 URL 到百度（增量推送）
0 3 * * * cd /path/to/xgjia-website && php scripts/baidu_push.php --recent 24 >> logs/baidu_push.log 2>&1

# 每周日凌晨 4:00 推送全站重要 URL 到百度（全量推送）
0 4 * * 0 cd /path/to/xgjia-website && php scripts/baidu_push.php --all >> logs/baidu_push.log 2>&1
CRONEOF

echo "⚠️  注意：上面任务中的 /path/to/xgjia-website 需要替换为实际路径"
echo ""
echo "推荐 cron 任务内容如下，请手动添加到 crontab（crontab -e）："
echo ""
cat "$TEMP"
echo ""
echo "路径替换（自动生成）..."
sed -i.bak "s|/path/to/xgjia-website|${SCRIPT_DIR}|g" "$TEMP"
sed -i.bak "s|php scripts/|${PHP_BIN} scripts/|g" "$TEMP"
echo ""
echo "=== 实际安装命令（请确认后执行）==="
echo "crontab -e"
echo "然后粘贴以下内容："
echo ""
cat "$TEMP"
echo ""

rm -f "$TEMP" "$TEMP.bak"
echo ""
echo "📝 推荐 crontab 条目说明："
echo "  1. 每周一 02:00  — 生成赛事周报文章（auto_article_weekly.php --run）"
echo "  2. 每天  03:00  — 增量推送新URL到百度（baidu_push.php --recent 24）"
echo "  3. 每周日 04:00  — 全量推送重要URL到百度（baidu_push.php --all）"
echo ""
echo "✅ 配置完成！记得先创建日志目录："
echo "   mkdir -p ${SCRIPT_DIR}/logs"
