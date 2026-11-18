<?php
// admin/actions/delete_review.php
require_once '../includes/admin_auth.php';
require_once '../../includes/db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id) {
    $stmt = $conn->prepare("DELETE FROM reviews WHERE review_id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Review deleted successfully.";
    } else {
        $_SESSION['error_message'] = "Error deleting review.";
    }
}

header("Location: ../pages/reviews.php");
exit();
?>
