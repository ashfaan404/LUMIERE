<?php
require_once 'config_email.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require_once __DIR__ . '/../src/Exception.php';
require_once __DIR__ . '/../src/PHPMailer.php';
require_once __DIR__ . '/../src/SMTP.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Sanitize user input
function sanitize($conn, $input)
{
    return htmlspecialchars(strip_tags(trim($input)));
}

// Generate 6-digit OTP
function generateOTP()
{
    return rand(100000, 999999);
}

// Redirect and exit
function redirect($url)
{
    header("Location: $url");
    exit();
}

// Check if user is logged in
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

// Get cart total item count
function getCartCount()
{
    if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        $count = 0;
        foreach ($_SESSION['cart'] as $item) {
            if (is_array($item) && isset($item['quantity'])) {
                $count += $item['quantity'];
            }
        }
        return $count;
    }
    return 0;
}

// Get compare list count
function getCompareCount()
{
    if (isset($_SESSION['compare'])) {
        return count($_SESSION['compare']);
    }
    return 0;
}

// Check if a product is in the compare list
function isInCompare($product_id)
{
    if (isset($_SESSION['compare']) && is_array($_SESSION['compare'])) {
        return in_array($product_id, $_SESSION['compare']);
    }
    return false;
}

// Get wishlist list count
function getWishlistCount($conn)
{
    if (isLoggedIn()) {
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'];
    }
    return 0;
}

// Format price in Sri Lankan Rupees
function formatPrice($price)
{
    return "Rs. " . number_format($price, 2);
}

