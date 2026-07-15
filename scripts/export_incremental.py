#!/usr/bin/env python3
"""
信鸽之家 - 增量数据导出脚本
====================================
用法：
  python3 export_incremental.py
  # 生成 xgjia_incremental_20260625.sql

功能：
  - 导出 2026-06-24 以来新增的所有增量数据
  - races / shops / pigeons: INSERT IGNORE（直接生成 INSERT）
  - race_results: mysqldump --insert-ignore 高效导出
  - 自动保留 ID 关联（race_id -> races.id），避免外链断裂
  - 输出单文件 .sql，线上直接 mysql < file.sql 导入
"""

import subprocess, os, sys
from datetime import datetime

# ========== 配置 ==========
MYSQL = "/Applications/phpstudy/Extensions/MySQL5.7.28/bin/mysql"
MYSQLDUMP = "/Applications/phpstudy/Extensions/MySQL5.7.28/bin/mysqldump"
DB = "xgjia"
USER = "root"
PASS = "123456"
MIN_DATE = "2026-06-24"  # 增量数据起始日期

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
OUTPUT = os.path.join(SCRIPT_DIR, f"xgjia_incremental_{MIN_DATE.replace('-', '')}.sql")


# ========== 工具函数 ==========

def run_mysql(sql):
    """执行 MySQL 查询，返回多行文本（tab 分隔）"""
    cmd = [MYSQL, "-u", USER, f"-p{PASS}", DB, "-N", "-B", "-e", sql]
    try:
        out = subprocess.check_output(cmd, stderr=subprocess.DEVNULL, timeout=300)
        return out.decode("utf-8").strip()
    except subprocess.CalledProcessError as e:
        print(f"[错误] MySQL 查询失败: {e}", file=sys.stderr)
        sys.exit(1)


def esc(val):
    """Safe MySQL string escaping"""
    if val is None or val == 'NULL' or val == '':
        return 'NULL'
    s = str(val)
    s = s.replace("\\", "\\\\").replace("'", "\\'")
    return "'" + s + "'"


def esc_null(val):
    """Escaped value, returns 'NULL' for None or empty"""
    if val is None or val == 'NULL':
        return 'NULL'
    v = str(val).strip()
    if v == '':
        return 'NULL'
    s = v.replace("\\", "\\\\").replace("'", "\\'")
    return "'" + s + "'"


def dt_val(val):
    """Format datetime value: return 'NULL' or quoted string"""
    if val is None or val == 'NULL' or str(val).strip() == '':
        return 'NULL'
    v = str(val).strip()
    return "'" + v + "'"


def run_mysqldump(table, where_clause):
    """Run mysqldump with --insert-ignore and return SQL text"""
    cmd = [MYSQLDUMP, "-u", USER, f"-p{PASS}", DB, table,
           "--insert-ignore", "--no-create-info",
           "--skip-add-locks", "--skip-comments", "--skip-tz-utc",
           f"--where={where_clause}"]
    try:
        out = subprocess.check_output(cmd, stderr=subprocess.DEVNULL, timeout=600)
        return out.decode("utf-8").strip()
    except subprocess.CalledProcessError as e:
        print(f"[错误] mysqldump 失败 ({table}): {e}", file=sys.stderr)
        sys.exit(1)


# ========== 主逻辑 ==========

