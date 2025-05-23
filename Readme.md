# Certificate Validator

A web-based application for validating certificates, featuring an admin dashboard and a user portal. This project is designed for easy deployment on shared hosting (e.g., Hostinger) and uses PHP and MySQL.

---

## Table of Contents

- [Features](#features)
- [Technologies Used](#technologies-used)
- [Deployment Steps](#deployment-steps)
- [Project Structure](#project-structure)
- [Usage](#usage)
- [Contributing](#contributing)
- [License](#license)

---

## Features

- Admin dashboard for managing certificates
- User portal for certificate verification
- Secure authentication and data handling
- Easy deployment on shared hosting

---

## Technologies Used

- **Backend:** PHP (vanilla)
- **Database:** MySQL
- **Frontend:** HTML, CSS, JavaScript
- **Hosting:** Hostinger (or any PHP/MySQL compatible host)
- **Tools:** phpMyAdmin

---

## Deployment Steps

1. **Database Setup**

   - Log into your Hostinger account and access phpMyAdmin.
   - Create a new database (or use an existing one).
   - Run the SQL code in `initate.sql` to set up the required tables.
   - Note your database name, username, and password.

2. **File Upload**
   - Upload all files in both `admin_dashboard` and `user_portal` directories to your hosting account.
   - Update secrets and configuration in the following files:
     - `admin-auth.php`
     - `config.php`
     - `verify.php`
   - Ensure file permissions are set appropriately.

---
