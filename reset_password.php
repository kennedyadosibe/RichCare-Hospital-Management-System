<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/helpers.php';

$error = null;
$success = null;
$token = $_GET['token'] ?? '';
$showPasswordForm = false;
$resetRecord = null;

function build_reset_link($token) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    return $scheme . '://' . $host . $path . '/reset_password.php?token=' . urlencode($token);
}

function log_reset_link($email, $link) {
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $email . ' - ' . $link . PHP_EOL;
    file_put_contents(__DIR__ . '/password_reset_mail.log', $line, FILE_APPEND);
}

if (staff_is_logged_in()) {
    header('Location: staff.php');
    exit;
}

if ($token !== '') {
    $tokenHash = hash('sha256', $token);
    $statement = $pdo->prepare(
        'SELECT pr.*, su.email, su.staff_code
         FROM password_resets pr
         INNER JOIN staff_users su ON su.id = pr.staff_id
         WHERE pr.token_hash = :token_hash
           AND pr.used_at IS NULL
           AND pr.expires_at >= NOW()
           AND su.is_active = 1
         LIMIT 1'
    );
    $statement->execute([':token_hash' => $tokenHash]);
    $resetRecord = $statement->fetch();
    $showPasswordForm = (bool) $resetRecord;

    if (!$showPasswordForm) {
        $error = 'This password reset link is invalid or has expired.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_type'] ?? '') === 'request_reset') {
    $staffId = trim($_POST['staff_id'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($staffId === '' || $email === '') {
        $error = 'Please provide your Staff ID and registered email.';
    } else {
        $statement = $pdo->prepare(
            'SELECT * FROM staff_users
             WHERE staff_code = :staff_code AND email = :email AND is_active = 1
             LIMIT 1'
        );
        $statement->execute([
            ':staff_code' => $staffId,
            ':email' => $email,
        ]);
        $staff = $statement->fetch();

        if ($staff) {
            $plainToken = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $plainToken);
            $expiresAt = date('Y-m-d H:i:s', time() + 3600);

            $pdo->prepare('UPDATE password_resets SET used_at = NOW() WHERE staff_id = :staff_id AND used_at IS NULL')
                ->execute([':staff_id' => $staff['id']]);

            $insert = $pdo->prepare(
                'INSERT INTO password_resets (staff_id, token_hash, expires_at)
                 VALUES (:staff_id, :token_hash, :expires_at)'
            );
            $insert->execute([
                ':staff_id' => $staff['id'],
                ':token_hash' => $tokenHash,
                ':expires_at' => $expiresAt,
            ]);

            $link = build_reset_link($plainToken);
            $subject = 'RichCare password reset';
            $message = "Use this secure link to reset your RichCare staff password:\n\n" . $link . "\n\nThis link expires in 1 hour.";
            @mail($email, $subject, $message);
            log_reset_link($email, $link);
            log_activity(
                $pdo,
                'Requested password reset',
                'Security',
                $staff['staff_code'] . ' - ' . $staff['full_name'] . ' requested a password reset link.',
                'staff_user',
                (int) $staff['id'],
                [
                    'id' => $staff['id'],
                    'staff_code' => $staff['staff_code'],
                    'name' => $staff['full_name'],
                    'role' => $staff['role'],
                ]
            );
        }

        $success = 'If the Staff ID and email match an active account, a password reset link has been sent to the registered email.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_type'] ?? '') === 'set_password') {
    $postedToken = $_POST['token'] ?? '';
    $newPassword = trim($_POST['new_password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');
    $tokenHash = hash('sha256', $postedToken);

    $statement = $pdo->prepare(
        'SELECT pr.*, su.email, su.staff_code
         FROM password_resets pr
         INNER JOIN staff_users su ON su.id = pr.staff_id
         WHERE pr.token_hash = :token_hash
           AND pr.used_at IS NULL
           AND pr.expires_at >= NOW()
           AND su.is_active = 1
         LIMIT 1'
    );
    $statement->execute([':token_hash' => $tokenHash]);
    $resetRecord = $statement->fetch();

    if (!$resetRecord) {
        $error = 'This password reset link is invalid or has expired.';
    } elseif ($newPassword === '' || $confirmPassword === '') {
        $error = 'Please enter and confirm your new password.';
        $showPasswordForm = true;
        $token = $postedToken;
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'The new passwords do not match.';
        $showPasswordForm = true;
        $token = $postedToken;
    } elseif (strlen($newPassword) < 6) {
        $error = 'Password must be at least 6 characters.';
        $showPasswordForm = true;
        $token = $postedToken;
    } else {
        $pdo->prepare('UPDATE staff_users SET password_hash = :password_hash WHERE id = :id')
            ->execute([
                ':password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
                ':id' => $resetRecord['staff_id'],
            ]);
        $pdo->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = :id')
            ->execute([':id' => $resetRecord['id']]);
        log_activity(
            $pdo,
            'Completed password reset',
            'Security',
            'Password was reset for ' . $resetRecord['email'] . '.',
            'staff_user',
            (int) $resetRecord['staff_id'],
            [
                'id' => $resetRecord['staff_id'],
                'staff_code' => $resetRecord['staff_code'] ?? null,
                'name' => $resetRecord['email'],
                'role' => null,
            ]
        );
        $success = 'Password reset successfully. You can now sign in with your new password.';
        $showPasswordForm = false;
        $token = '';
    }
}

include __DIR__ . '/includes/header.php';
?>

<section class="login-page">
    <div class="container">
        <div class="row login-row">
            <div class="col-md-7">
                <p class="eyebrow">Staff password recovery</p>
                <h1>Reset Staff Password</h1>
            </div>
            <div class="col-md-5">
                <form class="login-card" method="post" action="reset_password.php<?php echo $showPasswordForm ? '?token=' . urlencode($token) : ''; ?>">
                    <?php if ($showPasswordForm): ?>
                        <h2>Set new password</h2>
                        <input type="hidden" name="form_type" value="set_password">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php else: ?>
                        <h2>Request reset link</h2>
                        <input type="hidden" name="form_type" value="request_reset">
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php endif; ?>

                    <?php if ($showPasswordForm): ?>
                        <div class="form-group">
                            <label>New password</label>
                            <input class="form-control" type="password" name="new_password" placeholder="At least 6 characters" required>
                        </div>
                        <div class="form-group">
                            <label>Confirm new password</label>
                            <input class="form-control" type="password" name="confirm_password" placeholder="Repeat new password" required>
                        </div>
                        <button class="btn btn-primary btn-lg btn-block" type="submit">Save new password</button>
                    <?php else: ?>
                        <div class="form-group">
                            <label>Staff ID</label>
                            <input class="form-control" type="text" name="staff_id" placeholder="Enter your staff ID" required>
                        </div>
                        <div class="form-group">
                            <label>Registered email</label>
                            <input class="form-control" type="email" name="email" placeholder="Enter your staff email" required>
                        </div>
                        <button class="btn btn-primary btn-lg btn-block" type="submit">Send reset link</button>
                    <?php endif; ?>

                    <div class="login-helper-link">
                        <a href="login.php">Back to staff login</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
