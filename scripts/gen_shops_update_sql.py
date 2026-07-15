#!/usr/bin/env python3
"""从 shops_detail.json 生成展厅详情 UPDATE SQL"""
import json, sys

OUT_DIR = '/Users/scentlibrart-007/.qclaw/workspace-agent-f8126f99/xgjia-website/scripts'

with open(f'{OUT_DIR}/shops_detail.json', encoding='utf-8') as f:
    shops = json.load(f)

lines = [
    '-- ============================================',
    '-- 展厅详情更新SQL（补充联系信息+分类+展品数）',
    f'-- 数据量: {len(shops)} 条',
    '-- ============================================',
    '',
]

updated = 0
for shop in shops:
    if shop.get('detail_status') != 'ok':
        continue
    
    source_id = shop.get('shop_id', '')
    if not source_id:
        continue
    
    sets = []
    if shop.get('province'):
        sets.append(f"province = '{shop['province'].replace(chr(39), chr(39)+chr(39))}'")
    if shop.get('city'):
        sets.append(f"city = '{shop['city'].replace(chr(39), chr(39)+chr(39))}'")
    if shop.get('address'):
        sets.append(f"address = '{shop['address'].replace(chr(39), chr(39)+chr(39))}'")
    if shop.get('contact_name'):
        sets.append(f"contact_name = '{shop['contact_name'].replace(chr(39), chr(39)+chr(39))}'")
    if shop.get('contact_phone'):
        sets.append(f"contact_phone = '{shop['contact_phone'].replace(chr(39), chr(39)+chr(39))}'")
    if shop.get('email'):
        sets.append(f"description = CONCAT(IFNULL(description,''), '\\nEmail: {shop['email'].replace(chr(39), chr(39)+chr(39))}')")
    if shop.get('model'):
        sets.append(f"model = '{shop['model']}'")
    if shop.get('pigeon_count'):
        sets.append(f"pigeon_count = {shop.get('pigeon_count', 0) or len(shop.get('product_ids', []))}")
    elif shop.get('product_ids'):
        sets.append(f"pigeon_count = {len(shop['product_ids'])}")
    if shop.get('website'):
        sets.append(f"website = '{shop['website'].replace(chr(39), chr(39)+chr(39))}'")
    
    if not sets:
        continue
    
    sql = f"UPDATE `shops` SET {', '.join(sets)} WHERE source_id = '{source_id}';"
    lines.append(sql)
    updated += 1
    
    # 生成 shop_categories INSERT
    for cat in shop.get('categories', []):
        cat_name = cat['name'].replace("'", "''")[:100]
        lines.append(
            f"INSERT INTO `shop_categories` (`shop_id`, `source_id`, `name`, `pigeon_count`) "
            f"SELECT id, '{cat['source_id']}', '{cat_name}', {cat['pigeon_count']} "
            f"FROM `shops` WHERE source_id = '{source_id}';"
        )

print(f"Generated {updated} shop UPDATE statements + categories")

out = f'{OUT_DIR}/shops_update_detail.sql'
with open(out, 'w', encoding='utf-8') as f:
    f.write('\n'.join(lines))
print(f"Output: {out}")
