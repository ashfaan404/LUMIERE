<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    $_SESSION['error_message'] = "You must be logged in to cancel a ticket.";
    redirect('../pages/login.php');
}

if (isset($_GET['id'])) {
    $ticket_id = intval($_GET['id']);
    $user_id = $_SESSION['user_id'];
    
    // Check if ticket belongs to user and is not already closed/cancelled
    $stmt = $conn->prepare("SELECT status FROM support_tickets WHERE ticket_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $ticket_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $ticket = $result->fetch_assoc();
        if ($ticket['status'] === 'In Progress') {
            $_SESSION['error_message'] = "This ticket is currently in process and cannot be cancelled.";
        } elseif ($ticket['status'] !== 'Closed' && $ticket['status'] !== 'Cancelled') {
            // Cancel it
            $update_stmt = $conn->prepare("UPDATE support_tickets SET status = 'Cancelled' WHERE ticket_id = ?");
            $update_stmt->bind_param("i", $ticket_id);
            if ($update_stmt->execute()) {
                $_SESSION['success_message'] = "Support ticket updated to Cancelled.";
                
                // Optional: log to admin notifications
                $notif_msg = "Support ticket #" . $ticket_id . " was cancelled by the user.";
                $notif_stmt = $conn->prepare("INSERT INTO admin_notifications (type, message, link) VALUES ('ticket_update', ?, ?)");
                $link = "tickets.php";
                $notif_stmt->bind_param("ss", $notif_msg, $link);
                $notif_stmt->execute();
            } else {
                $_SESSION['error_message'] = "Failed to cancel ticket.";
            }
        } else {
            $_SESSION['error_message'] = "This ticket is already closed or cancelled.";
        }
    } else {
        $_SESSION['error_message'] = "Invalid ticket.";
    }
}

redirect('../pages/support.php');
?>
