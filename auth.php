<?php
session_start();

function staff_is_logged_in() {
    return isset($_SESSION['staff_user']);
}

function require_staff_login() {
    if (!staff_is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function staff_login($staff) {
    $_SESSION['staff_user'] = [
        'id' => $staff['id'],
        'staff_code' => $staff['staff_code'],
        'name' => $staff['full_name'],
        'role' => $staff['role'],
        'department' => $staff['department'],
    ];
}

function staff_has_role($role) {
    return staff_is_logged_in() && ($_SESSION['staff_user']['role'] ?? '') === $role;
}

function staff_has_any_role($roles) {
    if (!staff_is_logged_in()) {
        return false;
    }

    return in_array($_SESSION['staff_user']['role'] ?? '', $roles, true);
}

function staff_logout() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}
?>
