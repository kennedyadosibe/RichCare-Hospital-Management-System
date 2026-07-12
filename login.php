<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config/db.php';

$error = null;

if (staff_is_logged_in()) {
    header('Location: staff.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $staffId = trim($_POST['staff_id'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $statement = $pdo->prepare('SELECT * FROM staff_users WHERE staff_code = :staff_code LIMIT 1');
    $statement->execute([':staff_code' => $staffId]);
    $staff = $statement->fetch();

    if ($staff && (int) $staff['is_active'] === 1 && password_verify($password, $staff['password_hash'])) {
        staff_login($staff);
        header('Location: staff.php');
        exit;
    }

    $error = 'Invalid staff ID or password.';
}

include __DIR__ . '/includes/header.php';
?>

<section class="login-page">
    <div class="container">
        <div class="row login-row">
            <div class="col-md-7">
                <p class="eyebrow">Secure staff access</p>
                <h1>RichCare Staff Portal</h1>
                <p>
                    Sign in to manage appointments, patients, records, billing, and department operations.
                </p>
            </div>
            <div class="col-md-5">
                <form class="login-card" method="post" action="login.php">
                    <h2>Sign in</h2>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label>Staff ID</label>
                        <input class="form-control" type="text" name="staff_id" value="RC-STAFF-001">
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <div class="password-toggle-field">
                            <input class="form-control" id="staff-login-password" type="password" name="password" value="richcare">
                            <button class="btn btn-default password-eye-button" type="button" data-toggle-password="#staff-login-password" aria-label="Show password">
                                <span class="glyphicon glyphicon-eye-open"></span>
                            </button>
                        </div>
                    </div>
                    <button class="btn btn-primary btn-lg btn-block" type="submit">Enter dashboard</button>
                    <div class="login-helper-link">
                        <a href="reset_password.php">Forgot password?</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
