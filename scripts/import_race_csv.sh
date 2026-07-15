#!/bin/bash
# Import CSV race results into MySQL (local)
# Usage: bash scripts/import_race_csv.sh

set -e
MYSQL="/Applications/phpstudy/Extensions/MySQL5.7.28/bin/mysql"
DB="xgjia"
CDIR="$(cd "$(dirname "$0")/.." && pwd)"
DATA="${CDIR}/data"

echo "=== Race Results Import ==="
echo "DB: $DB"
echo "Data: $DATA"

# Prep: add race_id column once
echo "[Prep] Adding race_id col + truncating _temp_race_import..."
$MYSQL -u root -p123456 $DB -e "
  ALTER TABLE _temp_race_import ADD COLUMN IF NOT EXISTS race_id INT UNSIGNED;
  TRUNCATE _temp_race_import;
" 2>/dev/null || $MYSQL -u root -p123456 $DB -e "
  TRUNCATE _temp_race_import;
"

SHARDS=($(ls "$DATA"/race_results_*.csv 2>/dev/null))
TOTAL=${#SHARDS[@]}
echo "Found $TOTAL CSV shard(s)"

for i in "${!SHARDS[@]}"; do
  csv="${SHARDS[$i]}"
  fname=$(basename "$csv")
  shard_no=$((i+1))
  fsize=$(du -h "$csv" | cut -f1)
  echo ""
  echo "--- Shard $shard_no/$TOTAL: $fname ($fsize) ---"
  
  # Step 1: LOAD DATA
  echo "  [1/4] LOAD DATA LOCAL INFILE..."
  $MYSQL -u root -p123456 $DB --local-infile=1 -e "
    LOAD DATA LOCAL INFILE '$csv'
    INTO TABLE _temp_race_import
    FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"'
    IGNORE 1 ROWS
    (source_id, gp_id, loft_name, race_name, total_results, rank,
     owner_name, region, ring_number, color, arrival_time, speed);
  " 2>/dev/null
  
  n_loaded=$($MYSQL -u root -p123456 $DB -N -e "SELECT COUNT(*) FROM _temp_race_import WHERE race_id IS NULL;" 2>/dev/null)
  echo "  Loaded $n_loaded rows"
  
  # Step 2: Insert unique races
  echo "  [2/4] Inserting new races..."
  $MYSQL -u root -p123456 $DB -e "
    INSERT IGNORE INTO races (source_id, loft_id, name, entry_count, data_source)
    SELECT t.source_id, l.id, MAX(t.race_name), MAX(t.total_results), 'crawl'
    FROM (SELECT DISTINCT source_id, gp_id FROM _temp_race_import WHERE race_id IS NULL) t
    LEFT JOIN lofts l ON l.gp_id = t.gp_id
    GROUP BY t.source_id, l.id;
  " 2>/dev/null
  
  # Step 3: Map race_id
  echo "  [3/4] Mapping race_id..."
  $MYSQL -u root -p123456 $DB -e "
    UPDATE _temp_race_import t
    JOIN races r ON r.source_id = t.source_id
    SET t.race_id = r.id
    WHERE t.race_id IS NULL;
  " 2>/dev/null
  
  n_mapped=$($MYSQL -u root -p123456 $DB -N -e "SELECT COUNT(*) FROM _temp_race_import WHERE race_id IS NOT NULL;" 2>/dev/null)
  echo "  Mapped $n_mapped rows"
  
  # Step 4: Insert results
  echo "  [4/4] Inserting race_results..."
  $MYSQL -u root -p123456 $DB -e "
    INSERT INTO race_results (race_id, rank, owner_name, region, ring_number, color, arrival_time, speed)
    SELECT race_id, rank, owner_name, region, ring_number, color,
           STR_TO_DATE(arrival_time, '%Y-%m-%d %H:%i:%s.%f'),
           CAST(NULLIF(speed,'') AS DECIMAL(10,6))
    FROM _temp_race_import
    WHERE race_id IS NOT NULL;
  " 2>/dev/null
  
  n_inserted=$($MYSQL -u root -p123456 $DB -N -e "SELECT COUNT(*) FROM race_results;" 2>/dev/null)
  echo "  Total race_results: $n_inserted"
  
  # Cleanup
  $MYSQL -u root -p123456 $DB -e "TRUNCATE _temp_race_import;" 2>/dev/null
done

echo ""
echo "=== Done ==="

TOTAL_RACES=$($MYSQL -u root -p123456 $DB -N -e "SELECT COUNT(*) FROM races;" 2>/dev/null)
TOTAL_RESULTS=$($MYSQL -u root -p123456 $DB -N -e "SELECT COUNT(*) FROM race_results;" 2>/dev/null)
echo "Races: $TOTAL_RACES"
echo "Results: $TOTAL_RESULTS"
