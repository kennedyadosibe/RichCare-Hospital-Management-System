# Login Password Eye Toggle

## Purpose

Allow staff users to show or hide the password while typing on the staff login page, so they can confirm what they entered before submitting.

## Files Changed

- `login.php`
- `includes/footer.php`
- `assets/js/site.js`

## Database Changes

None.

## How To Test

- Open `http://localhost/RichcarexHospital/login.php`.
- Type into the password field.
- Click the eye button.
- Confirm the password field changes from hidden dots to visible text.
- Click the eye button again.
- Confirm the password is hidden again.
- Log in with a seeded staff account.
- If the button appears unchanged after deployment, hard refresh the browser so `assets/js/site.js?v=3` is loaded.

## Status

Completed.
