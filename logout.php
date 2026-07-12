<?php
require_once __DIR__ . '/auth.php';
staff_logout();
header('Location: index.php');
exit;
?>
