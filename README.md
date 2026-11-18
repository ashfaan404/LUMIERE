# LUMIÈRE — Perfume E-commerce Web Application

A full-stack PHP and MySQL perfume store with customer shopping workflows, admin management, and staff-facing tools. This repository is intended for **local development and portfolio demonstration**; it has not been independently validated for production deployment.

## Features

- **Customers:** Browse fragrances, search/filter products, compare items, add to cart and wishlist, register/login, manage addresses, place orders, leave reviews, and submit support tickets.
- **Admin dashboard:** Manage products, categories, promotions, orders, staff members, support tickets, notifications, and reports.
- **Staff area:** Staff login and access to operational order and ticket pages.
- **Integrations:** PHPMailer for account emails and OTPs; Stripe Checkout integration for test payments (requires your own keys).
- **Database:** MySQL schema in `database.sql`; entity-relationship diagram in `ERD new.drawio`.

## Technology

PHP, MySQL, HTML, CSS, JavaScript, PHPMailer, and Stripe API.

## Screenshots

Add authentic screenshots in `docs/screenshots/` after running the application locally. Do not upload screenshots containing real customer records, credentials, or payment information.

## Local setup (e.g. XAMPP on Windows)

1. Install XAMPP or a compatible PHP + MySQL + Apache environment. The application uses PHP `mysqli`, `curl`, sessions and the OpenSSL/TLS stack. A current supported PHP 8.x version is recommended; this source has not undergone a complete compatibility test.
2. Place the project at `C:\xampp\htdocs\LUMIERE` (or choose another web directory).
3. Start **Apache** and **MySQL**.
4. In phpMyAdmin, create a database named `perfume_shop` with UTF-8 support. Import `database.sql`.
5. Copy `includes/config.local.example.php` to `includes/config.local.php` and enter your own database settings, base URL, and optional Stripe/SMTP credentials. **The local file is intentionally ignored by Git.**
6. Set `APP_BASE_URL` in the local configuration to match your folder path, e.g. `http://localhost/LUMIERE`.
7. Visit `http://localhost/LUMIERE/` in your browser.

### Admin account (required for admin login)

For safety, `database.sql` does **not** contain a usable default admin or staff account. To create a local admin, generate a fresh password hash using PHP CLI:

```bash
php -r "echo password_hash('REPLACE_WITH_YOUR_NEW_LOCAL_PASSWORD', PASSWORD_DEFAULT), PHP_EOL;"
```

Then in phpMyAdmin insert a row into `admins` with an admin name, email and the resulting hash in `admin_password`. Do not commit the password or the hash generated for your account. Create staff accounts via authorized admin workflows.

### Payment and email setup

Use only **new credentials you control**. Leave the optional Stripe and SMTP settings blank if you only need to browse the storefront. Payment and email features will not work until configured. Use Stripe **test mode**, not live payments, for demonstrations. Some payment features and email workflows require further validation.

> Security note: This archive originally contained exposed test credentials. The public-ready copy replaces them with local configuration, but the original credentials must still be revoked/rotated by their owners.

## Folder structure

```text
LUMIERE/
├── actions/                # Customer and checkout actions
├── admin/                  # Admin pages and workflows
├── assets/                 # Stylesheets, scripts, demo product images
├── customer/               # Customer dashboard, account and orders
├── docs/screenshots/       # Add safe screenshots of your work
├── includes/               # Shared database, email, session and helpers
├── pages/                  # Customer-facing storefront pages
├── src/                    # Bundled PHPMailer classes
├── staff/                  # Staff workflows
├── database.sql            # Schema and public-safe seed product data
├── ERD new.drawio          # Database design diagram
└── index.php               # Main entry point
```

## Known limitations and follow-up work

- Local PHP/MySQL application; hosting and production security have not been audited.
- Stripe success redirects and order fulfillment should be reviewed carefully before using real payments; consider verifying webhook signatures and idempotency.
- Only example catalog records are supplied, not real user/order records.
- The upload-ready copy intentionally omits runtime profile and promotion uploads to avoid publishing user data.
- Add automated tests, app screenshots, and end-to-end setup validation before describing this as a production-ready application.

## Attribution and licensing

The uploaded archive's original README credited **[@RaviPraveen](https://github.com/RaviPraveen)**. Confirm the actual author(s), your contribution, and permission to redistribute this code before publishing it publicly. Document other contributors appropriately; do not present someone else's work as solely your own. The provided archive did not include an application-level license file, so **no license is assumed or granted by this README**. The bundled PHPMailer code carries its own LGPL notice in the source headers.
