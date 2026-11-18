<?php
// pages/dashboard.php
// Legacy dashboard - now redirected to home
require_once '../includes/db.php';
require_once '../includes/customer_check.php';

header("Location: home.php");
exit();
?>
