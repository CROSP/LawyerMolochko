# DDEV: Export & Run on Another Host

How to dump this project and run it on another machine with DDEV (same config, database, etc.).

---

## 1. On the current host (export)

### 1.1 Dump the database

From the project root (with DDEV running: `ddev start`):

```bash
# Create a timestamped dump in project root (recommended for portability)
ddev export-db --file=./db-export.sql.gz

# Or uncompressed (larger, but universal)
ddev export-db --file=./db-export.sql
```

You can also use:

```bash
ddev snapshot  # Creates snapshot in .ddev/db_snapshots/ (not ideal for moving to another host)
ddev mysql -e "..."  # For custom dump options
```

### 1.2 What to copy to the new host

Copy the **whole project** (or use git), including:

| Include | Notes |
|--------|--------|
| **Entire codebase** | All WordPress files, `wp-content`, themes, plugins |
| **`.ddev/config.yaml`** | DDEV project config (name, PHP, DB, ports) – already in repo |
| **`db-export.sql.gz`** (or `.sql`) | Database dump from step 1.1 |
| **`wp-config-ddev.php`** | DDEV-specific WP config – keep for DDEV |
| **`wp-config.php`** | If you use it to include DDEV config (often not in git) |

**Do not copy** (DDEV will regenerate or they’re machine-specific):

- `.ddev/db_snapshots/`
- `.ddev/traefik/` (certs, config)
- `.ddev/.ddev-docker-compose*.yaml`
- `.ddev/mutagen/`

So: **clone/copy the repo (or project folder) + add the DB dump file** is enough.

---

## 2. On the new host (import)

### 2.1 Prerequisites

- [DDEV installed](https://ddev.com/docs/installation/)
- Project folder (and `db-export.sql.gz` or `db-export.sql`) on the new machine

### 2.2 Start DDEV and import DB

```bash
cd /path/to/lawyermolochko

# Start containers (uses .ddev/config.yaml; same PHP, DB, ports)
ddev start

# Import the database (overwrites current DB)
ddev import-db --file=./db-export.sql.gz

# If you used uncompressed:
# ddev import-db --file=./db-export.sql
```

### 2.3 Fix URLs (if site URL changed)

If the new site will use a different URL (e.g. only `lawyermolochko.ddev.site` instead of a custom domain):

```bash
# List current URLs in DB
ddev wp option get siteurl
ddev wp option get home

# Search-replace old URL → new URL (example: production → DDEV)
ddev wp search-replace 'https://your-old-domain.com' 'https://lawyermolochko.ddev.site'
```

Or use [Interconnect/IT Search Replace DB](https://github.com/interconnectit/Search-Replace-DB) or similar.

### 2.4 Open the site

```bash
ddev launch
```

---

## 3. One-liner summary

**Export (current host):**

```bash
ddev export-db --file=./db-export.sql.gz
# Then copy whole project + db-export.sql.gz to new host (or commit dump to git / share via USB/cloud)
```

**Import (new host):**

```bash
ddev start
ddev import-db --file=./db-export.sql.gz
ddev launch
```

---

## 4. Your current DDEV config (for reference)

- **Project name:** `lawyermolochko`
- **Type:** WordPress  
- **PHP:** 8.2  
- **DB:** MariaDB 10.11  
- **Web server:** nginx-fpm  
- **HTTP/HTTPS ports:** 8080 / 8443  
- **Primary URL:** https://lawyermolochko.ddev.site  

The same config is in `.ddev/config.yaml`, so `ddev start` on the new host gives the same environment.

---

## 5. Optional: automate export

You can add a custom DDEV command to standardize exports, e.g. `.ddev/commands/host/db-export.sh`:

```bash
#!/bin/bash
ddev export-db --file=./db-export-$(date +%Y%m%d-%H%M%S).sql.gz
```

Then: `ddev db-export` from the project root. (You’d need to create the `host` directory and make the script executable.)
