# Docker deployment (lawyermolochko)

**Domain:** [lawyer-molochko.com.ua](https://lawyer-molochko.com.ua)  
**Remote host:** crosphz (SSH). WordPress runs in Docker with **wp-content** and **wp-config** mounted; Nginx on host proxies the domain to the container.

## Prerequisites

- SSH: `ssh crosphz`
- Docker and Docker Compose on the remote server (V2 plugin `docker compose` is preferred; legacy `docker-compose` also works)
- Local: DDEV (for DB export on first deploy)

## Deploy (with database on first run)

From project root:

```bash
./deploy-remote.sh
```

The script will:

1. **Export DB** from DDEV to `dumps/init.sql` and replace all dev URLs with `https://lawyer-molochko.com.ua`.
2. Test SSH to **crosphz**.
3. Create `/home/docker/lawyermolochko` and subdirs.
4. Build a tarball: `wp-content/`, `docker-compose.yml`, `wp-config.docker.php`, `.env.example`, `dumps/` (with `init.sql`), `nginx-proxy-lawyer-molochko.conf`.
5. Upload and extract on the remote.
6. Set `.env` with `WORDPRESS_URL` and `WORDPRESS_SITEURL` to production URL.
7. Run `docker compose up -d`. On **first start**, MariaDB imports `dumps/init.sql` / `dumps/init.sql.gz` automatically.
8. Wait for DB to be ready, then **update WordPress `siteurl` and `home` in the database** to the production URL (so the site works without manual steps).

## Nginx proxy (lawyer-molochko.com.ua)

The container listens on **8092** (HTTP) and **8453** (HTTPS). Nginx on the host should proxy the domain to the container.

**On crosphz after deploy:**

```bash
sudo cp /home/docker/lawyermolochko/nginx-proxy-lawyer-molochko.conf /etc/nginx/conf.d/lawyer-molochko.conf
sudo nginx -t && sudo systemctl reload nginx
```

Then point DNS for **lawyer-molochko.com.ua** and **www.lawyer-molochko.com.ua** to the server. For HTTPS, add certificates (e.g. certbot) and uncomment the SSL server block in the proxy config.

## Volume mapping

| Host (remote)              | Container              |
|----------------------------|------------------------|
| `./wp-content`             | `/var/www/html/wp-content` |
| `./wp-config.docker.php`   | `/var/www/html/wp-config.php` |
| `./mysql-data`             | DB data (MariaDB)      |
| `./dumps`                  | Initial DB import (`.sql` run on first DB start) |

## Ports

- **8092:80** — WordPress HTTP (proxy target)
- **8453:443** — WordPress HTTPS (optional)
- **127.0.0.1:8083:80** — phpMyAdmin (localhost only; use `ssh -L 8083:127.0.0.1:8083 crosphz` then open http://127.0.0.1:8083)

Host Nginx/Caddy binds 80/443 for lawyer-molochko.com.ua and proxies to `127.0.0.1:8092`.

## Security (production)

Before going live:

1. **DB passwords** — On the server, edit `.env` and set **strong** `MYSQL_PASSWORD` and `MYSQL_ROOT_PASSWORD`. Do not use the values from `.env.example` in production. Then run `docker compose down && docker compose up -d` so containers pick up the new env.
2. **phpMyAdmin** — Bound to `127.0.0.1:8083` only; not exposed to the internet. Use an SSH tunnel to access it.
3. **WordPress** — `wp-config.docker.php` sets `DISALLOW_FILE_EDIT` (no theme/plugin editor in admin) and `FORCE_SSL_ADMIN` (HTTPS for wp-admin). Debug is off.
4. **Secrets** — `.env` is in `.gitignore`; do not commit it. Real credentials live only on the server.
5. **HTTPS** — Caddy (or Nginx) should terminate SSL; the deploy script and config trust `X-Forwarded-Proto` so WordPress does not redirect-loop.

## After deploy (on remote)

```bash
ssh crosphz
cd /home/docker/lawyermolochko

docker compose ps
docker compose logs -f
docker compose exec wordpress wp --info
```

## Database

- **First deploy:** `./deploy-remote.sh` exports from DDEV, replaces URLs, and includes `dumps/init.sql`. MariaDB runs it on first container start.
- **Re-import:** Remove `mysql-data/`, then `docker compose up -d` again (all DB data is lost).

## File layout (remote)

```
/home/docker/lawyermolochko/
├── docker-compose.yml
├── wp-config.docker.php
├── .env
├── nginx-proxy-lawyer-molochko.conf   # copy to /etc/nginx/conf.d/
├── wp-content/
├── mysql-data/
└── dumps/
    └── init.sql   # used on first DB start
```
