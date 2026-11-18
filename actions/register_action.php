<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['resend'])) {
    
    // Check if it's a resend request
    if (isset($_GET['resend']) && isset($_SESSION['register_data'])) {
        $full_name = $_SESSION['register_data']['full_name'];
        $email = $_SESSION['register_data']['email'];
        $phone = $_SESSION['register_data']['phone'];
        $address = $_SESSION['register_data']['address'];
        $password = $_SESSION['register_data']['password'];
    } else {
        $full_name = isset($_POST['full_name']) ? sanitize($conn, $_POST['full_name']) : '';
        $email = isset($_POST['email']) ? sanitize($conn, $_POST['email']) : '';
        $phone = isset($_POST['phone']) ? sanitize($conn, $_POST['phone']) : '';
        $address = isset($_POST['address']) ? sanitize($conn, $_POST['address']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
        
        // Validation
        if (empty($full_name) || empty($email) || empty($phone) || empty($password)) {
            $_SESSION['error_message'] = "Please fill in all required fields.";
            redirect('../pages/register.php');
        }
        
        if ($password !== $confirm_password) {
            $_SESSION['error_message'] = "Passwords do not match.";
            redirect('../pages/register.php');
        }
        
        if (strlen($password) < 6) {
            $_SESSION['error_message'] = "Password must be at least 6 characters.";
            redirect('../pages/register.php');
        }
        
        if (!preg_match('/^[A-Z]/', $password)) {
            $_SESSION['error_message'] = "Password must start with a capital letter.";
            redirect('../pages/register.php');
        }

        if (!preg_match('/[0-9]/', $password)) {
            $_SESSION['error_message'] = "Password must contain at least one number.";
            redirect('../pages/register.php');
        }

        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            $_SESSION['error_message'] = "Password must contain at least one special character (e.g. @).";
            redirect('../pages/register.php');
        }
        
        // Check duplicate email
        $stmt_email = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt_email->bind_param("s", $email);
        $stmt_email->execute();
        if ($stmt_email->get_result()->num_rows > 0) {
            $_SESSION['error_message'] = "Email is already registered.";
            redirect('../pages/register.php');
        }
        
        // Check duplicate phone
        $stmt_phone = $conn->prepare("SELECT user_id FROM users WHERE phone = ?");
        $stmt_phone->bind_param("s", $phone);
        $stmt_phone->execute();
        if ($stmt_phone->get_result()->num_rows > 0) {
            $_SESSION['error_message'] = "Phone number is already registered.";
            redirect('../pages/register.php');
        }
        
        // Save in session to preserve while verifying OTP
        $_SESSION['register_data'] = [
            'full_name' => $full_name,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'password' => password_hash($password, PASSWORD_DEFAULT)
        ];
    }
    
    // Generate OTP
    $otp = generateOTP();
    
    // Invalidate old register OTPs for this phone
    $stmt_inv = $conn->prepare("UPDATE otp_verifications SET is_used = 1 WHERE phone = ? AND otp_type = 'register_phone'");
    $stmt_inv->bind_param("s", $phone);
    $stmt_inv->execute();
    
    // Store new OTP
    $stmt_ins = $conn->prepare("INSERT INTO otp_verifications (phone, email, otp_code, otp_type, expires_at) VALUES (?, ?, ?, 'register_phone', DATE_ADD(NOW(), INTERVAL 2 MINUTE))");
    $stmt_ins->bind_param("sss", $phone, $email, $otp);
    
    if ($stmt_ins->execute()) {
        if (sendEmailOTP($email, $otp, 'register_phone')) {
            $_SESSION['success_message'] = "OTP sent to your email address.";
            redirect('../pages/verify_register_otp.php');
        } else {
            error_log("OTP email failed for: $email - Check SMTP settings in includes/config_email.php");
            $_SESSION['error_message'] = "We could not send a verification email to <strong>" . htmlspecialchars($email) . "</strong>. Please check that your email address is correct and try again.";
            redirect('../pages/register.php');
        }
    } else {
        $_SESSION['error_message'] = "Failed to generate OTP. Please try again.";
        redirect('../pages/register.php');
    }
}
?>
