<?php
// actions/update_profile.php
require_once '../includes/db.php';
require_once '../includes/customer_check.php';

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../customer/profile.php");
    exit();
}

$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');

$full_name = trim($first_name . ' ' . $last_name);

// Basic validation
if (empty($first_name) || empty($email)) {
    $_SESSION['error_message'] = "First name and email are required.";
    header("Location: ../customer/profile.php");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error_message'] = "Please enter a valid email address.";
    header("Location: ../customer/profile.php");
    exit();
}

// Check if email is taken by another user
$stmt_check = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
$stmt_check->bind_param("si", $email, $user_id);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows > 0) {
    $_SESSION['error_message'] = "That email address is already used by another account.";
    header("Location: ../customer/profile.php");
    exit();
}

// Handle profile pic upload
$profile_pic_path = null;
$remove_pic = isset($_POST['remove_pic']) && $_POST['remove_pic'] == '1';

if ($remove_pic) {
    // Get existing pic and delete file
    $stmt_cur = $conn->prepare("SELECT profile_pic FROM users WHERE user_id = ?");
    $stmt_cur->bind_param("i", $user_id);
    $stmt_cur->execute();
    $current = $stmt_cur->get_result()->fetch_assoc();
    if (!empty($current['profile_pic']) && file_exists('../' . $current['profile_pic'])) {
        unlink('../' . $current['profile_pic']);
    }
    $profile_pic_path = ''; // clear it
}

if (!$remove_pic && !empty($_FILES['profile_pic']['name'])) {
    $file = $_FILES['profile_pic'];
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 2 * 1024 * 1024; // 2MB

    if (!in_array($file['type'], $allowed_types)) {
        $_SESSION['error_message'] = "Profile picture must be a JPG, PNG, GIF or WEBP image.";
        header("Location: ../customer/profile.php");
        exit();
    }

    if ($file['size'] > $max_size) {
        $_SESSION['error_message'] = "Profile picture must be less than 2MB.";
        header("Location: ../customer/profile.php");
        exit();
    }

    $upload_dir = '../assets/uploads/profiles/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'user_' . $user_id . '_' . time() . '.' . strtolower($ext);
    $dest = $upload_dir . $filename;

    if (move_uploaded_file($file['tmp_name'], $dest)) {
        // Delete old pic
        $stmt_cur = $conn->prepare("SELECT profile_pic FROM users WHERE user_id = ?");
        $stmt_cur->bind_param("i", $user_id);
        $stmt_cur->execute();
        $current = $stmt_cur->get_result()->fetch_assoc();
        if (!empty($current['profile_pic']) && file_exists('../' . $current['profile_pic'])) {
            unlink('../' . $current['profile_pic']);
        }
        $profile_pic_path = 'assets/uploads/profiles/' . $filename;
    } else {
        $_SESSION['error_message'] = "Failed to upload image. Please try again.";
        header("Location: ../customer/profile.php");
        exit();
    }
}

// Build update query
if ($profile_pic_path !== null) {
    $stmt_up = $conn->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, profile_pic = ? WHERE user_id = ?");
    $stmt_up->bind_param("ssssi", $full_name, $email, $phone, $profile_pic_path, $user_id);
} else {
    $stmt_up = $conn->prepare("UPDATE users SET full_name = ?, email = ?, phone = ? WHERE user_id = ?");
    $stmt_up->bind_param("sssi", $full_name, $email, $phone, $user_id);
}

if ($stmt_up->execute()) {
    $_SESSION['success_message'] = "Profile updated successfully!";
} else {
    $_SESSION['error_message'] = "Something went wrong. Please try again.";
}

// Update session name if changed
$_SESSION['user_name'] = $full_name;

header("Location: ../customer/profile.php");
exit();
