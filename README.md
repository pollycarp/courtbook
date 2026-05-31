# CourtBook — Sports Court Booking System

> **Academic Project** | FdSc / Cert HE Computing | Professional Development (S2-PFD200)
> Task 2: Mini Project Development & Task 3: Digital Portfolio

A full-stack web application for booking football and tennis courts at a multi-sport facility. Built as a self-directed mini project to address identified skill gaps in full-stack web development, database management, algorithmic logic, and web security.

---

## Live Demo

**Live site:** https://courtbook.infinityfreeapp.com
**GitHub:** https://github.com/YOUR_USERNAME/courtbook

---

## Project Overview

CourtBook allows users to check court availability by date, time, and sport type, make and cancel bookings, and receive a printable booking confirmation with a unique reference number. An admin panel provides full management of courts, bookings, and registered users.

The project was proposed in Task 1 as a practical way to consolidate and extend skills in HTML, CSS, JavaScript, PHP, and SQL — while specifically targeting two self-identified skill gaps: **algorithmic thinking / data management** and **cybersecurity fundamentals**.

---

## Features

### User-Facing
- **Real-time availability check** — filter by date, time slot, and sport type (football / tennis)
- **Instant booking** — SweetAlert2 modal form with client and server-side validation
- **Booking reference** — unique `CB-YYYY-NNNN` reference generated on confirmation
- **Printable receipt** — confirmation page with Print / Save as PDF button
- **Booking cancellation** — users can cancel future bookings with ownership verification
- **User accounts** — register, log in, log out with bcrypt-hashed passwords
- **Personal bookings** — "My Bookings" page shows only the logged-in user's reservations
- **Weekly calendar view** — 7-day × 11-slot colour-coded availability grid; click any free cell to pre-fill the booking form

### Admin Panel (`/admin/`)
- Secure admin login (role-based, separate from user accounts)
- Dashboard with live stats: total bookings, today, this week, active courts, registered users, most popular court
- Full bookings table with filters (name, sport, date) and delete capability
- Court management: add courts, toggle active/inactive, delete
- User management: view all registered users and their booking counts

### Security Implemented
- PDO prepared statements throughout (SQL injection prevention)
- `password_hash()` / `password_verify()` with bcrypt for all passwords
- Server-side input validation on all form submissions
- Past-date and elapsed time-slot rejection
- Session-based authentication with `session_regenerate_id()` on login
- XSS prevention via `htmlspecialchars()` on all output
- Race condition protection on booking (double-check before insert)

---

## Tech Stack

| Layer | Technology |
|---|---|
| Frontend | HTML5, Tailwind CSS (CDN), Alpine.js v3, Motion One (animations) |
| UI / Dialogs | SweetAlert2 v11 |
| Backend | PHP 8+ |
| Database | MySQL (PDO) |
| Server | Apache (XAMPP for local development) |
| Version Control | Git / GitHub |

---

## Project Structure

```
courtbook/
├── index.php                  # Homepage — availability check & booking form
├── book.php                   # POST endpoint — creates a booking
├── check_availability.php     # AJAX endpoint — returns available courts as JSON
├── confirmation.php           # Booking receipt page
├── cancel_booking.php         # POST endpoint — cancels a booking
├── my_bookings.php            # User's personal bookings list
├── week_view.php              # 7-day availability calendar grid
├── register.php               # User registration
├── login.php                  # User login
├── logout.php                 # Session destroy
├── db_connect.example.php     # Credentials template (see setup below)
├── style.css                  # Minimal overrides (Tailwind handles most styling)
├── script.js                  # Legacy — superseded by Alpine.js inline logic
├── admin/
│   ├── login.php              # Admin login
│   ├── logout.php
│   ├── index.php              # Dashboard with stats
│   ├── bookings.php           # All bookings with filters
│   ├── courts.php             # Court management (add / toggle / delete)
│   ├── users.php              # Registered users list
│   ├── delete_booking.php     # Admin delete endpoint
│   ├── auth.php               # Shared admin session guard
│   └── partials/nav.php       # Admin navigation
└── migrations/
    ├── 001_add_reference_column.sql
    ├── 002_add_users_table.sql
    └── 003_add_admin_flag.sql
```

---

## Local Setup (XAMPP)

### Prerequisites
- XAMPP with Apache and MySQL running
- PHP 8.0+

### 1. Clone the repository

```bash
git clone https://github.com/YOUR_USERNAME/courtbook.git
cd courtbook
```

