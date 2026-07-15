#!/usr/bin/env python3
"""
Fixed parser: byte-level structural parsing + correct UTF-8 JSON decoding + 2026 filter.
REPLACES parse_checkpoint_to_csv.py

Root cause of garbled chars: old parser did byte-by-byte UTF-8 decode.
Multi-byte Chinese chars (3 bytes) are invalid when one byte is decoded alone → \ufffd.
Fix: accumulate raw bytes for each race object, decode only via json.loads(bytes).
"""

import json, sys, os

CHUNK = 4 * 1024 * 1024  # 4MB

INPUT = "scripts/race_results_p1_full_checkpoint.json"
OUTPUT = "data/race_results_2026.csv"

def esc(s):
    if s is None: return ''
    return str(s).replace(',', ';').replace('\n', ' ').replace('\r', '')

def escape_csv(s):
    if s is None: return ''
    s = str(s).replace('\n', ' ').replace('\r', '')
    # Replace comma with semicolon for CSV safety
    return s.replace(',', ';')

def main():
    fin = INPUT
    fout = OUTPUT
    
    if not os.path.exists(fin):
        print(f"Error: {fin} not found", file=sys.stderr)
        return 1

    file_size = os.path.getsize(fin)
    print(f"Input: {fin} ({file_size/(1024*1024):.1f} MB)")

    # Find "results" array offset
    with open(fin, 'rb') as f:
        head = f.read(8 * 1024 * 1024).decode('utf-8', errors='replace')
    idx = head.find('"results": [')
    if idx == -1:
        print("Error: 'results' key not found", file=sys.stderr)
        return 1
    offset = idx + len('"results": [')
    print(f"'results' at byte {offset}")

    # Prepare output
    os.makedirs(os.path.dirname(fout) or '.', exist_ok=True)
    
    depth = 0
    in_str = False
    escaped = False
    buf = bytearray()
    started = False
    
    race_count = 0
    row_count = 0
    row_2026 = 0
    rows_skipped_other_season = 0
    races_with_2026 = 0
    
    with open(fout, 'w', encoding='utf-8') as out:
        # CSV header
        out.write("source_id,gp_id,loft_name,race_name,total_results,rank,owner_name,region,ring_number,color,arrival_time,speed\n")
        
        with open(fin, 'rb') as f:
            f.seek(offset)
            total_read = 0
            last_pct = -1
            
            while True:
                data = f.read(CHUNK)
                if not data:
                    break
                total_read += len(data)
                
                pct = int(total_read * 100 / (file_size - offset))
                if pct != last_pct:
                    print(f"\r  Reading... {pct}%", end='', flush=True)
                    last_pct = pct
                
                for b in data:
                    if escaped:
                        buf.append(b)
                        escaped = False
                        continue
                    
                    if b == 0x5c:  # backslash
                        buf.append(b)
                        escaped = True
                        continue
                    
                    if b == 0x22:  # double quote
                        in_str = not in_str
                        buf.append(b)
                        continue
                    
                    if in_str:
                        buf.append(b)
                        continue
                    
                    if b == 0x7b:  # {
                        if depth == 0:
                            buf = bytearray(b'{')
                            started = True
                        else:
                            buf.append(b)
                        depth += 1
                        continue
                    
                    if b == 0x7d:  # }
                        depth -= 1
                        buf.append(b)
                        if depth == 0 and started and len(buf) > 2:
                            race_count += 1
                            try:
                                race = json.loads(bytes(buf))
                                src_id = escape_csv(race.get('source_id'))
                                gp_id = escape_csv(race.get('gp_id'))
                                loft = escape_csv(race.get('loft_name'))
                                rname = escape_csv(race.get('race_name'))
                                total = race.get('total_results', '')
                                
                                race_has_2026 = False
                                results = race.get('results', [])
                                row_count += len(results)
                                
                                for r in results:
                                    atime = str(r.get('arrival_time', ''))
                                    if atime.startswith('2026'):
                                        race_has_2026 = True
                                        out.write(
                                            f"{src_id},{gp_id},{loft},{rname},{total},"
                                            f"{r.get('rank','')},{escape_csv(r.get('owner_name'))},"
                                            f"{escape_csv(r.get('region'))},{escape_csv(r.get('ring_number'))},"
                                            f"{escape_csv(r.get('color'))},{atime},"
                                            f"{r.get('speed','')}\n"
                                        )
                                        row_2026 += 1
                                    else:
                                        rows_skipped_other_season += 1
                                
                                if race_has_2026:
                                    races_with_2026 += 1
                                
                                if race_count % 100 == 0:
                                    print(f"\r  Races: {race_count} | 2026: {races_with_2026} ({row_2026} rows)", end='', flush=True)
                                    
                            except json.JSONDecodeError as e:
                                print(f"\n  Skip malformed race at byte ~{offset + total_read}: {str(e)[:80]}", file=sys.stderr)
                            finally:
                                buf = bytearray()
                                started = False
                        continue
                    
                    if b == 0x5d and depth == 0 and not started:  # ]
                        break
                    
                    if started:
                        buf.append(b)
        
        print(f"\r  Reading... 100%", flush=True)
    
    csv_size = os.path.getsize(fout) if os.path.exists(fout) else 0
    print(f"\nDone!")
    print(f"  Races processed:    {race_count}")
    print(f"  Total results:      {row_count:,}")
    print(f"  2026 results:       {row_2026:,}")
    print(f"  Skipped (non-2026): {rows_skipped_other_season:,}")
    print(f"  Races with 2026:    {races_with_2026}")
    print(f"  CSV output:         {fout} ({csv_size/(1024*1024):.1f} MB)")
    
    return 0

if __name__ == '__main__':
    exit(main())
