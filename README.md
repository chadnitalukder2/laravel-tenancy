# Tenancy

Laravel multi-tenant application built with `stancl/tenancy` (v3) using a **multi-database** architecture.

## Overview

- Central application stores tenant metadata (`tenants`, `domains`) and central users.
- Each tenant gets its own domain and separate database.
- Tenant databases are provisioned during tenant registration.
- Tenant-specific routes live in `routes/tenant.php`.

## Tech Stack

- PHP 8.1+
- Laravel 10
- MySQL 5.7+/8.x
- Node.js 18+ and npm
- `stancl/tenancy` v3

## Quick Start

```bash
git clone <repository-url> tenancy
cd tenancy
composer install
npm install
copy .env.example .env
php artisan key:generate
php artisan migrate
npm run dev
php artisan serve
```

Open the app at `http://localhost:8000`.

## Tenant Registration Flow

1. Open `http://localhost:8000/tenancy-register`.
2. Submit tenant details (for example, subdomain `acme`).
3. The app creates:
   - tenant record
   - tenant domain (for example, `acme.localhost`)
   - tenant database
   - tenant migrations from `database/migrations/tenant`
4. Access tenant app via tenant URL.

## Useful Commands

```bash
php artisan migrate
php artisan tenants:list
php artisan tenants:migrate
php artisan optimize:clear
```

## Documentation

- Full setup, domain configuration, and troubleshooting:
  - See `INSTALLATION.md`

## Notes

- Local subdomain resolution may require hosts-file entries.
- DB user must have permission to create tenant databases.

## License

This project is open-sourced software licensed under the MIT license.
