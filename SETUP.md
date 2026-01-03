# SMARTGRADE Setup Guide

Welcome to **SMARTGRADE** — a complete, DepEd-compliant School Management System.

This guide will help you set up and run SMARTGRADE on your local machine (XAMPP recommended) or a live server.

## Requirements

- **Web Server**: Apache (XAMPP, WAMP, LAMP, or any PHP-compatible server)
- **PHP**: 8.0 or higher
- **Database**: MySQL or MariaDB
- **Composer** (for PHP dependencies)

## Step 1: Download & Extract

1. Download the latest SMARTGRADE release or clone the repository.
2. Extract the contents to your web server directory:
   - XAMPP: `C:\xampp\htdocs\smartgrade-reformed` 

## Step 2: Install Dependencies

Open terminal/command prompt in the project root and run:

```bash
composer install
```

This installs:
- Dompdf (PDF generation)
- TCPDF + FPDI (advanced PDF watermarking)
- Other required libraries

## Step 3: Set Up Database

1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Create a new database named `smartgrade`
3. Import the schema file:
   - Locate `database/smartgrade_schema.sql` in the project
   - Import it into the `smartgrade` database

## Step 4: Configure Database Connection

1. Copy `includes/config.example.php` to `includes/config.php`
2. Edit `includes/config.php` and update the database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Default XAMPP has empty password
define('DB_NAME', 'smartgrade_db'); // or any unsa imo pangan
```

## Step 5: Create Storage Directories

Create these folders and make them writable:

```
storage/
├── documents/
├── logos/
└── snapshots/
```

On Windows (XAMPP):
- Right-click each folder → Properties → Security → Give full permissions to "Everyone" or your user

## Step 6: Default Login Credentials

After importing the schema, use these accounts:

| Role      | Email                     | Password  |
|-----------|---------------------------|-----------|
| Admin     | admin@deped.gov.ph        | admin123  |
| Adviser   | teacher1@deped.gov.ph     | teacher123|
| Student   | student1@example.com      | student123|

## Step 7: Launch SMARTGRADE

Open your browser and go to:

```
http://localhost/smartgrade-reformed/
```

You should see the login page.

## Features Overview

- **Admin**: Full control (users, sections, subjects, school settings, logo upload)
- **Adviser**: Create sections, assign subjects, enroll students, generate SF9
- **Student**: View grades and watermarked SF9

## Document Generation

- SF9 Report Card (DepEd Form 9)
- Immutable with versioning
- Revoke & regenerate for corrections
- Student view has permanent watermark

## Commercial Use / Selling

SMARTGRADE is designed to be **multi-school ready**:

- Each school has its own `school_id`
- Upload custom logo via Admin → School Settings
- All documents automatically branded
- Perfect for SaaS or per-school licensing

## Troubleshooting

- **Blank page?** Check PHP error logs (`xampp/php/logs/php_error_log`)
- **PDF not generating?** Ensure `storage/` folders are writable
- **Watermark not showing?** Use the FPDI + TCPDF version in `student/view_document.php`
- **Permission errors?** Run XAMPP as administrator or fix folder permissions

## Support

For issues or customization:
- This system is fully open and documented
- All code is clean and commented
- Ready for deployment

**Congratulations!** Your SMARTGRADE system is now set up and ready to use.

You’ve built a powerful, professional school management tool.

Enjoy! 🎉🚀

*SMARTGRADE — Making Education Smarter, One Grade at a Time.*