#!/usr/bin/env python3
"""
清洗铭鸽 description 中的 chinaxinge 导航栏脏数据。

导航栏模式：鸽舍族谱 → 展厅新闻 → ... → 联系我们
占 description 开头，后面可能跟有效内容（血统/分类等）。

策略：
1. 正则匹配并删除导航前缀 ^鸽舍族谱.*?联系我们[\s\\r\\n]*
2. 删除后若剩余内容 < 10 字符 → 清空 description
3. 同时处理混入的“输入鸽名”等无效标记
"""

import re
import sys

# 导航前缀正则：从"鸽舍族谱"到"联系我们"（包括换行空白）
NAV_PREFIX_RE = re.compile(
    r'^鸽舍族谱\s*'
    r'.*?'
    r'联系我们[\s\\r\\n]*',
    re.DOTALL
)

# 其他无效内容标记（单独出现的无意义片段）
JUNK_ONLY_RE = re.compile(
    r'^(鸽舍族谱|展厅新闻|历年赛绩|信鸽拍卖|在线购买|'
    r'推荐配对|鸽圈动态|展厅相册|鸽友留言|联系我们|'
    r'输入鸽名|\\r|\\n|\s)+$'
)

def clean_description(desc):
    """清洗单条 description，返回 (cleaned_desc, was_dirty)"""
    if not desc or len(desc) < 5:
        return desc, False

    original = desc

    # 1. 删除导航前缀
    desc = NAV_PREFIX_RE.sub('', desc).strip()

    # 2. 如果剩余内容过短或只是无效标记，清空
    if len(desc) < 10 or JUNK_ONLY_RE.match(desc):
        desc = ''

    # 3. 限制长度
    if len(desc) > 2000:
        desc = desc[:2000]

    was_dirty = (desc != original)
    return desc, was_dirty


def process_tsv(input_path, output_path):
    """读取 TSV 文件，生成 SQL UPDATE 语句"""
    sql_lines = []
    stats = {'total': 0, 'cleared': 0, 'trimmed': 0, 'kept': 0}

    with open(input_path, 'r', encoding='utf-8') as f:
        for line in f:
            line = line.rstrip('\n')
            if not line:
                continue

            parts = line.split('\t', 2)
            if len(parts) < 3:
                continue

            pid = parts[0]
            # parts[1] 是原始长度，不需要
            raw_desc = parts[2]

            # 解码 \\n \\r → 真正的换行回车
            desc = raw_desc.replace('\\n', '\n').replace('\\r', '\r')

            cleaned, was_dirty = clean_description(desc)
            stats['total'] += 1

            if not was_dirty:
                continue

            if not cleaned:
                stats['cleared'] += 1
                # 清空 description
                sql_lines.append(f"UPDATE pigeons SET description = '' WHERE id = {pid};")
            else:
                stats['trimmed'] += 1
                # 转义单引号
                escaped = cleaned.replace('\\', '\\\\').replace("'", "\\'")
                sql_lines.append(f"UPDATE pigeons SET description = '{escaped}' WHERE id = {pid};")
                stats['kept'] += 1

    with open(output_path, 'w', encoding='utf-8') as f:
        f.write("-- 铭鸽 description 导航脏数据清洗\n")
        f.write(f"-- 生成时间: 2026-06-26\n")
        f.write(f"-- 总处理: {stats['total']} 条 | 清空: {stats['cleared']} | 裁剪保留: {stats['trimmed']}\n\n")
        f.write("SET NAMES utf8mb4;\n\n")
        f.write('\n'.join(sql_lines))
        f.write('\n')

    return stats


if __name__ == '__main__':
    stats = process_tsv('/tmp/pigeon_dirty.tsv', '/tmp/pigeon_cleanup.sql')
    print(f"总处理: {stats['total']}")
    print(f"清空 description: {stats['cleared']}")
    print(f"裁剪保留内容: {stats['trimmed']}")
    print(f"SQL 输出: /tmp/pigeon_cleanup.sql")