### 2. Configure the database

Copy the credentials template and fill in your local values:

```bash
cp db_connect.example.php db_connect.php
```

Edit `db_connect.php`:

```php
$host     = 'localhost';       // or localhost:3307 if using non-default port
$dbname   = 'courtbook_db';
$username = 'root';
$password = '';
```

### 3. Create the database

Open **phpMyAdmin** → create a database named `courtbook_db`.

Create the base tables:

```sql
CREATE TABLE courts (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    sport_type ENUM('football','tennis') NOT NULL,
    is_active  TINYINT(1) NOT NULL DEFAULT 1
);

CREATE TABLE bookings (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    user_id        INT NULL,
    court_id       INT NOT NULL,
    customer_name  VARCHAR(100) NOT NULL,
    customer_email VARCHAR(150) NULL,
    booking_date   DATE NOT NULL,
    time_slot      TIME NOT NULL,
    reference      VARCHAR(20) NULL UNIQUE,
    FOREIGN KEY (court_id) REFERENCES courts(id)
);
```

### 4. Run the migrations (in order)

In phpMyAdmin → SQL tab, run each file from the `migrations/` folder:

```sql
-- 001: booking reference column (skip if already in CREATE TABLE above)
ALTER TABLE bookings ADD COLUMN reference VARCHAR(20) NULL UNIQUE AFTER time_slot;

-- 002: user accounts
CREATE TABLE IF NOT EXISTS users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    full_name     VARCHAR(100) NOT NULL,
    email         VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    is_admin      TINYINT(1) NOT NULL DEFAULT 0,
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP
);
ALTER TABLE bookings
    ADD COLUMN user_id INT NULL AFTER id,
    ADD CONSTRAINT fk_bookings_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL;

-- 003: admin flag (skip if already in CREATE TABLE above)
ALTER TABLE users ADD COLUMN is_admin TINYINT(1) NOT NULL DEFAULT 0 AFTER password_hash;
```

### 5. Seed some courts

```sql
INSERT INTO courts (name, sport_type) VALUES
    ('Pitch A',   'football'),
    ('Pitch B',   'football'),
    ('Court 1',   'tennis'),
    ('Court 2',   'tennis');
```

### 6. Create your admin account

Register at `/register.php`, then promote yourself in phpMyAdmin:

```sql
UPDATE users SET is_admin = 1 WHERE email = 'your@email.com';
```

Admin panel is at `/admin/login.php`.

### 7. Open the app

```
http://localhost/courtbook/
```

---

## Deployment (InfinityFree)

1. Sign up at **infinityfree.com** and create a hosting account
2. Create a MySQL database in the control panel — note the host, name, username, password
3. Open phpMyAdmin from the control panel and run all migrations
4. Update `db_connect.php` with the InfinityFree credentials
5. Upload all files via FTP (FileZilla) into the `htdocs` folder
6. Visit your subdomain — e.g. `https://courtbook.infinityfreeapp.com`

---

## Skills Demonstrated

This project was built to directly address gaps identified in the Task 1 self-assessment:

| Skill Gap Identified | How It Was Addressed |
|---|---|
| Algorithmic thinking & data management | Weekly calendar algorithm (7-day × 11-slot bulk query + availability matrix), booking conflict detection, sequential reference number generation |
| Cybersecurity fundamentals | bcrypt password hashing, PDO prepared statements, session security, XSS prevention, input validation, past-date enforcement |
| Applied SQL | JOIN queries, GROUP BY aggregations, COUNT statistics, FK constraints, role-based access |
| User-centric design | Responsive Tailwind layout, SweetAlert2 modals replacing browser dialogs, sport-type filter, inline availability count badge |
| Front-end interactivity | Alpine.js reactive state, Motion One entrance animations, hero split-screen with SVG court overlays |

---

## Academic Context

- **Programme:** FdSc / Cert HE Computing
- **Module:** Professional Development (S2-PFD200)
- **Assessment:** Portfolio — Task 2 (Project Development) & Task 3 (Digital Portfolio)
- **Submission deadline:** 12 June 2026
- **Student:** Polycarp Mark

---

## References

Clark, D. (2021) *The long game.* New York: PublicAffairs.

Cottrell, S. (2013) *The study skills handbook.* 4th edn. London: Palgrave Macmillan.

Grant, A. (2021) *Think again.* New York: Viking.

Morgan, J. (2024) *The future of work.* New York: Wiley.
