<?php
function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function make_code($prefix) {
    return $prefix . '-' . date('ymd') . random_int(100, 999);
}

function log_activity($pdo, $action, $category, $description, $targetType = null, $targetId = null, $actor = null) {
    if ($actor === null && isset($_SESSION['staff_user'])) {
        $actor = $_SESSION['staff_user'];
    }

    $statement = $pdo->prepare(
        'INSERT INTO activity_logs
         (staff_id, staff_code, staff_name, staff_role, action, category, description, target_type, target_id, ip_address)
         VALUES (:staff_id, :staff_code, :staff_name, :staff_role, :action, :category, :description, :target_type, :target_id, :ip_address)'
    );

    $statement->execute([
        ':staff_id' => isset($actor['id']) ? (int) $actor['id'] : null,
        ':staff_code' => $actor['staff_code'] ?? null,
        ':staff_name' => $actor['name'] ?? ($actor['full_name'] ?? null),
        ':staff_role' => $actor['role'] ?? null,
        ':action' => $action,
        ':category' => $category,
        ':description' => $description,
        ':target_type' => $targetType,
        ':target_id' => $targetId,
        ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
    ]);
}
?>
