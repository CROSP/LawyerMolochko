#!/bin/bash
#
# Deploy lawyermolochko to remote Docker host (ssh crosphz).
# Domain: lawyer-molochko.com.ua. Exports DB from DDEV, replaces URLs, deploys with DB on first run.
#
set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

SSH_HOST="crosphz"
REMOTE_DIR="/home/docker/lawyermolochko"
PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PRODUCTION_URL="https://lawyer-molochko.com.ua"

echo -e "${GREEN}=== Remote Docker deployment → lawyer-molochko.com.ua ===${NC}\n"

# Pre-flight: theme must exist
if [ ! -d "$PROJECT_DIR/wp-content/themes/molochko" ]; then
    echo -e "${RED}Error: wp-content/themes/molochko not found.${NC}"
    exit 1
fi
echo "Found theme: wp-content/themes/molochko"

# --- Export database from DDEV and replace URLs for production ---
echo -e "\n${YELLOW}[0/7] Database: export from DDEV and prepare for production URL...${NC}"
mkdir -p "$PROJECT_DIR/dumps"
if command -v ddev &>/dev/null && [ -f "$PROJECT_DIR/.ddev/config.yaml" ]; then
    cd "$PROJECT_DIR"
    ddev export-db --file=dumps/init.sql 2>/dev/null || ddev export-db > dumps/init.sql
    if [ -s "$PROJECT_DIR/dumps/init.sql" ]; then
        echo "Exported DB to dumps/init.sql"
        # Replace all dev URLs with production (order matters: longer first)
        # Use LC_ALL=C so sed doesn't fail on UTF-8/binary in dump
        for old in \
            "https://lawyermolochko.ddev.site:8443" \
            "http://lawyermolochko.ddev.site:8443" \
            "https://lawyermolochko.ddev.site:8080" \
            "http://lawyermolochko.ddev.site:8080" \
            "https://lawyer-molochko.com.ua:8443" \
            "http://lawyer-molochko.com.ua:8443" \
            "https://lawyermolochko.ddev.site" \
            "http://lawyermolochko.ddev.site"; do
            if [[ "$OSTYPE" == "darwin"* ]]; then
                LC_ALL=C sed -i '' "s|${old}|${PRODUCTION_URL}|g" "$PROJECT_DIR/dumps/init.sql"
            else
                LC_ALL=C sed -i "s|${old}|${PRODUCTION_URL}|g" "$PROJECT_DIR/dumps/init.sql"
            fi
        done
        echo -e "${GREEN}✓ DB dump ready (URLs → $PRODUCTION_URL)${NC}"
    else
        echo -e "${YELLOW}⚠ DDEV export produced empty file; deploy will run without initial DB.${NC}"
    fi
    cd "$PROJECT_DIR"
else
    if [ -s "$PROJECT_DIR/dumps/init.sql" ]; then
        echo "Using existing dumps/init.sql (no DDEV export)"
    else
        echo -e "${YELLOW}⚠ No DDEV and no dumps/init.sql; first deploy will start with empty DB.${NC}"
    fi
fi

# Check SSH
echo -e "\n${YELLOW}[1/7] Testing SSH ($SSH_HOST)...${NC}"
if ! ssh -o ConnectTimeout=5 "$SSH_HOST" "echo 'SSH OK'" &>/dev/null; then
    echo -e "${RED}Error: Cannot connect to $SSH_HOST. Configure SSH (e.g. ~/.ssh/config).${NC}"
    exit 1
fi
echo -e "${GREEN}✓ SSH OK${NC}"

# Docker on remote
echo -e "\n${YELLOW}[2/7] Checking Docker on remote...${NC}"
if ! ssh "$SSH_HOST" "command -v docker &>/dev/null"; then
    echo -e "${RED}Error: Docker not found on $SSH_HOST${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Docker installed${NC}"

# Remote dirs
echo -e "\n${YELLOW}[3/7] Creating remote directories...${NC}"
ssh "$SSH_HOST" "mkdir -p $REMOTE_DIR/{wp-content,mysql-data,dumps}"
echo -e "${GREEN}✓ Done${NC}"

# Archive (wp-content + Docker files + dumps + nginx proxy config)
echo -e "\n${YELLOW}[4/7] Creating deployment archive...${NC}"
TEMP_ARCHIVE=$(mktemp -d)/lawyermolochko-deploy.tar.gz
cd "$PROJECT_DIR"

# On macOS, don't put extended attributes in tar so Linux extraction is clean
[[ "$OSTYPE" == "darwin"* ]] && export COPYFILE_DISABLE=1

TAR_FILES="docker-compose.yml wp-config.docker.php .env.example wp-content"
[ -d "$PROJECT_DIR/dumps" ] && [ -n "$(ls -A "$PROJECT_DIR/dumps" 2>/dev/null)" ] && TAR_FILES="$TAR_FILES dumps"
[ -f "$PROJECT_DIR/nginx-proxy-lawyer-molochko.conf" ] && TAR_FILES="$TAR_FILES nginx-proxy-lawyer-molochko.conf"

tar -czf "$TEMP_ARCHIVE" \
    --exclude='.git' \
    --exclude='node_modules' \
    --exclude='*.log' \
    --exclude='__MACOSX' \
    --exclude='._*' \
    --exclude='wp-content/debug.log' \
    --exclude='wp-content/db.php' \
    --exclude='wp-content/litespeed' \
    $TAR_FILES

SIZE=$(du -h "$TEMP_ARCHIVE" | cut -f1)
echo -e "${GREEN}✓ Archive created ($SIZE)${NC}"