def main():
    lines = []
    lines.append("-- ===========================================================")
    lines.append(f"-- 信鸽之家增量数据导出")
    lines.append(f"-- 导出时间: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    lines.append(f"-- 数据范围: created_at >= {MIN_DATE}")
    lines.append("-- 线上导入: mysql -u root -p xgjia < xgjia_incremental.sql")
    lines.append("-- ===========================================================")
    lines.append("SET NAMES utf8mb4;")
    lines.append("START TRANSACTION;")
    lines.append("")

    # -------------------------------------------------------
    # 1. RACES（按 created_at 过滤）
    # -------------------------------------------------------
    print("[1/5] 导出增量 races ...", file=sys.stderr)
    rows = run_mysql(
        f"SELECT id, loft_id, source_id, name, release_location, "
        f"distance_km, participant_count, result_count, entry_count, "
        f"returned_count, return_rate, release_time, race_category, "
        f"season_year, season_type, status, data_source, source_url "
        f"FROM races WHERE DATE(created_at) >= '{MIN_DATE}' "
        f"ORDER BY id"
    )

    race_id_list = []
    if rows.strip():
        for row_text in rows.split("\n"):
            row = row_text.split("\t")
            if len(row) < 18:
                continue
            (rid, loft_id, source_id, name, release_location,
             distance_km, participant_count, result_count, entry_count,
             returned_count, return_rate, release_time, race_category,
             season_year, season_type, status, data_source, source_url) = row[:18]

            vals = [
                rid, loft_id, esc_null(source_id), esc_null(name),
                esc_null(release_location),
                distance_km if distance_km and distance_km != 'NULL' else 'NULL',
                participant_count if participant_count and participant_count != 'NULL' else 'NULL',
                result_count if result_count and result_count != 'NULL' else 'NULL',
                entry_count if entry_count and entry_count != 'NULL' else 'NULL',
                returned_count if returned_count and returned_count != 'NULL' else 'NULL',
                return_rate if return_rate and return_rate != 'NULL' else 'NULL',
                dt_val(release_time), esc(race_category),
                season_year if season_year and season_year != 'NULL' else 'NULL',
                esc_null(season_type), status, esc_null(data_source),
                esc_null(source_url)
            ]
            lines.append(
                f"INSERT IGNORE INTO `races` (`id`, `loft_id`, `source_id`, "
                f"`name`, `release_location`, `distance_km`, `participant_count`, "
                f"`result_count`, `entry_count`, `returned_count`, `return_rate`, "
                f"`release_time`, `race_category`, `season_year`, `season_type`, "
                f"`status`, `data_source`, `source_url`) VALUES "
                f"({', '.join(vals)});"
            )
            race_id_list.append(rid.strip())

        lines.append(f"-- 共 {len(race_id_list)} 条增量 races")
        lines.append("")
    else:
        lines.append("-- 无增量 races 数据")
        lines.append("")

    # -------------------------------------------------------
    # 2. RACE_RESULTS（mysqldump，仅导出增量 races 的成绩）
    # -------------------------------------------------------
    print("[2/5] 导出增量 race_results（mysqldump）...", file=sys.stderr)
    if race_id_list:
        race_id_where = "race_id IN (" + ",".join(race_id_list) + ")"
        dump_sql = run_mysqldump("race_results", race_id_where)
        if dump_sql:
            # mysqldump 输出重定向 INSERT 为 INSERT IGNORE
            # 但 --insert-ignore 已经做过了
            lines.append(dump_sql)
            lines.append("")
    else:
        lines.append("-- 无增量 race_results（无新 races）")
        lines.append("")

    # -------------------------------------------------------
    # 3. SHOPS（按 created_at 过滤，有 UNIQUE KEY source_id）
    # -------------------------------------------------------
    print("[3/5] 导出增量 shops ...", file=sys.stderr)
    rows = run_mysql(
        f"SELECT id, user_id, source_id, name, avatar, province, city, "
        f"address, contact_name, contact_phone, description, website, "
        f"model, views, pigeon_count, is_certified, is_hot, status "
        f"FROM shops WHERE DATE(created_at) >= '{MIN_DATE}' "
        f"ORDER BY id"
    )

    if rows.strip():
        count = 0
        for row_text in rows.split("\n"):
            row = row_text.split("\t")
            if len(row) < 18:
                continue
            (sid, uid, source_id, name, avatar, province, city, address,
             contact_name, contact_phone, description, website, model,
             views, pigeon_count, is_certified, is_hot, status) = row[:18]

            vals = [
                sid, uid, esc_null(source_id), esc_null(name),
                esc_null(avatar), esc_null(province), esc_null(city),
                esc_null(address), esc_null(contact_name),
                esc_null(contact_phone), esc_null(description),
                esc_null(website), esc_null(model),
                views if views and views != 'NULL' else '0',
                pigeon_count if pigeon_count and pigeon_count != 'NULL' else '0',
                is_certified if is_certified and is_certified != 'NULL' else '0',
                is_hot if is_hot and is_hot != 'NULL' else '0',
                status if status and status != 'NULL' else '0'
            ]
            lines.append(
                f"INSERT IGNORE INTO `shops` (`id`, `user_id`, `source_id`, "
                f"`name`, `avatar`, `province`, `city`, `address`, "
                f"`contact_name`, `contact_phone`, `description`, `website`, "
                f"`model`, `views`, `pigeon_count`, `is_certified`, `is_hot`, "
                f"`status`) VALUES ({', '.join(vals)});"
            )
            count += 1
        lines.append(f"-- 共 {count} 条增量 shops")
        lines.append("")
    else:
        lines.append("-- 无增量 shops 数据")
        lines.append("")

    # -------------------------------------------------------
    # 4. PIGEONS（按 created_at 过滤）
    # -------------------------------------------------------
    print("[4/5] 导出增量 pigeons ...", file=sys.stderr)
    rows = run_mysql(
        f"SELECT id, user_id, shop_id, source_id, category_id, name, "
        f"ring_number, bloodline, strain_id, gender, birth_date, color, "
        f"eye_color, description, images, video, achievements, pedigree, "
        f"views, likes, comments, is_top, is_recommend, status, reject_reason "
        f"FROM pigeons WHERE DATE(created_at) >= '{MIN_DATE}' "
        f"ORDER BY id"
    )

    if rows.strip():
        count = 0
        for row_text in rows.split("\n"):
            row = row_text.split("\t")
            if len(row) < 25:
                continue
            (pid, uid, shop_id, source_id, cid, name, ring_number,
             bloodline, strain_id, gender, birth_date, color, eye_color,
             description, images, video, achievements, pedigree,
             views, likes, comments, is_top, is_recommend, status,
             reject_reason) = row[:25]

            vals = [
                pid, uid,
                shop_id if shop_id and shop_id != 'NULL' else 'NULL',
                esc_null(source_id),
                cid if cid and cid != 'NULL' else 'NULL',
                esc_null(name), esc_null(ring_number),
                esc_null(bloodline),
                strain_id if strain_id and strain_id != 'NULL' else 'NULL',
                gender if gender and gender != 'NULL' else 'NULL',
                dt_val(birth_date), esc_null(color), esc_null(eye_color),
                esc_null(description), esc_null(images), esc_null(video),
                esc_null(achievements), esc_null(pedigree),
                views if views and views != 'NULL' else '0',
                likes if likes and likes != 'NULL' else '0',
                comments if comments and comments != 'NULL' else '0',
                is_top if is_top and is_top != 'NULL' else '0',
                is_recommend if is_recommend and is_recommend != 'NULL' else '0',
                status if status and status != 'NULL' else '0',
                esc_null(reject_reason)
            ]
            lines.append(
                f"INSERT IGNORE INTO `pigeons` (`id`, `user_id`, `shop_id`, "
                f"`source_id`, `category_id`, `name`, `ring_number`, "
                f"`bloodline`, `strain_id`, `gender`, `birth_date`, `color`, "
                f"`eye_color`, `description`, `images`, `video`, "
                f"`achievements`, `pedigree`, `views`, `likes`, `comments`, "
                f"`is_top`, `is_recommend`, `status`, `reject_reason`) VALUES "
                f"({', '.join(vals)});"
            )
            count += 1
        lines.append(f"-- 共 {count} 条增量 pigeons")
        lines.append("")
    else:
        lines.append("-- 无增量 pigeons 数据")
        lines.append("")

    # -------------------------------------------------------
    # 5. LOFTS（按 created_at 过滤）
    # -------------------------------------------------------
    print("[5/5] 导出增量 lofts ...", file=sys.stderr)
    rows = run_mysql(
        f"SELECT id, user_id, source_id, name, gp_id, province, city, "
        f"address, contact_name, contact_phone, logo, photos, description, "
        f"capacity, current_count, entry_fee, management_fee, prize_pool, "
        f"prize_detail, race_distance, race_type, collect_start, collect_end, "
        f"training_start, race_date, rules, facilities, rating, rating_count, "
        f"views, is_certified, is_hot, status, source_url, wechat, "
        f"website, lat, lng "
        f"FROM lofts WHERE DATE(created_at) >= '{MIN_DATE}' "
        f"ORDER BY id"
    )

    if rows.strip():
        count = 0
        for row_text in rows.split("\n"):
            row = row_text.split("\t")
            if len(row) < 38:
                continue
            (lid, uid, source_id, name, gp_id, province, city, address,
             contact_name, contact_phone, logo, photos, description,
             capacity, current_count, entry_fee, management_fee, prize_pool,
             prize_detail, race_distance, race_type, collect_start,
             collect_end, training_start, race_date, rules, facilities,
             rating, rating_count, views, is_certified, is_hot, status,
             source_url, wechat, website, lat, lng) = row[:38]

            vals = [
                lid, uid, esc_null(source_id), esc_null(name),
                esc_null(gp_id), esc_null(province), esc_null(city),
                esc_null(address), esc_null(contact_name),
                esc_null(contact_phone), esc_null(logo), esc_null(photos),
                esc_null(description),
                capacity if capacity and capacity != 'NULL' else '0',
                current_count if current_count and current_count != 'NULL' else '0',
                entry_fee if entry_fee and entry_fee != 'NULL' else '0.00',
                management_fee if management_fee and management_fee != 'NULL' else '0.00',
                prize_pool if prize_pool and prize_pool != 'NULL' else '0.00',
                esc_null(prize_detail),
                race_distance if race_distance and race_distance != 'NULL' else 'NULL',
                esc_null(race_type), dt_val(collect_start), dt_val(collect_end),
                dt_val(training_start), dt_val(race_date),
                esc_null(rules), esc_null(facilities),
                rating if rating and rating != 'NULL' else '0',
                rating_count if rating_count and rating_count != 'NULL' else '0',
                views if views and views != 'NULL' else '0',
                is_certified if is_certified and is_certified != 'NULL' else '0',
                is_hot if is_hot and is_hot != 'NULL' else '0',
                status if status and status != 'NULL' else '0',
                esc_null(source_url), esc_null(wechat), esc_null(website),
                lat if lat and lat != 'NULL' else 'NULL',
                lng if lng and lng != 'NULL' else 'NULL'
            ]
            lines.append(
                f"INSERT IGNORE INTO `lofts` (`id`, `user_id`, `source_id`, "
                f"`name`, `gp_id`, `province`, `city`, `address`, "
                f"`contact_name`, `contact_phone`, `logo`, `photos`, "
                f"`description`, `capacity`, `current_count`, `entry_fee`, "
                f"`management_fee`, `prize_pool`, `prize_detail`, "
                f"`race_distance`, `race_type`, `collect_start`, "
                f"`collect_end`, `training_start`, `race_date`, `rules`, "
                f"`facilities`, `rating`, `rating_count`, `views`, "
                f"`is_certified`, `is_hot`, `status`, `source_url`, "
                f"`wechat`, `website`, `lat`, `lng`) VALUES "
                f"({', '.join(vals)});"
            )
            count += 1
        lines.append(f"-- 共 {count} 条增量 lofts")
        lines.append("")
    else:
        lines.append("-- 无增量 lofts 数据")
        lines.append("")

    # -------------------------------------------------------
    # 写入文件
    # -------------------------------------------------------
    lines.append("COMMIT;")
    lines.append("-- 导出完成")

    content = "\n".join(lines) + "\n"

    with open(OUTPUT, "w", encoding="utf-8") as f:
        f.write(content)

    # 统计
    size_kb = os.path.getsize(OUTPUT) / 1024
    print(f"\n✅ 导出完成: {OUTPUT}", file=sys.stderr)
    print(f"   文件大小: {size_kb:.1f} KB", file=sys.stderr)
    print(f"   races: {len(race_id_list)} 条", file=sys.stderr)

    # 统计 race_results（mysqldump 多行 INSERT 格式）
    import re
    result_pattern = r"INSERT\s+IGNORE\s+INTO\s+`race_results`"
    result_matches = list(re.finditer(result_pattern, content))
    result_rows = 0
    for rm in result_matches:
        r_start = rm.end()
        r_end = content.find(';\n', r_start)
        if r_end == -1:
            r_end = content.find(';', r_start)
        if r_end > r_start:
            r_vals = content[r_start:r_end].strip()
            result_rows += r_vals.count('),(') + 1
    print(f"   race_results: {result_rows:,} 行", file=sys.stderr)
    print(f"   INSERT 语句数: {len(result_matches)}", file=sys.stderr)
    print(file=sys.stderr)
    print("线上导入命令:", file=sys.stderr)
    print(f"  mysql -u root -p xgjia < {OUTPUT}", file=sys.stderr)
    print(file=sys.stderr)
    print("⚠️  注意：如果提示 Duplicate entry，可用 --force 跳过:", file=sys.stderr)
    print(f"  mysql -u root -p --force xgjia < {OUTPUT}", file=sys.stderr)


if __name__ == "__main__":
    main()
