#!/usr/bin/env bash
#
# Import database dump into DDEV (overwrites existing DB).
# Uses same fixed filename as export by default.
# Run from project root.
#
# Usage:
#   ./import-db.sh              # imports db-export.sql.gz
#   ./import-db.sh mydump.sql.gz
#

set -e

DB_FILE="db-export.sql.gz"

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

if [[ ! -f .ddev/config.yaml ]]; then
  echo "Error: run from project root (where .ddev/config.yaml is)"
  exit 1
fi

IN_FILE="${1:-$DB_FILE}"

if [[ ! -f "$IN_FILE" ]]; then
  echo "Error: file not found: $IN_FILE"
  echo "Usage: ./import-db.sh [file.sql.gz]  (default: $DB_FILE)"
  exit 1
fi

echo "Importing $IN_FILE into DDEV (overwrites current DB) ..."
ddev import-db --file=./"$IN_FILE"
echo "Done."
