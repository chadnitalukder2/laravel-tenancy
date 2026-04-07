# Installation and Domain Setup (A-Z)

This project is a Laravel 10 app using [stancl/tenancy v3](https://tenancyforlaravel.com/docs/v3/introduction) with a multi-database setup:

- **Central app** stores tenant metadata (`tenants`, `domains`, central `users`).
- **Tenant app** runs per domain, each tenant with its own database.
- Tenant databases are created during tenant registration.

---

## 1) Prerequisites

Install these first:

- PHP 8.1+ with common Laravel extensions (`pdo_mysql`, `openssl`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`)
- Composer 2.x
- MySQL 5.7+ or 8.x
- Node.js 18+ and npm

MySQL user must have permission to create databases:

```sql
GRANT CREATE, ALTER, DROP, INDEX, REFERENCES ON *.* TO 'your_user'@'%';
FLUSH PRIVILEGES;
```

---

## 2) Get project and install dependencies

```bash
git clone <repository-url> tenancy
cd tenancy
composer install
npm install
```

---

## 3) Configure environment

Create `.env` and app key:

```bash
# Windows (PowerShell/CMD)
copy .env.example .env

# Linux/macOS
# cp .env.example .env

php artisan key:generate
```

Set at least these values in `.env`:

| Variable | Example | Why |
|---|---|---|
| `APP_URL` | `http://localhost:8000` | Main central URL. |
| `DB_CONNECTION` | `mysql` | Central DB driver. |
| `DB_HOST` | `127.0.0.1` | Central DB host. |
| `DB_PORT` | `3306` | Central DB port. |
| `DB_DATABASE` | `tenancy` | Central database name. |
| `DB_USERNAME` | `root` | DB username with create-database permissions. |
| `DB_PASSWORD` | `secret` | DB password. |

Create the central database:

```sql
CREATE DATABASE tenancy CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Run central migrations:

```bash
php artisan migrate
```

---

## 4) Domain strategy (recommended for local)

Choose one base domain for local development:

- **Quick start:** `localhost` (works fast with `php artisan serve`)
- **More realistic:** `tenant.test` or `app.test` (recommended with Valet/Nginx/Apache)

How tenancy routing works here:

- Hosts listed in `config/tenancy.php` > `central_domains` are treated as **central**.
- Any other host that exists in `domains` table is treated as a **tenant** domain.

If your central domain is `localhost`, tenant domains become like:

- `acme.localhost`
- `beta.localhost`

If your central domain is `app.test`, tenant domains become like:

- `acme.app.test`
- `beta.app.test`

---

## 5) Domain setup A-Z (Windows, Linux, macOS)

### Option A: Use `php artisan serve` (fastest)

Start app:

```bash
php artisan serve
```

Central URL:

- `http://127.0.0.1:8000` or `http://localhost:8000`

Tenant URL pattern:

- `http://<tenant-domain>:8000`

If wildcard localhost is not resolved on your machine, add host entries manually:

- **Windows hosts file:** `C:\Windows\System32\drivers\etc\hosts`
- **Linux/macOS hosts file:** `/etc/hosts`

Example entries:

```txt
127.0.0.1 localhost
127.0.0.1 acme.localhost
127.0.0.1 beta.localhost
```

Note: hosts files do not support wildcard entries (`*.localhost`) directly. Add each tenant hostname you need.

### Option B: Use Valet / Nginx / Apache (recommended for many subdomains)

Use a domain such as `app.test` and map wildcard subdomains to your local server.

- Central: `http://app.test`
- Tenants: `http://acme.app.test`, `http://beta.app.test`

High-level requirements:

1. DNS/hosts resolves `*.app.test` to `127.0.0.1`.
2. Web server uses your Laravel `public` folder as document root.
3. Web server accepts wildcard hostnames.
4. `APP_URL` points to central domain (`http://app.test`).
5. `central_domains` includes `app.test`.

If you use Laravel Valet, wildcard `.test` resolution is typically already handled.

---

## 6) Run frontend assets

Development:

```bash
npm run dev
```

Production build:

```bash
npm run build
```

---

## 7) Register first tenant

1. Open central registration page:
   - `http://localhost:8000/tenancy-register` (or your central domain)
2. Submit tenant form with subdomain (example: `acme`).
3. App creates:
   - tenant record
   - tenant domain (example `acme.localhost` or `acme.app.test`)
   - tenant database
   - tenant migrations from `database/migrations/tenant`
4. Open tenant URL in browser.

---

## 8) Verify tenancy is working

Run:

```bash
php artisan tenants:list
```

Check:

- Central tables contain tenant/domain records.
- New tenant database exists in MySQL.
- Tenant URL loads tenant routes (from `routes/tenant.php`).

---

## 9) Useful commands

| Command | Purpose |
|---|---|
| `php artisan migrate` | Run central migrations. |
| `php artisan tenants:list` | List registered tenants. |
| `php artisan tenants:migrate` | Run migrations on all tenant databases. |
| `php artisan optimize:clear` | Clear route/config/cache/views. |

---

## 10) Troubleshooting

- **Tenant DB not created**
  - Confirm DB user has create-database permission.
  - Check app logs in `storage/logs/laravel.log`.
- **Tenant URL returns 404 or central page**
  - Verify exact hostname exists in `domains` table.
  - Confirm host resolves to local machine (`ping acme.localhost`).
  - Confirm central domain is in `central_domains`; tenant domain should not be there.
- **Cannot resolve subdomain**
  - Add specific host entries, or use a wildcard-capable local server setup (Valet/Nginx/Apache).
- **Stale config**
  - Run `php artisan optimize:clear` and restart the server.

---

## 11) Production notes

- Use real DNS wildcard records (for example `*.yourdomain.com`).
- Configure HTTPS and wildcard certificates.
- Move tenant provisioning to queued jobs if needed.
- Ensure DB user permissions follow least privilege for your infrastructure model.
