#!/usr/bin/env bash
#
# Export DDEV database to a fixed file (same name for export/import, can keep in repo).
# Run from project root.
#
# Usage:
#   ./export-db.sh              # exports to db-export.sql.gz
#   ./export-db.sh mydump.sql.gz
#

set -e

DB_FILE="db-export.sql.gz"

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

if [[ ! -f .ddev/config.yaml ]]; then
  echo "Error: run from project root (where .ddev/config.yaml is)"
  exit 1
fi

OUT_FILE="${1:-$DB_FILE}"

echo "Exporting DDEV database to $OUT_FILE ..."
ddev export-db --file=./"$OUT_FILE"
echo "Done: $OUT_FILE"
