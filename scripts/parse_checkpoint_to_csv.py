#!/usr/bin/env python3
"""
Stream-parse race_results checkpoint JSON to CSV shards.
Zero dependencies — chunked streaming JSON parser for the "results" array.

The checkpoint JSON structure:
  {"processed": N, "total": N, "results": [{race1}, {race2}, ...]}
"""

import json, argparse, os, sys
from pathlib import Path

CHUNK = 4 * 1024 * 1024  # 4MB chunks

def esc(s):
    if s is None: return ''
    return str(s).replace(',', ';').replace('\n', ' ').replace('\r', '')

def parse_race_objects(filepath, offset):
    """
    Generator yielding race objects from a JSON file starting at `offset`.
    Uses chunked reading with brace-depth tracking.
    """
    depth = 0
    in_str = False
    escaped = False
    buf = []          # Current race object buffer
    pending = []      # Characters accumulated for the current race
    started = False   # Have we hit the first '{'?
    
    chunk_size = CHUNK
    pos = offset
    total_read = 0
    
    with open(filepath, 'rb') as f:
        f.seek(offset)
        while True:
            data = f.read(chunk_size)
            if not data:
                break
            total_read += len(data)
            
            for i in range(len(data)):
                byte = data[i:i+1]
                ch = byte.decode('utf-8', errors='replace')
                
                if escaped:
                    pending.append(ch)
                    escaped = False
                    continue
                
                if ch == '\\':
                    pending.append(ch)
                    escaped = True
                    continue
                
                if ch == '"':
                    in_str = not in_str
                    pending.append(ch)
                    continue
                
                if in_str:
                    pending.append(ch)
                    continue
                
                # Outside string
                if ch == '{':
                    if depth == 0:
                        # Start of a top-level race object
                        pending = ['{']
                        started = True
                    else:
                        pending.append(ch)
                    depth += 1
                    continue
                
                if ch == '}':
                    depth -= 1
                    pending.append(ch)
                    if depth == 0 and started:
                        race_json = ''.join(pending)
                        try:
                            race = json.loads(race_json)
                            yield race
                        except json.JSONDecodeError as e:
                            print(f"\n  Skip bad race at byte ~{offset + total_read}: {str(e)[:80]}", file=sys.stderr)
                        finally:
                            pending = []
                            started = False
                    continue
                
                if ch == ']' and depth == 0 and not started:
                    # End of the results array, stop
                    return
                
                if started:
                    pending.append(ch)
            
            # Progress
            pct = (total_read / (os.path.getsize(filepath) - offset)) * 100
            print(f"\r  Reading... {pct:.1f}%", end='', flush=True)

def main():
    p = argparse.ArgumentParser()
    p.add_argument('--shard-size', type=int, default=50000)
    p.add_argument('--input', default='scripts/race_results_p1_full_checkpoint.json')
    p.add_argument('--output-dir', default='data')
    args = p.parse_args()

    os.makedirs(args.output_dir, exist_ok=True)
    fin = Path(args.input)
    if not fin.exists():
        print(f"Error: {args.input} not found", file=sys.stderr)
        return 1

    file_size = fin.stat().st_size
    print(f"File: {fin} ({file_size/(1024*1024):.1f} MB)", flush=True)

    # Find "results" array
    with open(fin, 'rb') as f:
        head = f.read(4 * 1024 * 1024).decode('utf-8', errors='replace')
    idx = head.find('"results": [')
    if idx == -1:
        print("Error: 'results' key not found", file=sys.stderr)
        return 1
    offset = idx + len('"results": [')
    print(f"'results' at byte {offset}", flush=True)

    hdr = "source_id,gp_id,loft_name,race_name,total_results,rank,owner_name,region,ring_number,color,arrival_time,speed\n"
    sidx = rcnt = rcount = 0
    cf = None

    print("Streaming races...", flush=True)
    for race in parse_race_objects(args.input, offset):
        rcount += 1

        if cf is None or (rcnt > 0 and rcnt % args.shard_size == 0):
            if cf:
                cf.close()
                print(f"\n  Shard {sidx-1}: {rcnt} rows", flush=True)
            sp = Path(args.output_dir) / f"race_results_{sidx:03d}.csv"
            cf = open(sp, 'w', encoding='utf-8')
            cf.write(hdr)
            sidx += 1

        sid = esc(race.get('source_id'))
        gid = esc(race.get('gp_id'))
        lnm = esc(race.get('loft_name'))
        rnm = esc(race.get('race_name'))
        tr = race.get('total_results', '')

        for r in race.get('results', []):
            cf.write(f"{sid},{gid},{lnm},{rnm},{tr},"
                     f"{r.get('rank','')},{esc(r.get('owner_name'))},"
                     f"{esc(r.get('region'))},{esc(r.get('ring_number'))},"
                     f"{esc(r.get('color'))},{esc(r.get('arrival_time'))},"
                     f"{r.get('speed','')}\n")
            rcnt += 1

    if cf:
        cf.close()

    print(f"\nDone! {rcount} races, {rcnt} rows, {sidx} shards", flush=True)
    return 0

if __name__ == '__main__':
    exit(main())