# Installation

This project is a **Laravel 10** application with **multi-database tenancy** using [stancl/tenancy](https://tenancyforlaravel.com/) (v3). The **central** application stores tenants and domains; each **tenant** gets its own database, created automatically when a tenant is registered.

## Prerequisites

- **PHP** 8.1 or newer with extensions Laravel expects (including `pdo_mysql` for the default MySQL setup, `openssl`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`)
- **Composer** 2.x
- **MySQL** 5.7+ or 8.x (other drivers are supported by Laravel and tenancy, but this repo’s `.env.example` targets MySQL)
- **Node.js** 18+ and **npm** (for Vite and frontend assets)

The MySQL user configured in `.env` must be allowed to **`CREATE DATABASE`**, because new tenant databases are created at runtime.

## 1. Get the code and install PHP dependencies

```bash
git clone <repository-url> tenancy
cd tenancy
composer install
```

## 2. Environment file

Copy the example environment file and generate an application key:

```bash
copy .env.example .env
php artisan key:generate
```

On Linux or macOS, use `cp .env.example .env` instead of `copy`.

Edit `.env` and set at least:

| Variable | Purpose |
|----------|---------|
| `APP_URL` | Public URL of the **central** app (e.g. `http://localhost:8000`). Used to derive **central domains** in `config/tenancy.php`. |
| `DB_*` | Connection to the **central** database (default database name in `.env.example` is `tenancy`). |

Create the central database in MySQL before running migrations (empty database is enough), for example:

```sql
CREATE DATABASE tenancy CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

## 3. Central database migrations

Run migrations that live in `database/migrations/` (not the `tenant/` subdirectory). This creates central tables such as `tenants`, `domains`, and central `users`.

```bash
php artisan migrate
```

## 4. Frontend assets (optional for local UI)

Install JavaScript dependencies and, during development, run the Vite dev server:

```bash
npm install
npm run dev
```

For a production build:

```bash
npm run build
```

## 5. Run the application

```bash
php artisan serve
```

Visit the URL shown (typically `http://127.0.0.1:8000`) for the central routes defined in `routes/web.php`.

## Tenancy: domains and registering a tenant

- **Central** routes are restricted to hosts listed in `config/tenancy.php` under `central_domains` (by default this includes `127.0.0.1`, `localhost`, and the host from `APP_URL`).
- **Tenant** routes are loaded for any other host that matches a row in the `domains` table, with middleware `InitializeTenancyByDomain` (see `routes/tenant.php`).

To create a tenant from the UI:

1. Open **`/tenancy-register`** on the **central** domain (e.g. `http://127.0.0.1:8000/tenancy-register`).
2. Submit the form. The app appends your chosen subdomain to the second entry in `central_domains` (see `StoreTenancyRegisterRequest` and the registration view). For example, if that host is `localhost`, a subdomain `acme` becomes **`acme.localhost`** in the database.
3. After a successful registration, the package creates a **new database** for the tenant and runs **tenant** migrations from `database/migrations/tenant/`.

To visit the tenant app, use the full domain you registered (e.g. `http://acme.localhost:8000`), with the same port as `php artisan serve` if you use the built-in server. Many systems resolve `*.localhost` to `127.0.0.1`; if yours does not, add a line to your hosts file for that hostname.

## Useful Artisan commands

| Command | Description |
|---------|-------------|
| `php artisan migrate` | Run **central** migrations only. |
| `php artisan tenants:list` | List tenants (provided by stancl/tenancy). |
| `php artisan tenants:migrate` | Migrate **all** tenant databases (useful after adding tenant migrations). |

## Troubleshooting

- **“Access denied” when creating a tenant**: Grant the MySQL user permission to create databases, or use a user with sufficient privileges.
- **Tenant URL does not load**: Confirm the `domains` table contains the exact host you type in the browser (scheme/port are separate; hostname must match).
- **Queue**: Tenant provisioning runs synchronously by default in `App\Providers\TenancyServiceProvider` (`shouldBeQueued(false)`). For production, consider a real queue worker and enabling queued jobs.

## Further reading

- [Tenancy for Laravel v3 documentation](https://tenancyforlaravel.com/docs/v3/introduction)
