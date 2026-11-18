<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? sanitize($conn, $_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    if (empty($email) || empty($password)) {
        $_SESSION['error_message'] = "Please fill in all fields.";
        redirect('../pages/login.php');
    }
    
    // Check brute-force lockout
    $locked = checkLoginLocked($email);
    if ($locked) {
        $_SESSION['error_message'] = $locked;
        redirect('../pages/login.php');
    }
    
    $stmt = $conn->prepare("SELECT user_id, password, full_name, is_phone_verified FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            // Successful login - clear attempts
            clearLoginAttempts($email);
            
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['success_message'] = "Welcome back, " . htmlspecialchars($user['full_name']) . "!";
            
            // Send login notification email
            $subject = "Recent Login to LUMIÈRE Account";
            $message = "Hello " . htmlspecialchars($user['full_name']) . ",\n\nWe noticed a new login to your LUMIÈRE account on " . date('Y-m-d H:i:s') . ". If this was you, no further action is required.\n\nThank you,\nLUMIÈRE";
            sendGeneralEmail($email, $subject, $message);
            
            redirect('../pages/home.php');
            
        } else {
            // Record failed attempt
            recordFailedLogin($email);
            $attempts_key = 'login_attempts_' . md5($email);
            $count = $_SESSION[$attempts_key]['count'] ?? 1;
            $remaining = 5 - $count;
            if ($remaining > 0) {
                $_SESSION['error_message'] = "Invalid credentials. $remaining attempt(s) remaining before account is locked.";
            } else {
                $_SESSION['error_message'] = "Invalid credentials. Account locked for 15 minutes.";
            }
            redirect('../pages/login.php');
        }
    } else {
        recordFailedLogin($email);
        $_SESSION['error_message'] = "Invalid credentials.";
        redirect('../pages/login.php');
    }
}
?>
