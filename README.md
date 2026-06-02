# Autoease Car Showroom (Final)

A lightweight PHP web application for managing a car showroom inventory, categories, orders, and customer feedback. Includes an admin dashboard for CRUD operations, order management, and a simple contact‑support interface.

---

## Overview

Autoease Car Showroom provides a clean, responsive interface for both customers and administrators:

- **Customers** can browse cars, view details, place orders, and contact support.
- **Admins** can manage cars, categories, users, orders, and view payment records through a secure backend.

The project is built with core PHP, MySQL, and vanilla CSS, making it easy to deploy on any standard LAMP stack.

---

## Features

| ✅ | Feature |
|---|---------|
| ✔️ | **Admin Authentication** – Secure login/logout with session handling. |
| ✔️ | **Car Management** – Add, edit, delete car listings with image uploads. |
| ✔️ | **Category Management** – Organize cars into categories. |
| ✔️ | **Order Processing** – View, update, and cancel orders. |
| ✔️ | **Payment Overview** – Simple table view of payment records. |
| ✔️ | **Feedback System** – Admin can read and reply to customer feedback. |
| ✔️ | **Contact Support** – Front‑end form for users to reach out to the team. |
| ✔️ | **Responsive Design** – Basic CSS styling for mobile‑friendly layout. |

---

## Tech Stack

| Component | Technology |
|-----------|------------|
| **Backend** | PHP 7.x / 8.x |
| **Database** | MySQL (SQL script in `Database/atuoease_db.sql`) |
| **Frontend** | HTML5, CSS3 |
| **Server** | Apache / Nginx (LAMP / LEMP) |
| **Version Control** | Git (GitHub) |

---

## Installation

### Prerequisites

- PHP 7.4+ (with `mysqli` extension)
- MySQL 5.7+ (or MariaDB)
- Web server (Apache/Nginx) with PHP support
- Composer (optional, only if you plan to add dependencies)

### Steps

1. **Clone the repository**

   ```bash
   git clone https://github.com/your-username/Autoease-carshowroom-final.git
   cd Autoease-carshowroom-final
   ```

2. **Create the database**

   ```sql
   -- In your MySQL client (e.g., phpMyAdmin, MySQL Shell)
   SOURCE Database/atuoease_db.sql;
   ```

   This script creates the required tables and some seed data.

3. **Configure database connection**

   - Open `config.php` (root) and `admin/config.php`.
   - Replace placeholder values with your own credentials:

     ```php
     define('DB_HOST', 'YOUR_DB_HOST');
     define('DB_USER', 'YOUR_DB_USER');
     define('DB_PASS', 'YOUR_DB_PASSWORD');
     define('DB_NAME', 'YOUR_DB_NAME');
     ```

4. **Set file permissions**

   ```bash
   # Allow PHP to write uploaded images
   chmod -R 755 admin/uploads
   ```

5. **Deploy**

   - Place the project folder in your web server’s document root (e.g., `/var/www/html/autoease`).
   - Ensure the server points to `index.php` as the entry point.

6. **Optional – Composer autoload (if you add packages)**

   ```bash
   composer install
   ```

---

## Usage

### Admin

1. Navigate to `http://your-domain.com/admin/admin_login.php`.
2. Log in with the credentials you set in the `admin_users` table (default admin user can be created via the SQL script or manually inserted).
3. Use the navigation bar to:
   - **Add / Edit Cars** – `admin/add_car.php`, `admin/edit