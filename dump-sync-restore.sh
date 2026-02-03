#!/usr/bin/env bash
#
# Run this script IN THIS PROJECT. It will:
#   1. Dump this project's DDEV database
#   2. Sync this project to crosphz:~/Development/lawyermolochko
#   3. Restore the database on the remote (DDEV) and start the site
#
# Usage: ./dump-sync-restore.sh
#

set -e

REMOTE_HOST="crosphz"
REMOTE_BASE="~/Development"
PROJECT_NAME="lawyermolochko"
REMOTE_DIR="${REMOTE_BASE}/${PROJECT_NAME}"
DUMP_FILE="db-export.sql.gz"

# ---
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

if [[ ! -f .ddev/config.yaml ]]; then
  echo "Error: run from project root (where .ddev/config.yaml is)"
  exit 1
fi

echo "Syncing THIS project ($(pwd)) to ${REMOTE_HOST}:${REMOTE_DIR}"
echo ""
echo "=== 1. Dump database (DDEV) ==="
ddev export-db --file=./"$DUMP_FILE"
echo "Dumped to $DUMP_FILE"

echo ""
echo "=== 2. Sync to ${REMOTE_HOST}:${REMOTE_DIR}/ ==="
rsync -avz --delete \
  --exclude='.ddev/db_snapshots' \
  --exclude='.ddev/traefik' \
  --exclude='.ddev/.ddev-docker-compose*.yaml' \
  --exclude='.ddev/mutagen' \
  --exclude='.ddev/.homeadditions' \
  --exclude='.ddev/.sshimageBuild' \
  --exclude='.ddev/.dbimageBuild' \
  --exclude='.ddev/.webimageBuild' \
  --exclude='node_modules' \
  ./ "${REMOTE_HOST}:${REMOTE_DIR}/"
echo "Sync done."

echo ""
echo "=== 3. Restore database on remote (DDEV) ==="
ssh "$REMOTE_HOST" "cd ${REMOTE_DIR} && ddev start && ddev import-db --file=./${DUMP_FILE}"
echo "Restore done."

echo ""
echo "All done. Remote site: ${REMOTE_HOST}:${REMOTE_DIR}"
echo "Open on remote: ssh ${REMOTE_HOST} -t 'cd ${REMOTE_DIR} && ddev launch'"
