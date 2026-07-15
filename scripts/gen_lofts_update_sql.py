#!/usr/bin/env python3
"""从 lofts_detail.json 生成 UPDATE SQL，补充公棚联系信息"""
import json, sys

OUT_DIR = '/Users/scentlibrart-007/.qclaw/workspace-agent-f8126f99/xgjia-website/scripts'

with open(f'{OUT_DIR}/lofts_detail.json', encoding='utf-8') as f:
    lofts = json.load(f)

lines = [
    '-- ============================================',
    '-- 公棚详情更新SQL（补充联系信息）',
    f'-- 数据量: {len(lofts)} 条',
    '-- 按gp_id匹配lofts表description字段中的"公棚ID: XXX"',
    '-- ============================================',
    '',
]

updated = 0
for loft in lofts:
    if loft.get('detail_status') != 'ok':
        continue
    
    gp_id = loft.get('gp_id', '')
    if not gp_id:
        continue
    
    # 收集有值的字段
    sets = []
    if loft.get('province'):
        sets.append(f"province = '{loft['province'].replace(chr(39), chr(39)+chr(39))}'")
    if loft.get('city'):
        sets.append(f"city = '{loft['city'].replace(chr(39), chr(39)+chr(39))}'")
    if loft.get('address'):
        sets.append(f"address = '{loft['address'].replace(chr(39), chr(39)+chr(39))}'")
    if loft.get('contact_name'):
        sets.append(f"contact_name = '{loft['contact_name'].replace(chr(39), chr(39)+chr(39))}'")
    if loft.get('contact_phone'):
        sets.append(f"contact_phone = '{loft['contact_phone'].replace(chr(39), chr(39)+chr(39))}'")
    if loft.get('entry_fee'):
        sets.append(f"entry_fee = '{loft['entry_fee']}'")
    if loft.get('management_fee'):
        sets.append(f"management_fee = '{loft['management_fee']}'")
    if loft.get('prize_pool'):
        sets.append(f"prize_pool = '{loft['prize_pool']}'")
    if loft.get('capacity'):
        sets.append(f"capacity = {loft['capacity']}")
    if loft.get('race_distance'):
        sets.append(f"race_distance = '{loft['race_distance']}'")
    if loft.get('logo'):
        sets.append(f"logo = '{loft['logo'].replace(chr(39), chr(39)+chr(39))}'")
    if loft.get('description') and len(loft['description']) > 20:
        desc = loft['description'].replace("'", "''")[:2000]
        sets.append(f"description = '{desc}'")
    
    if not sets:
        continue
    
    # 使用 description LIKE '%公棚ID: XXX%' 来匹配
    where = f"description LIKE '%公棚ID: {gp_id}%'"
    sql = f"UPDATE `lofts` SET {', '.join(sets)} WHERE {where};"
    lines.append(sql)
    updated += 1

print(f"Generated {updated} UPDATE statements")

out = f'{OUT_DIR}/lofts_update_detail.sql'
with open(out, 'w', encoding='utf-8') as f:
    f.write('\n'.join(lines))
print(f"Output: {out}")
