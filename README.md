# LUMIERE

A modern PHP-based web application featuring elegant styling and interactive functionality.

## Overview

LUMIERE is a full-stack perfume e-commerce platform built with PHP, CSS, and JavaScript. The project combines a responsive storefront with customer accounts, an administrator dashboard, and a separate staff portal.

It provides a complete database-backed shopping experience, including authentication, product browsing, cart and wishlist management, checkout, order tracking, reviews, and support tickets.

## Technology Stack

- **Backend:** PHP 8+, MySQL / mysqli
- **Frontend:** HTML5, CSS3, JavaScript
- **Server:** Apache
- **Email:** PHPMailer with SMTP
- **Payments:** Stripe Checkout
- **Database Design:** draw.io

## Features

- Customer registration, login, profile, and address management
- Responsive perfume catalog with search, filtering, and comparison
- Shopping cart, wishlist, promotions, and checkout
- Stripe test payments and order tracking
- Product reviews and customer support tickets
- Administrator dashboard for products, categories, users, staff, and reports
- Dedicated staff dashboard for orders and support management

## Getting Started

### Prerequisites

- PHP 8 or newer
- Apache web server
- MySQL or MariaDB
- XAMPP, WAMP, or another local PHP environment
- Git

### Installation

1. Clone the repository:

```bash
git clone https://github.com/ashfaan404/LUMIERE.git
cd LUMIERE
```

2. Move the project into your web server directory, such as `htdocs/LUMIERE`.

3. Create the database and import `database.sql` through phpMyAdmin or the MySQL CLI.

4. Copy the local configuration template:

```text
includes/config.local.example.php
```

Create `includes/config.local.php` and add your database and integration settings:

```php
'DB_HOST' => 'localhost',
'DB_USER' => 'root',
'DB_PASS' => '',
'DB_NAME' => 'perfume_shop',
'APP_BASE_URL' => 'http://localhost/LUMIERE',
```

5. Start Apache and MySQL, then open:

```text
http://localhost/LUMIERE
```

## Project Structure

```text
LUMIERE/
├── actions/            # Shopping and account actions
├── admin/              # Administrator dashboard
├── assets/             # CSS, JavaScript, and images
├── customer/           # Customer account pages
├── docs/               # Documentation assets
├── includes/           # Shared configuration and helpers
├── pages/              # Public storefront pages
├── src/                # PHPMailer source files
├── staff/              # Staff portal
├── database.sql        # Database schema and demo data
├── ERD new.drawio      # Database entity relationship diagram
└── index.php           # Application entry point
```

## Usage

The application is organized around three roles:

- **Customers:** Browse products, manage orders, and use account features.
- **Administrators:** Manage catalog data, customers, staff, reports, and payments.
- **Staff:** Handle assigned orders and customer support tickets.

Set up your own SMTP and Stripe test credentials before using email or payment features. Stripe and SMTP credentials must remain in `includes/config.local.php` or environment variables and should never be committed.

## Development

Contribute by working from a feature branch and testing the application in a local PHP environment.

```bash
git checkout -b feature/your-feature
git add .
git commit -m "Add your feature"
git push origin feature/your-feature
```

### Code Style

- Follow the existing PHP and file naming conventions.
- Keep database credentials outside the repository.
- Use semantic HTML and reusable CSS classes.
- Keep application logic out of view files where possible.
- Test account, checkout, and payment flows before submitting changes.

## License

This project is available for educational and portfolio use. Add a license file if you intend to distribute it publicly under specific terms.

---

**Project:** [LUMIERE](https://github.com/ashfaan404/LUMIERE)
**Repository:** [github.com/ashfaan404/LUMIERE](https://github.com/ashfaan404/LUMIERE)
