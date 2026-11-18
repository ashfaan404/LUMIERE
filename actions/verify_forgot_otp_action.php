<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['reset_contact'])) {
        redirect('../pages/forgot_password.php');
    }
    
    $contact = $_SESSION['reset_contact'];
    $otp = isset($_POST['otp']) ? sanitize($conn, $_POST['otp']) : '';
    
    if (empty($otp)) {
        $_SESSION['error_message'] = "Please provide the OTP.";
        redirect('../pages/verify_forgot_otp.php');
    }
    
    $stmt = $conn->prepare("SELECT v.otp_id, v.user_id FROM otp_verifications v WHERE (v.phone = ? OR v.email = ?) AND v.otp_code = ? AND v.otp_type = 'forgot_password' AND v.is_used = 0 AND v.expires_at > NOW() ORDER BY v.created_at DESC LIMIT 1");
    $stmt->bind_param("sss", $contact, $contact, $otp);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $data = $result->fetch_assoc();
        
        $stmt_update = $conn->prepare("UPDATE otp_verifications SET is_used = 1 WHERE otp_id = ?");
        $stmt_update->bind_param("i", $data['otp_id']);
        $stmt_update->execute();
        
        $_SESSION['reset_user_id'] = $data['user_id'];
        unset($_SESSION['reset_contact']);
        
        $_SESSION['success_message'] = "OTP verified. Please enter your new password.";
        redirect('../pages/reset_password.php');
        
    } else {
        $_SESSION['error_message'] = "Invalid or expired OTP.";
        redirect('../pages/verify_forgot_otp.php');
    }
}
?>
