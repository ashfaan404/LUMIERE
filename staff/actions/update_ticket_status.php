<?php
// staff/actions/update_ticket_status.php
session_start();
require_once '../../includes/db.php';

if (!isset($_SESSION['admin_role']) || ($_SESSION['admin_role'] !== 'staff' && $_SESSION['admin_role'] !== 'super')) {
    $_SESSION['error_message'] = "Unauthorized access.";
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticket_id = intval($_POST['ticket_id']);
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE support_tickets SET status = ? WHERE ticket_id = ?");
    $stmt->bind_param("si", $status, $ticket_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Ticket status updated to $status.";
    } else {
        $_SESSION['error_message'] = "Failed to update ticket.";
    }
}

header("Location: ../pages/tickets.php");
exit();
?>