# Upload
echo -e "\n${YELLOW}[5/7] Uploading to $SSH_HOST:$REMOTE_DIR...${NC}"
scp "$TEMP_ARCHIVE" "$SSH_HOST:$REMOTE_DIR/lawyermolochko-deploy.tar.gz"
rm -f "$TEMP_ARCHIVE"
echo -e "${GREEN}✓ Uploaded${NC}"

# Extract and start on remote
echo -e "\n${YELLOW}[6/7] Extracting and starting containers on remote...${NC}"
ssh "$SSH_HOST" "cd $REMOTE_DIR && \
set -e && \
echo 'Extracting...' && \
tar -xzf lawyermolochko-deploy.tar.gz 2>/dev/null && \
rm -f lawyermolochko-deploy.tar.gz && \
find wp-content -name '._*' -type f -delete 2>/dev/null || true && \
chmod -R 755 wp-content && \
chmod -R 777 wp-content/uploads 2>/dev/null || true && \
if [ ! -f .env ]; then cp .env.example .env; fi && \
(grep -q '^WORDPRESS_URL=' .env && sed -i 's|^WORDPRESS_URL=.*|WORDPRESS_URL=$PRODUCTION_URL|' .env || echo \"WORDPRESS_URL=$PRODUCTION_URL\" >> .env) && \
(grep -q '^WORDPRESS_SITEURL=' .env && sed -i 's|^WORDPRESS_SITEURL=.*|WORDPRESS_SITEURL=$PRODUCTION_URL|' .env || echo \"WORDPRESS_SITEURL=$PRODUCTION_URL\" >> .env) && \
(docker compose down 2>/dev/null || docker-compose down 2>/dev/null || true) && \
[ -f dumps/init.sql ] && ( file dumps/init.sql | grep -qi gzip ) && mv dumps/init.sql dumps/init.sql.gz || true && \
echo 'Starting Docker (DB will import from dumps/ on first start)...' && \
(docker compose up -d || docker-compose up -d) && \
echo 'Waiting for DB to be ready...' && \
for i in \$(seq 1 30); do (docker compose exec -T db mariadb -u wordpress -p\"\$(grep '^MYSQL_PASSWORD=' .env | cut -d= -f2-)\" wordpress -e 'SELECT 1' 2>/dev/null) && break; sleep 3; done || true && \
echo 'Setting WordPress URLs to production...' && \
(docker compose exec -T db mariadb -u wordpress -p\"\$(grep '^MYSQL_PASSWORD=' .env | cut -d= -f2-)\" wordpress -e \"UPDATE wp_options SET option_value='$PRODUCTION_URL' WHERE option_name IN ('home','siteurl');\" 2>/dev/null && echo '✓ WordPress siteurl/home set to $PRODUCTION_URL' || echo '⚠ DB URL update skipped (empty DB or still importing)') && \
echo 'Replacing any dev URLs in database (WP-CLI search-replace)...' && \
(docker compose exec -T wordpress sh -c 'curl -sL -o /tmp/wp-cli.phar https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar && chmod +x /tmp/wp-cli.phar' 2>/dev/null) && \
(docker compose exec -T wordpress sh -c 'cd /var/www/html && php /tmp/wp-cli.phar search-replace \"https://lawyermolochko.ddev.site:8443\" \"'$PRODUCTION_URL'\" --all-tables --allow-root' 2>/dev/null || true) && \
(docker compose exec -T wordpress sh -c 'cd /var/www/html && php /tmp/wp-cli.phar search-replace \"http://lawyermolochko.ddev.site:8443\" \"'$PRODUCTION_URL'\" --all-tables --allow-root' 2>/dev/null || true) && \
(docker compose exec -T wordpress sh -c 'cd /var/www/html && php /tmp/wp-cli.phar search-replace \"https://lawyermolochko.ddev.site\" \"'$PRODUCTION_URL'\" --all-tables --allow-root' 2>/dev/null || true) && \
(docker compose exec -T wordpress sh -c 'cd /var/www/html && php /tmp/wp-cli.phar search-replace \"http://lawyermolochko.ddev.site\" \"'$PRODUCTION_URL'\" --all-tables --allow-root' 2>/dev/null || true) && \
(docker compose exec -T wordpress sh -c 'cd /var/www/html && php /tmp/wp-cli.phar search-replace \"https://lawyer-molochko.com.ua:8443\" \"'$PRODUCTION_URL'\" --all-tables --allow-root' 2>/dev/null || true) && \
echo '✓ Deployment complete.' && \
echo '  docker compose ps' && \
echo '  docker compose logs -f' && \
if [ -f nginx-proxy-lawyer-molochko.conf ]; then echo ''; echo 'To enable domain: sudo cp nginx-proxy-lawyer-molochko.conf /etc/nginx/conf.d/lawyer-molochko.conf && sudo nginx -t && sudo systemctl reload nginx'; fi"

echo -e "\n${YELLOW}[7/7] Proxy: install Nginx config on host to serve lawyer-molochko.com.ua${NC}"
echo -e "\n${GREEN}=== Deployment complete ===${NC}\n"
echo "Site URL: $PRODUCTION_URL"
echo "Container: ports 8092 (HTTP), 8453 (HTTPS). Nginx on host should proxy 80/443 → 8092."
echo -e "\n${YELLOW}On remote:${NC}"
echo "  ssh $SSH_HOST"
echo "  cd $REMOTE_DIR"
echo "  docker compose ps"
echo "  docker compose logs -f"
echo ""
echo "Enable domain (on crosphz):"
echo "  sudo cp $REMOTE_DIR/nginx-proxy-lawyer-molochko.conf /etc/nginx/conf.d/lawyer-molochko.conf"
echo "  sudo nginx -t && sudo systemctl reload nginx"
