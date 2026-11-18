<?php
// admin/actions/update_ticket_status.php
require_once '../includes/admin_auth.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['ticket_id']);
    $status = sanitize($conn, $_POST['status']);

    if ($id && $status) {
        $stmt = $conn->prepare("UPDATE support_tickets SET status = ? WHERE ticket_id = ?");
        $stmt->bind_param("si", $status, $id);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Ticket #$id updated to $status.";
        } else {
            $_SESSION['error_message'] = "Error updating ticket.";
        }
    }
}
header("Location: ../pages/tickets.php");
exit();
?>
