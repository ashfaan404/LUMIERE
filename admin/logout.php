<?php
// admin/logout.php
session_start();
session_destroy();
session_start();
$_SESSION['success_message'] = "Logged out successfully from Admin Panel.";
header("Location: login.php");
exit();
?>
