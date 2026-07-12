# RichCare Hospital Management System

RichCare is a web-based hospital management system built for managing public bookings, staff workflows, patient records, appointments, billing, reports, and administrative operations from one platform.

The project uses HTML, CSS, JavaScript, PHP, MySQL, and Bootstrap 3.3.7. It is designed to run locally through XAMPP.

## Main Features

- Public hospital website with Home, About, Services, Contact, and Booking pages
- Patient appointment booking
- Staff login and role-based dashboard access
- Patient registration and previous patient records
- Appointment status management
- Medical records
- Lab results
- Prescriptions and pharmacy workflow
- Billing linked to checked-in or completed visits
- Printable invoices
- Printable patient medical reports
- Staff account creation, disable/reactivate, delete, and password reset
- Activity/audit logs
- Reports dashboard
- Staff notification bell with role-based alerts
- Mobile responsive dashboard layout

## Technology Stack

- HTML
- CSS
- JavaScript
- PHP
- MySQL
- Bootstrap 3.3.7
- XAMPP for local Apache/MySQL hosting

## Project Structure

```text
assets/              CSS, JavaScript, and image assets
config/              Database configuration
database/            MySQL schema
docs/features/       Feature documentation
includes/            Shared header, footer, and helper functions
progress/            Progress tracking notes
staff.php            Staff dashboard and operations
index.php            Public homepage
book.php             Public appointment booking
patient.php          Patient medical record report
invoice.php          Printable invoice
reset_password.php   Staff password reset flow
```

## Local Setup

1. Install XAMPP.
2. Copy or sync the project to:

```text
C:\xampp\htdocs\RichcarexHospital
```

3. Start Apache and MySQL in XAMPP.
4. Import the database schema:

```text
database/schema.sql
```

5. Open the public site:

```text
http://localhost/RichcarexHospital/index.php
```

6. Open the staff dashboard:

```text
http://localhost/RichcarexHospital/login.php
```

## Demo Staff Accounts

Default password for seeded staff accounts:

```text
richcare
```

Available accounts:

```text
RC-STAFF-001  Administrator
RC-DOC-002    Doctor
RC-NUR-003    Nurse
RC-BIL-004    Billing
RC-REC-005    Receptionist
RC-LAB-006    Laboratory
RC-PHA-007    Pharmacy
```

## Git Workflow

- `dev` is used for active development and testing.
- `main` is used for stable official code.
- New features should be committed to `dev`.
- Merge or push to `main` only after tested work is approved.
- Every new feature should have documentation in `docs/features/`.
- Progress notes should be kept in `progress/`.

## Testing Checklist

- Run PHP syntax checks after PHP edits:

```text
C:\xampp\php\php.exe -l staff.php
```

- Test public pages in the browser.
- Test staff login and role-based access.
- Test booking to billing flow.
- Test mobile dashboard pages.
- Confirm print pages for invoices and patient reports.

## Current Status

RichCare currently supports the core hospital workflow from public booking through staff management, patient records, billing, reports, and notifications.
