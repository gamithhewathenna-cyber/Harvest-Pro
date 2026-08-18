# Tea Estate Management System

A complete PHP + MySQL web application for managing tea estates, workers, daily
plucking assignments, payroll, expenses, service cycles, reminders and reports.
Built to run on standard cPanel shared hosting.

## Requirements
- PHP 7.4+ (tested on PHP 8.3) with PDO MySQL extension
- MySQL 5.7+ / MariaDB 10+
- Any cPanel host (or any Apache/PHP server)

## Installation on cPanel

1. **Upload the files**
   - Compress not needed — upload the whole folder's contents into `public_html`
     (or a subfolder like `public_html/estate`).

2. **Create the database**
   - cPanel → *MySQL Databases*
   - Create a new database (e.g. `tea_estate`)
   - Create a new user with a password
   - Add the user to the database with **All Privileges**
   - Note the full names cPanel gives you (usually prefixed, e.g. `cpuser_tea_estate`
     and `cpuser_teauser`).

3. **Configure**
   - Open `config/config.php` in cPanel's File Manager (Edit)
   - Set `DB_NAME`, `DB_USER`, `DB_PASS` to the values from step 2
   - `DB_HOST` stays `localhost` on almost all cPanel hosts
   - Save.

4. **Run the installer**
   - Visit `https://yourdomain.com/install.php` (adjust path if in a subfolder)
   - Click **Run Installer** — this creates all tables and seed data.

5. **Log in**
   - Email: `admin@estate.local`
   - Password: `admin123`

6. **Secure it**
   - **Delete `install.php`** from the server.
   - Go to *User Management* and change the admin password (Set/Reset Password),
     or create your own Owner account and disable the default.

## Default login
`admin@estate.local` / `admin123` (change immediately after first login)

## Roles
Owner, Administrator, Estate Manager, Supervisor, Accountant, Viewer.
Write/approve actions are permission-gated; Viewer is read-only.

## Modules
- Dashboard with live KPIs, date filters, charts (Payroll vs Expenses,
  Harvest by Section), Top Workers, Upcoming Events, Expense Breakdown
- Daily Assignments with mobile-optimized bulk entry (auto-calculates
  Plucking Pay = KG × Rate + Allowance − Deduction)
- Estates, Sections, Employees, Expenses, Reminders, Service Cycles
- Payroll auto-generated from assignments (Draft → Calculated → Approved → Paid)
- Reports (Harvest, Worker, Section, Expense, Payroll, Profitability) with
  CSV export and Print/PDF

## Notes
- All dashboard numbers are computed from operational records — nothing is
  hard-coded.
- Tea selling price (for profitability) is stored in Settings
  (`tea_price_per_kg`, default 300) and can be overridden per report.
- Set the timezone in `config/config.php` if you are not in Asia/Colombo.

## Folder structure
```
config/config.php      DB credentials (edit this)
database.sql           Schema + seed (imported by install.php)
install.php            One-time installer (DELETE after use)
index.php login.php logout.php
dashboard.php assignments.php estates.php employees.php
expenses.php reminders.php service.php users.php payroll.php reports.php
api/                   JSON endpoints
includes/              auth, layout, shared helpers
assets/css assets/js   front-end
```
