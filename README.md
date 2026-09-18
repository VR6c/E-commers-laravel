# TVR eCommerce

[![Laravel](https://img.shields.io/badge/Laravel-10.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.1%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-Ready-336791?style=for-the-badge&logo=postgresql&logoColor=white)](https://www.postgresql.org)
[![Sanctum](https://img.shields.io/badge/API-Laravel_Sanctum-red?style=for-the-badge)](https://laravel.com/docs/sanctum)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg?style=for-the-badge)](LICENSE)

A modern, high-performance Multi-Vendor and Multi-Lingual eCommerce platform and RESTful API built with **Laravel 10**. Engineered to power both rich web storefronts and companion mobile applications (such as Flutter) with clean separation of roles, fast response times, and production-ready architecture.

---

## 🌟 Key Features

### 🛍️ Multi-Vendor Architecture
- **Dedicated Portals**: Independent, secure access for **Super Admin**, **Vendors/Sellers**, and **Customers**.
- **Vendor Storefronts**: Individual vendor profiles, product management, inventory tracking, and order fulfillment.

### 📱 Mobile-First REST API
- **Sanctum Authentication**: Secure bearer token authentication, token refresh, and guest/customer sessions.
- **Customer Profiles**: Mobile profile management, address books, and profile avatar upload/deletion.
- **Product Discovery**: Fast search suggestions, category browsing, brand filtering, and related products.
- **Mobile Checkout**: Full checkout pipeline, coupon redemption, and order tracking endpoints for mobile clients.

### 💳 Payment Gateways
- **ABA PayWay**: Integrated KHQR and card payment workflows with webhook confirmation.
- **Stripe**: Card checkout integration.

### 🌐 Multi-Lingual & Multi-Currency
- Dynamic locale switching and currency helper utilities.
- Flexible pricing and tax calculations across vendors.

### ⚡ Optimized Performance
- **Optimized Queries**: Composite database indexes for high-volume catalogs and filtered searches.
- **Eager Loading**: Elimination of N+1 query bottlenecks on products, variants, and vendor relationships.
- **Queued Background Work**: Asynchronous email delivery, notifications, and OTP mailers via database queues.
- **Caching**: Multi-level caching (Redis/File) for categories, banners, site settings, and search suggestions.

### 📄 PDF Receipts & Content
- Automated PDF invoice and order receipt generation powered by **DOMPDF**.
- Recipe management system with ingredient listings, cooking instructions, and downloadable PDF cards.

---

## 🛠️ Tech Stack

| Layer | Technologies |
| :--- | :--- |
| **Backend Framework** | Laravel 10.x, PHP 8.1+ |
| **Database** | PostgreSQL (Neon Cloud / Local PGSQL) & MySQL compatible |
| **API & Auth** | Laravel Sanctum (Token Auth) & Session Authentication |
| **Web Frontend** | Blade Templates (Xylo Theme), Livewire 3, Bootstrap 5, Sass, Vite |
| **Data Tables** | Yajra Laravel DataTables |
| **PDF Generation** | Barryvdh Laravel DOMPDF |
| **Payments** | ABA PayWay (KHQR), Stripe |
| **Mobile Client** | REST API designed for Flutter companion application |

---

## 📂 System Architecture & Portals

```text
├── Admin Panel (/admin)       -> Dashboard, Catalog, Vendors, Orders, Site Settings, Payment Configs
├── Vendor Panel (/vendor)     -> Vendor Dashboard, Product Catalog, Orders, Reviews, Profile
├── Storefront (/)             -> Customer Web Shop (Xylo theme), Cart, Checkout, Wishlist, Recipes
└── REST API (/api/*)          -> Mobile Endpoints for Flutter App (Auth, Catalog, Cart, Orders)
```

---

## 🚀 Getting Started

### Prerequisites
- **PHP** >= 8.1 (with `pdo_pgsql`, `pdo_mysql`, `bcmath`, `mbstring`, `openssl`, `curl`)
- **Composer** >= 2.x
- **Node.js** >= 18.x & **NPM**
- **PostgreSQL** or **MySQL** database server

### 1. Clone & Install Dependencies
```bash
git clone https://github.com/VR6c/E-commers-laravel.git
cd E-commers-laravel

# Install PHP dependencies
composer install

# Install frontend dependencies
npm install
```

### 2. Environment Configuration
Create your local environment file:
```bash
cp .env.example .env  # or create .env directly
```

Configure your database connection in `.env`:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 3. Generate Key & Run Migrations
```bash
# Generate application key
php artisan key:generate

# Run migrations and seed sample data
php artisan migrate --seed

# Create storage symlink
php artisan storage:link
```

### 4. Build Frontend Assets
```bash
# For development
npm run dev

# For production build
npm run build
```

### 5. Run the Application
```bash
# Start local development server
php artisan serve
```
Visit `http://localhost:8000` to view the web storefront.

---

## ⚡ Background Workers & Caching

To process background emails, password resets, and asynchronous jobs:
```bash
php artisan queue:work --queue=default,emails --tries=3
```

For production deployment optimization:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 🔑 Default Credentials

After seeding the database (`php artisan db:seed`):

- **Admin Dashboard**: `http://localhost:8000/admin`
  - **Email**: `admin@example.com`
  - **Password**: `password`

---

## 📄 License

This project is open-source software licensed under the [TVR](LICENSE).
