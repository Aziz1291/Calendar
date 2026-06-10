# 📅 Calendar App

A PHP calendar application with event management, user roles, and an admin approval workflow.

## Features

- 🗓 Monthly calendar view with multi-day event support
- 👤 User registration & login (bcrypt passwords)
- 🔐 Role-based access: `user` / `admin`
- ✅ Event approval workflow: Pending → Approved / Rejected
- 🌐 Admin-wide events visible to all users
- 📋 Admin dashboard with event & user management
- 🔎 Filter events by status and search by username

## Tech Stack

- **Backend:** PHP (vanilla, no framework)
- **Database:** MySQL 8 on [Aiven](https://aiven.io) (SSL required)
- **Frontend:** HTML / CSS / vanilla JS

## Setup

### 1. Clone the repository
```bash
git clone https://github.com/Aziz1291/Calendar.git
cd Calendar
```

### 2. Configure the database
```bash
cp config.example.php config.php
```
Edit `config.php` and fill in your database credentials.  
Download the **CA certificate** from your Aiven console and save it as `ca.pem` in the project root.

### 3. Import the schema
Run the SQL below in your MySQL client (or import via phpMyAdmin):

```sql
CREATE TABLE users (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username   VARCHAR(50)  NOT NULL,
  email      VARCHAR(150) NOT NULL,
  password   VARCHAR(255) NOT NULL,
  verified   TINYINT(1)   NOT NULL DEFAULT 0,
  role       ENUM('user','admin') NOT NULL DEFAULT 'user',
  created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_username (username),
  UNIQUE KEY uq_users_email    (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE events (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name            VARCHAR(255) NOT NULL,
  description     TEXT         NULL,
  start           DATETIME     NOT NULL,
  end             DATETIME     NOT NULL,
  user_id         INT UNSIGNED NOT NULL,
  admin_event     TINYINT(1)   NOT NULL DEFAULT 0,
  status          ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  rejectionReason TEXT         NULL,
  created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_events_start      (start),
  KEY idx_events_user_id    (user_id),
  KEY idx_events_user_admin (user_id, admin_event),
  CONSTRAINT fk_events_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 4. Serve with XAMPP
Point your Apache virtual host (or XAMPP htdocs) to the `public/` directory.

## Project Structure

```
Calendar/
├── public/          # Entry points (index.php, login.php, dashboard.php…)
├── src/
│   ├── App/         # Validators
│   └── Calendar/    # Models: Event, Events, User + bootstrap
├── views/           # PHP view templates
├── config.example.php  # ← copy to config.php
└── .gitignore
```

## Default Credentials (seed data)

| Username | Role  | Password   |
|----------|-------|------------|
| admin1   | admin | `password` |
| john     | user  | `password` |
| sara     | user  | `password` |
| mike     | user  | `password` |
