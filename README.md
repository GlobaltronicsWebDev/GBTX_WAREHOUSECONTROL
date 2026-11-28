# Globaltronics Warehouse Management System

Laravel application for managing user access and Stock Requisition Form (SRF) workflows.

SRFs retain their item details, approvals, history, and printable forms. SRF requests do not automatically change inventory; actual stock movements are recorded separately.

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

## Delivery receipts

Create Client and Internal delivery receipts under Logistics. Each type has its own automatically generated DR number. Saving or editing a draft does not change inventory. Releasing a draft checks current stock, deducts the three inventory balances, and records a linked Stock Out transaction; do not record a separate Stock Out for the same delivery. Released receipts cannot be edited or released again. Printed drafts are marked as not released. The Client template is for operational use and should be reviewed before use as an official receipt.

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
