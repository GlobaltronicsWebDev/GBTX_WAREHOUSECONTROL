# Globaltronics Warehouse Management System

Laravel application for managing user access and Stock Requisition Form (SRF) workflows.

SRFs retain their item details, approvals, history, and printable forms. Item details are stored on each request; the application does not track inventory quantities or reservations.

## Requirements

- PHP 8.3+
- Composer
- Node.js and npm
- A database configured in `.env`

## Setup

```sh
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
```

Configure database credentials in `.env` before running migrations.

## Access roles

- **IT Administrator**: manages user accounts and roles.
- **Warehouse Admin**: reviews and approves SRFs.
- **Technical**: submits and views SRFs.
- **Warehouse Staff**: account role for warehouse personnel.

## Development

```sh
php artisan serve
npm run dev
```

Run the test suite with:

```sh
php artisan test
```
