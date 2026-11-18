<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['resend'])) {
    $contact = isset($_POST['contact']) ? sanitize($conn, $_POST['contact']) : (isset($_GET['contact']) ? sanitize($conn, $_GET['contact']) : '');
    
    if (empty($contact)) {
        $_SESSION['error_message'] = "Please enter your email or phone number.";
        redirect('../pages/forgot_password.php');
    }
    
    $stmt = $conn->prepare("SELECT user_id, phone, email FROM users WHERE phone = ? OR email = ?");
    $stmt->bind_param("ss", $contact, $contact);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        $otp = generateOTP();
        
        // Invalidate old OTPs
        $stmt_inv = $conn->prepare("UPDATE otp_verifications SET is_used = 1 WHERE user_id = ? AND otp_type = 'forgot_password'");
        $stmt_inv->bind_param("i", $user['user_id']);
        $stmt_inv->execute();
        
        // Insert new OTP
        $stmt_ins = $conn->prepare("INSERT INTO otp_verifications (user_id, phone, email, otp_code, otp_type, expires_at) VALUES (?, ?, ?, ?, 'forgot_password', DATE_ADD(NOW(), INTERVAL 2 MINUTE))");
        $stmt_ins->bind_param("isss", $user['user_id'], $user['phone'], $user['email'], $otp);
        
        if ($stmt_ins->execute()) {
            if (sendEmailOTP($user['email'], $otp, 'forgot_password')) {
                $_SESSION['reset_contact'] = $contact;
                $_SESSION['success_message'] = "OTP sent to your email!";
                redirect('../pages/verify_forgot_otp.php');
            } else {
                $_SESSION['error_message'] = "Failed to send email. Check SMTP configuration.";
                redirect('../pages/forgot_password.php');
            }
        } else {
            $_SESSION['error_message'] = "Failed to generate OTP.";
            redirect('../pages/forgot_password.php');
        }
        
    } else {
        $_SESSION['error_message'] = "No account found with that contact info.";
        redirect('../pages/forgot_password.php');
    }
}
?>