// Fetch average rating and total reviews count for a product
function getAverageRating($conn, $product_id)
{
    $stmt = $conn->prepare("SELECT AVG(rating) as avg_rating, COUNT(review_id) as total_reviews FROM reviews WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Check if user has purchased a product and order is Delivered
function hasPurchasedProduct($conn, $user_id, $product_id)
{
    $stmt = $conn->prepare("SELECT oi.order_item_id FROM order_items oi JOIN orders o ON oi.order_id = o.order_id WHERE o.user_id = ? AND oi.product_id = ? AND o.order_status = 'Delivered'");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

// Send OTP via Email using PHPMailer
function sendEmailOTP($email, $otp, $type)
{
    if (empty($email))
        return false;

    $subject = "Your LUMIÈRE OTP Code";
    $message = "Hello,\n\nYour OTP code is: " . $otp . "\n\n";

    switch ($type) {
        case 'register_phone':
            $message .= "Use this code to complete your registration.\n";
            break;
        case 'forgot_password':
            $message .= "Use this code to reset your password.\n";
            break;
    }

    $message .= "\nThis code is valid for 2 minutes.Do Not share with any one\n\nThank you,\nLUMIÈRE";

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;

        // Recipients
        $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
        $mail->addAddress($email);

        // Content
        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body = $message;

        return $mail->send();
    }
    catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

// Send a general email via PHPMailer
function sendGeneralEmail($email, $subject, $message)
{
    if (empty($email)) return false;

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;

        $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
        $mail->addAddress($email);

        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body = $message;

        return $mail->send();
    } catch (Exception $e) {
        error_log("General email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
// Send Order Status Update Email to Customer
function sendOrderStatusEmail($customer_email, $customer_name, $order_id, $new_status, $payment_method = '')
{
    if (empty($customer_email)) return false;

    $order_num = str_pad($order_id, 6, '0', STR_PAD_LEFT);

    $status_messages = [
        'Confirmed'  => ['icon'=>'✅', 'color'=>'#2e7d32', 'msg'=>'Great news! Your order has been <strong>confirmed</strong> and is being prepared.'],
        'Packed'     => ['icon'=>'📦', 'color'=>'#1565c0', 'msg'=>'Your order has been <strong>packed</strong> and is ready to be handed over to the courier.'],
        'Shipped'    => ['icon'=>'🚚', 'color'=>'#6a1b9a', 'msg'=>'Your order is on its way! It has been <strong>shipped</strong> and will arrive soon.'],
        'Delivered'  => ['icon'=>'🎉', 'color'=>'#c5a059', 'msg'=>'Your order has been <strong>delivered</strong>. Thank you for shopping with LUMIÈRE!'],
        'Cancelled'  => ['icon'=>'❌', 'color'=>'#c62828', 'msg'=>'Your order has been <strong>cancelled</strong>.' .
            ($payment_method === 'Card Payment' ? ' If you made a card payment, your refund will be processed within <strong>15 working days</strong>.' : '')],
        'Placed'     => ['icon'=>'🛍️', 'color'=>'#37474f', 'msg'=>'Your order has been received and is awaiting confirmation.'],
    ];

    $info   = $status_messages[$new_status] ?? ['icon'=>'🔔','color'=>'#37474f','msg'=>"Your order status has been updated to <strong>$new_status</strong>."];
    $icon   = $info['icon'];
    $color  = $info['color'];
    $body   = $info['msg'];

    $subject = "Order #$order_num — Status Update: $new_status | LUMIÈRE";

    $html = "
<!DOCTYPE html>
<html>
<head><meta charset='UTF-8'></head>
<body style='margin:0;padding:0;background:#f4f0eb;font-family:Georgia,serif;'>
  <table width='100%' cellpadding='0' cellspacing='0' style='background:#f4f0eb;padding:40px 20px;'>
    <tr><td align='center'>
      <table width='580' cellpadding='0' cellspacing='0' style='background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);'>

        <!-- Header -->
        <tr><td style='background:linear-gradient(135deg,#1a2340 0%,#2c3a67 100%);padding:36px 40px;text-align:center;'>
          <div style='font-size:28px;color:#C5A059;letter-spacing:6px;font-weight:bold;'>LUMIÈRE</div>
          <div style='font-size:12px;color:rgba(197,160,89,0.7);letter-spacing:3px;margin-top:6px;'>ORDER UPDATE</div>
        </td></tr>

        <!-- Status Banner -->
        <tr><td style='background:{$color}10;border-left:4px solid {$color};padding:24px 40px;text-align:center;'>
          <div style='font-size:42px;margin-bottom:8px;'>{$icon}</div>
          <div style='font-size:22px;font-weight:bold;color:{$color};'>Order {$new_status}</div>
          <div style='font-size:13px;color:#888;margin-top:4px;'>Order #<strong>{$order_num}</strong></div>
        </td></tr>

        <!-- Body -->
        <tr><td style='padding:36px 40px;'>
          <p style='font-size:15px;color:#555;margin:0 0 16px;'>Dear <strong style='color:#1a2340;'>{$customer_name}</strong>,</p>
          <p style='font-size:15px;color:#555;line-height:1.7;margin:0 0 24px;'>{$body}</p>

          <div style='background:#fdfbf7;border:1px solid #f0ebe2;border-radius:10px;padding:20px 24px;margin-bottom:24px;'>
            <table width='100%' cellpadding='6' cellspacing='0' style='font-size:13px;color:#555;'>
              <tr><td style='color:#888;width:40%;'>Order Number</td><td style='font-weight:700;color:#1a2340;'>#{$order_num}</td></tr>
              <tr><td style='color:#888;'>Current Status</td><td><span style='background:{$color};color:#fff;padding:3px 12px;border-radius:50px;font-size:12px;font-weight:700;'>{$new_status}</span></td></tr>
              <tr><td style='color:#888;'>Payment Method</td><td style='font-weight:600;'>" . (empty($payment_method) ? 'N/A' : htmlspecialchars($payment_method)) . "</td></tr>
            </table>
          </div>

          <p style='font-size:13px;color:#999;line-height:1.6;'>You can track your order by logging in to your account.<br>If you have any questions, please contact our support team.</p>
        </td></tr>

        <!-- Footer -->
        <tr><td style='background:#1a2340;padding:24px 40px;text-align:center;'>
          <div style='font-size:11px;color:rgba(255,255,255,0.45);letter-spacing:1px;'>© " . date('Y') . " LUMIÈRE. All rights reserved.</div>
          <div style='font-size:11px;color:rgba(197,160,89,0.6);margin-top:6px;'>This is an automated email. Please do not reply.</div>
        </td></tr>

      </table>
    </td></tr>
  </table>
</body>
</html>";

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
        $mail->addAddress($customer_email, $customer_name);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $html;
        $mail->AltBody = "Dear $customer_name, your order #$order_num status has been updated to: $new_status.";
        return $mail->send();
    } catch (Exception $e) {
        error_log("Order status email failed: {$mail->ErrorInfo}");
        return false;
    }
}

// Helper to display star ratings
function getRatingStars($rating, $count = null)
{
    ob_start();
    echo '<div class="rating-stars">';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rating)
            echo '★';
        else
            echo '<span class="empty">★</span>';
    }
    if ($count !== null) {
        echo ' <span class="rating-count">(' . $count . ')</span>';
    }
    echo '</div>';
    return ob_get_clean();
}

// Helper to render images correctly (local vs external)
function renderImage($path, $alt = '', $class = '', $style = '')
{
    if (empty($path))
        return '';
    $src = $path;
    // Check if it's external
    if (!filter_var($path, FILTER_VALIDATE_URL)) {
        // Build path relative to root
        // If the path already has a leading slash or http, leave it
        $root_relative = $path;

        // Derive the folder depth using the configured app base URL.
        $basePath = trim((string) parse_url(appConfig('APP_BASE_URL', 'http://localhost/LUMIERE'), PHP_URL_PATH), '/');
        $script = trim($_SERVER['SCRIPT_NAME'] ?? '', '/');
        if ($basePath !== '' && strpos($script, $basePath . '/') === 0) {
            $script = substr($script, strlen($basePath) + 1);
        }
        $depth = substr_count($script, '/');
        $src = str_repeat('../', $depth) . ltrim($path, '/');
    }
    $class_attr = $class ? ' class="' . htmlspecialchars($class) . '"' : '';
    $style_attr = $style ? ' style="' . htmlspecialchars($style) . '"' : '';
    return '<img src="' . htmlspecialchars($src) . '" alt="' . htmlspecialchars($alt) . '"' . $class_attr . $style_attr . '>';
}

// ---- SECURITY HELPERS ----

// Generate CSRF token and store in session
function generateCSRFToken()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Validate CSRF token from form
function validateCSRF($token)
{
    if (empty($_SESSION['csrf_token']) || empty($token))
        return false;
    return hash_equals($_SESSION['csrf_token'], $token);
}

// Get CSRF hidden input field
function csrfField()
{
    return '<input type="hidden" name="csrf_token" value="' . generateCSRFToken() . '">';
}

// Check if login is blocked due to too many failed attempts
function checkLoginLocked($email)
{
    $key = 'login_attempts_' . md5($email);
    if (!empty($_SESSION[$key])) {
        $data = $_SESSION[$key];
        if ($data['count'] >= 5) {
            $diff = time() - $data['last_attempt'];
            if ($diff < 900) { // 15 minute lockout
                $remaining = ceil((900 - $diff) / 60);
                return "Too many failed login attempts. Try again in $remaining minute(s).";
            }
            else {
                // Reset after lockout period
                unset($_SESSION[$key]);
            }
        }
    }
    return false;
}

// Record a failed login attempt
function recordFailedLogin($email)
{
    $key = 'login_attempts_' . md5($email);
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['count' => 0, 'last_attempt' => time()];
    }
    $_SESSION[$key]['count']++;
    $_SESSION[$key]['last_attempt'] = time();
}

// Clear login attempts on success
function clearLoginAttempts($email)
{
    $key = 'login_attempts_' . md5($email);
    unset($_SESSION[$key]);
}

// Set HTTP security headers
function setSecurityHeaders()
{
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}
