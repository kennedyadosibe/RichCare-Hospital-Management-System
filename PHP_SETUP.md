# RichCare PHP/MySQL Setup

This version uses:

- HTML
- CSS
- JavaScript
- PHP
- MySQL
- Bootstrap 3.3.7

## 1. Install a PHP/MySQL stack

Use XAMPP, WAMP, Laragon, or another local PHP/MySQL environment.

## 2. Move or open the project

Place this folder inside your local server web root, for example:

```text
C:\xampp\htdocs\RichcarexHospital
```

## 3. Create the database

Open phpMyAdmin and import:

```text
database/schema.sql
```

This creates the `richcare_hospital` database and starter tables.

## 4. Check database credentials

Edit:

```text
config/db.php
```

Default XAMPP settings are already used:

```php
$username = 'root';
$password = '';
```

## 5. Open the site

Visit:

```text
http://localhost/RichcarexHospital/index.php
```

Public pages:

- `index.php`
- `book.php`

Staff dashboard:

- `login.php`

Default seeded dashboard login after importing `database/schema.sql`:

```text
Staff ID: RC-STAFF-001
Password: richcare
```
