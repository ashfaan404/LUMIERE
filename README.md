LUMIÈRE — Perfume E-Commerce Web Application
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
│
├── actions/                      # Customer actions and checkout handlers
│   ├── add_to_cart.php
│   ├── login_action.php
│   ├── place_order.php
│   ├── register_action.php
│   ├── stripe_success.php
│   └── ...
│
├── admin/                        # Administrator panel
│   ├── actions/                  # Admin CRUD and management handlers
│   ├── assets/                   # Admin-specific CSS and JavaScript
│   ├── includes/                 # Admin authentication, header, footer
│   ├── pages/                    # Dashboard, products, orders, reports, etc.
│   ├── login.php
│   └── logout.php
│
├── assets/                       # Shared frontend assets
│   ├── css/                      # Application stylesheets
│   ├── images/products/          # Demonstration product images
│   ├── js/                       # Frontend scripts
│   └── uploads/                  # Runtime profile/promotion uploads (excluded)
│
├── customer/                     # Customer account area
│   ├── actions/                  # Customer account handlers
│   ├── addresses.php
│   ├── order-details.php
│   ├── orders.php
│   ├── profile.php
│   └── wishlist.php
│
├── docs/
│   └── screenshots/             # Add genuine screenshots of the running app
│
├── includes/                     # Shared application logic and configuration
│   ├── app_config.php            # Reads environment/local settings
│   ├── config.local.example.php  # Template for private local settings
│   ├── config_email.php          # Email integration
│   ├── db.php                    # Database connection
│   ├── functions.php             # Shared helpers
│   ├── header.php
│   └── footer.php
│
├── pages/                        # Public-facing pages
│   ├── cart.php
│   ├── checkout.php
│   ├── compare.php
│   ├── home.php
│   ├── login.php
│   ├── product.php
│   ├── register.php
│   ├── shop.php
│   ├── support.php
│   ├── track_order.php
│   └── ...
│
├── src/                          # Bundled PHPMailer library classes
│
├── staff/                        # Staff portal
│   ├── actions/                  # Staff actions
│   ├── includes/                 # Staff authentication and layout
│   ├── pages/                    # Staff dashboard and operations
│   ├── login.php
│   └── logout.php
│
├── .gitignore                    # Excludes private and generated files
├── database.sql                  # MySQL schema and demo catalog data
├── ERD new.drawio                # Editable database diagram
├── index.php                     # Main application entry point
└── README.md                     # Project documentation


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
