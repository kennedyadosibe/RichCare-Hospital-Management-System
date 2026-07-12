# Project Health Scan

## Purpose

Run a full project scan to catch broken syntax, outdated setup instructions, missing local links/assets, build problems, database mismatches, and live route issues.

## Files Changed

- `PHP_SETUP.md`
- `docs/features/2026-07-12-project-health-scan.md`
- `progress/2026-07-12-health-scan.md`

## Database Changes

None.

## Fixes Made

- Updated `PHP_SETUP.md` to use the actual XAMPP folder `C:\xampp\htdocs\RichcarexHospital`.
- Updated the setup URL to `http://localhost/RichcarexHospital/index.php`.
- Added seeded dashboard login details to the setup guide.

## Tests Run

- PHP syntax check across all PHP files.
- JavaScript syntax check for `server/index.js` and `assets/js/site.js`.
- `npm run build`.
- MySQL table and column checks.
- Concrete local `href`, `src`, and `action` target scan.
- Live public route checks.
- Protected route redirect checks.
- Seeded staff login check.
- Authenticated dashboard, patient record, and invoice route checks.

## Status

Completed.
