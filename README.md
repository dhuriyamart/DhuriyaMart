# DhuriyaMart — Full-Stack Multi-Vendor E-Commerce Marketplace

[![Tests](https://img.shields.io/badge/PHPUnit%20Tests-30%20passed%20(130%20assertions)-brightgreen.svg)](#automated-testing)
[![Framework](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%20--%208.4-blue.svg)](https://php.net)
[![Currency](https://img.shields.io/badge/Currency-INR%20(%E2%82%B9)-orange.svg)](#money--currency-representation)
[![License](https://img.shields.io/badge/License-Proprietary-darkblue.svg)](#)

DhuriyaMart is a production-grade, full-stack multi-vendor e-commerce marketplace built with Laravel, MySQL, Blade, and Bootstrap 5. Designed specifically for the Indian market, it provides complete multi-shop order splitting, server-side pricing snapshots, role-based access control, Razorpay sandbox integration, Cash on Delivery, inventory management with concurrency locks, and an admin operations dashboard.

---

## Table of Contents
1. [Core Architectural Highlights](#core-architectural-highlights)
2. [User Roles & Permissions](#user-roles--permissions)
3. [Multi-Vendor Order Splitting Strategy](#multi-vendor-order-splitting-strategy)
4. [Money & Currency Representation](#money--currency-representation)
5. [Pre-Seeded Demo Accounts](#pre-seeded-demo-accounts)
6. [Technology Stack](#technology-stack)
7. [Directory Structure](#directory-structure)
8. [Local Development Setup](#local-development-setup)
9. [Automated Testing](#automated-testing)
10. [Payment Gateway & Webhook Testing](#payment-gateway--webhook-testing)
11. [Production Deployment Reference](#production-deployment-reference)

---

## Core Architectural Highlights

- **Multi-Vendor Isolation:** Customers can add products from multiple shops into a single basket. Upon checkout, the platform atomically generates **one parent customer order** and **independent seller sub-orders (`seller_orders`)**, complete with item-level price snapshots, commission deductions, and individual shipment tracking.
- **Strict Concurrency & Stock Safety:** Inventory operations execute inside database transactions utilizing row-level locks (`SELECT ... FOR UPDATE`). Insufficient stock immediately blocks checkout, strictly preventing negative inventory balances.
- **Audit & Security Trail:** Critical administrative operations (product moderation, seller approvals/suspensions, setting updates) automatically log immutable entries into `audit_logs` with old/new JSON diffs, user IDs, and client IP addresses.
- **Server-Side Financial Integrity:** Never trusts browser prices. Subtotals, volume limits, discount rules, GST tax calculations, and shipping thresholds are verified exclusively on the server in integer paise.

---

## User Roles & Permissions

The platform enforces 6 distinct user roles through server-side middleware (`role:slug`) and policies:

| Role Slug | Description | Access Level |
|---|---|---|
| `super-admin` | Platform Owners & Executives | Full unrestricted system access, settlements, audit logs, and settings. |
| `admin` | Operations & Catalog Team | Moderates products, approves/suspends sellers, oversees orders and refunds. |
| `seller` | Merchant Shop Owners | Manages shop profile, lists products (enters moderation), manages stock, dispatches orders, views settlements. Strictly isolated from other shops. |
| `customer` | End-consumer Buyers | Browses catalog, places orders, manages delivery addresses, tracks shipments, submits returns, writes reviews. |
| `support-staff` | Customer Support Team | Assists customers with order queries, returns, and ticket troubleshooting. |
| `finance-staff` | Accounting & Treasury | Reconciles merchant settlements, payment ledger entries, and commission accounting. |

---

## Multi-Vendor Order Splitting Strategy

When a customer checks out with products originating from multiple merchants:

```
[ Customer Cart (Items from Shop A + Shop B) ]
                     │
                     ▼
             [ Checkout Order ]
                     │
        ┌────────────┴────────────┐
        ▼                         ▼
[ Parent Order #DM-1001 ]   [ Order Addresses ]
(Customer billing total,    (Shipping snapshot)
 overall payment status)
        │
        ├─────────────────────────┐
        ▼                         ▼
[ Seller Order #SO-1001-A ]  [ Seller Order #SO-1001-B ]
• Belongs to Shop A          • Belongs to Shop B
• Shop A Line Items          • Shop B Line Items
• Shop A Subtotal & Tax      • Shop B Subtotal & Tax
• Platform Commission Take   • Platform Commission Take
• Net Seller Receivable      • Net Seller Receivable
• Independent Courier / AWB  • Independent Courier / AWB
```

---

## Money & Currency Representation

All monetary amounts across migrations, models, services, and transactions are stored as **strictly non-negative 64-bit integer paise (`*_paise`)**:
- ₹1.00 = `100` paise
- ₹1,499.00 = `149900` paise
- ₹0.50 = `50` paise

This completely eliminates IEEE 754 floating-point inaccuracies during multi-item calculations, discounts, and percentage-based commission deductions. Helper utilities are encapsulated in [`app/Support/Money.php`](file:///d:/DhuriyaMart/app/Support/Money.php):
- `Money::format(149900)` ➔ `"₹1,499.00"`
- `Money::toPaise(1499.00)` ➔ `149900`
- `Money::toRupees(149900)` ➔ `1499.00`

---

## Pre-Seeded Demo Accounts

The database seeder initializes 7 accounts covering all platform workflows:

| Role | Email | Password | Associated Entity |
|---|---|---|---|
| **Super Admin** | `superadmin@dhuriyamart.com` | `password123` | Full Administrative Privileges |
| **Admin** | `admin@dhuriyamart.com` | `password123` | Catalog & Seller Operations |
| **Seller 1** | `seller1@dhuriyamart.com` | `password123` | Shop: **TechNova Electronics** (Approved) |
| **Seller 2** | `seller2@dhuriyamart.com` | `password123` | Shop: **Vedic Roots Organics** (Approved) |
| **Customer** | `customer@dhuriyamart.com` | `password123` | Customer with saved Bengaluru address |
| **Support Staff**| `support@dhuriyamart.com` | `password123` | Support Desk Operations |
| **Finance Staff**| `finance@dhuriyamart.com` | `password123` | Finance & Ledger Operations |

*All seed passwords in local development are set to `password123`.*

---

## Technology Stack

- **Backend Framework:** Laravel 12.x (PHP 8.2 - 8.4)
- **Database:** MySQL 8.0+ with InnoDB storage engine, foreign keys, and indexes (SQLite supported for fast local tests)
- **Frontend Architecture:** Laravel Blade templates, HTML5, CSS3, Bootstrap 5.3, Bootstrap Icons
- **Payment Processing:** Razorpay (HMAC-SHA256 signature verification, webhook deduplication) + Cash on Delivery + Sandbox Test Simulator
- **Testing:** PHPUnit with in-memory database test isolation (`RefreshDatabase`)

---

## Directory Structure

```
dhuriyamart/
├── app/
│   ├── Http/
│   │   ├── Controllers/       # 18 Modular controllers (Storefront, Customer, Seller, Admin)
│   │   └── Middleware/        # RoleMiddleware, EnsureSellerApproved
│   ├── Models/                # 36 Eloquent models with integer paise casts & relations
│   ├── Policies/              # Authorization policies (Product, SellerOrder, Order, Shop)
│   ├── Services/              # Business logic services:
│   │   ├── CartService.php
│   │   ├── CatalogService.php
│   │   ├── CheckoutService.php
│   │   ├── CommissionService.php
│   │   ├── InventoryService.php
│   │   ├── OrderService.php
│   │   ├── PaymentService.php
│   │   ├── SettlementService.php
│   │   └── ShippingService.php
│   └── Support/
│       └── Money.php          # Currency conversion and formatting utilities
├── database/
│   ├── migrations/            # 14 Organized migrations with foreign keys and indexes
│   └── seeders/               # Comprehensive DatabaseSeeder with catalog and shops
├── resources/
│   └── views/                 # 42 Reusable Blade views:
│       ├── layouts/           # app.blade.php, admin.blade.php, seller.blade.php
│       ├── components/        # alerts, status-badge, product-card
│       ├── storefront/        # home, catalog, search, product-detail, cart, checkout...
│       ├── customer/          # dashboard, orders, order-details, invoice, addresses...
│       ├── seller/            # dashboard, products, inventory, orders, settlements...
│       └── admin/             # dashboard, products, categories, sellers, orders, settings...
├── routes/
│   └── web.php                # 86 Clean, protected RESTful web routes
└── tests/
    ├── Feature/               # 6 Comprehensive Feature test suites
    └── Unit/                  # Unit test suites (Money formatting)
```

---

## Local Development Setup

### 1. Clone & Install Dependencies
```bash
git clone https://github.com/your-username/dhuriyamart.git
cd dhuriyamart

# Install PHP dependencies
composer install
```

### 2. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

Configure your `.env` database parameters:
```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dhuriyamart
DB_USERNAME=root
DB_PASSWORD=your_password
```
*(For quick SQLite testing without MySQL, set `DB_CONNECTION=sqlite` and `touch database/database.sqlite`)*

### 3. Database Migration & Seeding
```bash
php artisan migrate:fresh --seed
```

### 4. Create Public Storage Symlink
```bash
php artisan storage:link
```

### 5. Start Development Server
```bash
php artisan serve
```
Open your browser at `http://127.0.0.1:8000`.

---

## Automated Testing

The platform includes automated feature and unit tests validating all core acceptance criteria:

```bash
php artisan test
```

### Verified Test Suites:
1. **`AuthAndRoleAccessTest`**: Verifies role permissions, guest redirects, 403 authorization guards, and seller onboarding checks.
2. **`MultiVendorCheckoutTest`**: Verifies multi-vendor cart splitting into 1 parent order and 2 distinct seller orders with correct item snapshots and inventory deduction.
3. **`SellerDataIsolationTest`**: Ensures Seller A cannot view, update, delete, or dispatch Seller B's products or orders.
4. **`ProductModerationTest`**: Confirms that newly submitted seller products enter the moderation queue, remain hidden from the storefront until approved, and write audit logs upon review.
5. **`InventoryManagementTest`**: Tests concurrency locks, stock decrementing, negative inventory prevention, and stock restoration on cancellation.
6. **`PaymentAndWebhookTest`**: Validates HMAC-SHA256 signature verification, duplicate webhook idempotency detection, and sandbox payment simulation.
7. **`CouponAndCommissionTest`**: Validates fixed and percentage coupon discounts, minimum order value requirements, discount caps, and commission calculations.
8. **`MoneyTest`**: Unit tests verifying paise-to-rupee and rupee-to-paise precision math.

---

## Payment Gateway & Webhook Testing

### Online Checkout Simulation:
- In test/development mode (`PAYMENT_SANDBOX_MODE=1`), selecting Razorpay during checkout redirects to the Gateway Simulation screen with a one-click **"Simulate Successful Payment"** button (`/payments/mock/{id}`).
- This fires the complete payment verification lifecycle, updates order and payment records, marks seller orders as `confirmed`, and records status history entries.

### Webhook Verification:
- Endpoint: `POST /webhooks/payments/razorpay`
- Webhook endpoints are automatically exempt from CSRF token verification in [`bootstrap/app.php`](file:///d:/DhuriyaMart/bootstrap/app.php).
- Duplicate webhook payloads with the same `event_id` are detected and processed idempotently without mutating order state twice.

---

## Production Deployment Reference

For deploying DhuriyaMart on an Ubuntu VPS with CloudPanel, Nginx, PHP-FPM, MySQL 8, SSL, Queue Workers, and Cron Jobs, refer to the complete deployment guide:
- 👉 [DEPLOYMENT.md](file:///d:/DhuriyaMart/DEPLOYMENT.md)
- 👉 [BACKUP_RESTORE.md](file:///d:/DhuriyaMart/BACKUP_RESTORE.md)
