<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['register_data'])) {
        $_SESSION['error_message'] = "Session expired or invalid. Please try registering again.";
        redirect('../pages/register.php');
    }
    
    $otp = isset($_POST['otp']) ? sanitize($conn, $_POST['otp']) : '';
    $data = $_SESSION['register_data'];
    $phone = $data['phone'];
    
    if (empty($otp)) {
        $_SESSION['error_message'] = "Please provide the OTP.";
        redirect('../pages/verify_register_otp.php');
    }
    
    // Check OTP
    $stmt = $conn->prepare("SELECT otp_id FROM otp_verifications WHERE (phone = ? OR email = ?) AND otp_code = ? AND otp_type = 'register_phone' AND is_used = 0 AND expires_at > NOW() ORDER BY created_at DESC LIMIT 1");
    $stmt->bind_param("sss", $phone, $data['email'], $otp);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $otp_data = $result->fetch_assoc();
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Mark OTP as used
            $stmt_update = $conn->prepare("UPDATE otp_verifications SET is_used = 1 WHERE otp_id = ?");
            $stmt_update->bind_param("i", $otp_data['otp_id']);
            $stmt_update->execute();
            
            // Insert User
            $stmt_user = $conn->prepare("INSERT INTO users (full_name, email, phone, password, address, is_phone_verified) VALUES (?, ?, ?, ?, ?, 1)");
            $stmt_user->bind_param("sssss", $data['full_name'], $data['email'], $data['phone'], $data['password'], $data['address']);
            $stmt_user->execute();
            $user_id = $conn->insert_id;
            
            $conn->commit();
            
            // Try to also save address into addresses table (if it exists) — non-critical
            if (!empty($data['address']) && $user_id) {
                try {
                    $reg_address = $data['address'];
                    $reg_name = $data['full_name'];
                    $reg_phone = $data['phone'];
                    $stmt_addr = $conn->prepare("INSERT INTO addresses (user_id, full_name, phone, street_address, city, state, zip_code, country, is_default) VALUES (?, ?, ?, ?, 'Colombo', 'Western Province', '00100', 'Sri Lanka', 1)");
                    if ($stmt_addr) {
                        $stmt_addr->bind_param("isss", $user_id, $reg_name, $reg_phone, $reg_address);
                        $stmt_addr->execute();
                    }
                } catch (Exception $addr_e) {
                    // Non-critical — log and continue
                    error_log("Address insert failed for user $user_id: " . $addr_e->getMessage());
                }
            }
            
            // Log them in immediately
            $_SESSION['user_id'] = $user_id;
            $_SESSION['full_name'] = $data['full_name'];
            $_SESSION['success_message'] = "Registration successful! Welcome to LUMIÈRE.";
            
            unset($_SESSION['register_data']);
            redirect('../pages/home.php');
            
        } catch (Exception $e) {
            $conn->rollback();
            error_log("Registration failed: " . $e->getMessage());
            $_SESSION['error_message'] = "Registration failed. Please try again.";
            redirect('../pages/verify_register_otp.php');
        }
        
    } else {
        $_SESSION['error_message'] = "Invalid or expired OTP.";
        redirect('../pages/verify_register_otp.php');
    }
}
?>
