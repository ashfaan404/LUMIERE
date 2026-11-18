UMIÈRE — Perfume E-Commerce Web Application
A Full-Stack PHP & MySQL E-Commerce Project

📖 Project Overview
LUMIÈRE is a full-stack, database-driven perfume e-commerce web application built with PHP, MySQL, HTML, CSS, and JavaScript. It combines an online fragrance storefront with dedicated customer, administrator, and staff workflows.

The project demonstrates practical web development concepts such as database integration, account authentication, product management, shopping cart operations, order processing, and integration with third-party services.
✨ Key Features
🛍️ Customer Experience
- Explore perfume collections, product details, and promotions.
- Search, filter, and compare fragrances.
- Manage a shopping cart and wishlist.
- Register, sign in, and manage account details and delivery addresses.
- Place orders and view order information and tracking updates.
- Write product reviews and submit customer support tickets.
🛠️ Administration Dashboard
- Manage products, categories, and promotions.
- Review and update customer orders.
- Manage staff accounts and customer information.
- Access reports, payment history, reviews, notifications, and support tickets.
👥 Staff Portal
- Separate staff login and dashboard.
- View assigned operational pages, orders, and order details.
- Handle customer support tickets and review notifications.
🔗 Integrations & Data
- MySQL: Relational storage for catalog, account, order, and operational data.
- Stripe Checkout: Payment integration designed for test-mode use.
- PHPMailer: SMTP-based account and OTP email workflows.
- draw.io: Editable entity-relationship diagram (ERD new.drawio).
🧰 Technology Stack
Area	Technology
Frontend	HTML5, CSS3, JavaScript
Backend	PHP
Database	MySQL, PHP mysqli
Web server	Apache
Email integration	PHPMailer, SMTP
Payment integration	Stripe API / Stripe Checkout
Database design	draw.io (ERD)
Development environment	VS Code, Git, GitHub


🗂️ Project Folder Structure
LUMIERE/
├── actions/                       # Customer actions, cart and checkout handlers
├── admin/                         # Administrator panel
│   ├── actions/                   # Product, order and staff management actions
│   ├── assets/                    # Admin CSS and JavaScript
│   ├── includes/                  # Admin authentication and shared layout
│   ├── pages/                     # Dashboard, catalog, reports and management pages
│   ├── login.php
│   └── logout.php
├── assets/                        # Shared public assets
│   ├── css/                       # Stylesheets
│   ├── images/                    # Product and site images
│   ├── js/                        # Frontend JavaScript
│   └── uploads/                   # Runtime uploads (excluded from public package)
├── customer/                      # Profiles, addresses, orders and wishlists
├── includes/                      # Shared application logic and configuration
│   ├── app_config.php
│   ├── config.local.example.php
│   ├── config_email.php
│   ├── db.php
│   ├── functions.php
│   ├── header.php
│   └── footer.php
├── pages/                         # Storefront and shopping pages
│   ├── home.php
│   ├── shop.php
│   ├── product.php
│   ├── cart.php
│   ├── checkout.php
│   └── ...
├── src/                           # Bundled PHPMailer classes
├── staff/                         # Staff panel, actions and pages
│   ├── actions/
│   ├── includes/
│   ├── pages/
│   ├── login.php
│   └── logout.php
├── .gitignore                     # Excluded local/private files
├── database.sql                   # Database schema and demo catalog records
├── ERD new.drawio                 # Database entity-relationship diagram
├── index.php                      # Application entry point
└── README.md                      # Project documentation
🗃️ Database Design
The project includes a relational MySQL schema in database.sql and an editable ER diagram in ERD new.drawio. The database covers products, categories, users, administrators, staff, orders, order items, addresses, wishlists, promotions, reviews, and support tickets, along with related workflows.
🧪 Project Status
This repository is intended for local development and portfolio demonstration. It includes the application source code and database design. Complete production-readiness, security auditing, end-to-end tests, and live-payment verification have not been established.
🚀 Future Improvements
- Expand automated testing for account, shopping, and order workflows.
- Review payment confirmation and authorization checks before deployment.
- Improve error handling, responsiveness, and performance monitoring.
👨‍💻 Repository
GitHub: @ashfaan404
Project: LUMIERE
