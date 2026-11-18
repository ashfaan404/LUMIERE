<?php
// admin/actions/mark_all_read.php
require_once '../includes/admin_auth.php';
require_once '../../includes/db.php';

$conn->query("UPDATE admin_notifications SET is_read = 1");

header("Location: " . $_SERVER['HTTP_REFERER']);
exit();
?>
