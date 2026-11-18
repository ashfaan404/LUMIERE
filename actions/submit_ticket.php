<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    $_SESSION['error_message'] = "You must be logged in to submit a support ticket.";
    redirect('../pages/login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $subject = isset($_POST['subject']) ? sanitize($conn, $_POST['subject']) : '';
    $ticket_type = isset($_POST['ticket_type']) ? sanitize($conn, $_POST['ticket_type']) : '';
    $order_id = (isset($_POST['order_id']) && !empty($_POST['order_id'])) ? intval($_POST['order_id']) : null;
    $message = isset($_POST['message']) ? sanitize($conn, $_POST['message']) : '';
    
    // Server-side validation
    if (empty($subject) || empty($ticket_type) || empty($message)) {
        $_SESSION['error_message'] = "Please fill out all required fields.";
        redirect('../pages/support.php');
    }
    
    // Image Upload Logic
    $image_path = null;
    if ($ticket_type === 'return' && isset($_FILES['return_image']) && $_FILES['return_image']['error'] === 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        if (in_array($_FILES['return_image']['type'], $allowed_types)) {
            $upload_dir = '../admin/uploads/tickets/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $filename = time() . '_' . basename($_FILES['return_image']['name']);
            if (move_uploaded_file($_FILES['return_image']['tmp_name'], $upload_dir . $filename)) {
                $image_path = 'admin/uploads/tickets/' . $filename;
            }
        } else {
             $_SESSION['error_message'] = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
             redirect('../pages/support.php');
        }
    }

    // Insert Ticket
    $stmt = $conn->prepare("INSERT INTO support_tickets (user_id, subject, ticket_type, order_id, message, image_path) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ississ", $user_id, $subject, $ticket_type, $order_id, $message, $image_path);
    
    if ($stmt->execute()) {
        $ticket_id = $conn->insert_id;
        $_SESSION['success_message'] = "Support ticket submitted successfully. We will get back to you soon.";
        
        // Add Admin Notification
        $notif_msg = "New support ticket: " . $subject;
        $notif_link = "tickets.php?id=" . $ticket_id;
        $notif_stmt = $conn->prepare("INSERT INTO admin_notifications (type, message, link) VALUES ('new_ticket', ?, ?)");
        $notif_stmt->bind_param("ss", $notif_msg, $notif_link);
        $notif_stmt->execute();
    } else {
        $_SESSION['error_message'] = "Failed to submit ticket. Please try again later.";
    }
    
    redirect('../pages/support.php');
}
?>
