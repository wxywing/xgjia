#!/usr/bin/env python3
"""从 products_detail.json 生成展品(pigeons) INSERT SQL"""
import json, sys

OUT_DIR = '/Users/scentlibrart-007/.qclaw/workspace-agent-f8126f99/xgjia-website/scripts'

with open(f'{OUT_DIR}/products_detail.json', encoding='utf-8') as f:
    products = json.load(f)

lines = [
    '-- ============================================',
    '-- 铭鸽展品数据批量导入',
    f'-- 数据量: {len(products)} 条',
    '-- user_id = 0: 待商家认领',
    '-- status = 0: 待审核',
    '-- ============================================',
    '',
]

ok_count = 0
for p in products:
    if p.get('status') != 'ok':
        continue
    ok_count += 1
    
    name = (p.get('name') or '').replace("'", "''")[:100]
    ring = (p.get('ring_number') or '').replace("'", "''")[:50]
    bloodline = (p.get('bloodline') or '').replace("'", "''")[:100]
    color = (p.get('color') or '').replace("'", "''")[:50]
    eye = (p.get('eye_color') or '').replace("'", "''")[:50]
    gender = p.get('gender', 'NULL') or 'NULL'
    category = (p.get('category') or '').replace("'", "''")[:100]
    price = (p.get('price') or '').replace("'", "''")
    shop_id = p.get('shop_id', '')
    source_id = p.get('product_id', '')
    
    # images as JSON array
    images = json.dumps(p.get('images', []), ensure_ascii=False).replace("'", "''")
    
    desc_parts = []
    if category:
        desc_parts.append(f"目录: {category}")
    if price:
        desc_parts.append(f"价格: ¥{price}")
    desc_parts.append(f"来源: 中信网展厅 shop_id={shop_id} product_id={source_id}")
    description = '\\n'.join(desc_parts).replace("'", "''")
    
    # shop_id 通过子查询获取
    sql = (
        f"INSERT INTO `pigeons` (`user_id`, `shop_id`, `source_id`, `name`, `ring_number`, "
        f"`bloodline`, `color`, `eye_type`, `gender`, `images`, `description`, `status`) "
        f"SELECT 0, s.id, '{source_id}', "
    )
    
    if name:
        sql += f"'{name}', "
    else:
        sql += "NULL, "
    
    if ring:
        sql += f"'{ring}', "
    else:
        sql += "NULL, "
    
    if bloodline:
        sql += f"'{bloodline}', "
    else:
        sql += "NULL, "
    
    if color:
        sql += f"'{color}', "
    else:
        sql += "NULL, "
    
    if eye:
        sql += f"'{eye}', "
    else:
        sql += "NULL, "
    
    if gender and gender != 'NULL':
        sql += f"{gender}, "
    else:
        sql += "0, "
    
    sql += f"'{images}', '{description}', 0 "
    sql += f"FROM `shops` s WHERE s.source_id = '{shop_id}';"
    
    lines.append(sql)

print(f"Generated {ok_count} INSERT statements (from {len(products)} total)")

out = f'{OUT_DIR}/products_import.sql'
with open(out, 'w', encoding='utf-8') as f:
    f.write('\n'.join(lines))
print(f"Output: {out}")
