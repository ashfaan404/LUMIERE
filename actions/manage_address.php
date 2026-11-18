<?php
// actions/manage_address.php
require_once '../includes/db.php';
require_once '../includes/customer_check.php';

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Helper: unset all defaults for user
function clearDefaults($conn, $user_id) {
    $s = $conn->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?");
    $s->bind_param("i", $user_id);
    $s->execute();
}

switch ($action) {

    case 'add':
        $full_name = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $street = trim($_POST['street_address'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $state = trim($_POST['state'] ?? '');
        $zip = trim($_POST['zip_code'] ?? '');
        $country = trim($_POST['country'] ?? 'Sri Lanka');
        $is_default = isset($_POST['is_default']) && $_POST['is_default'] == '1' ? 1 : 0;

        if (empty($full_name) || empty($phone) || empty($street) || empty($city) || empty($state) || empty($zip)) {
            $_SESSION['error_message'] = "All required address fields must be filled.";
            header("Location: ../customer/addresses.php");
            exit();
        }

        if ($is_default) {
            clearDefaults($conn, $user_id);
        }

        // If no addresses exist yet, set as default automatically
        $chk = $conn->prepare("SELECT COUNT(*) as cnt FROM addresses WHERE user_id = ?");
        $chk->bind_param("i", $user_id);
        $chk->execute();
        $count = $chk->get_result()->fetch_assoc()['cnt'];
        if ($count == 0) $is_default = 1;

        $stmt = $conn->prepare("INSERT INTO addresses (user_id, full_name, phone, street_address, city, state, zip_code, country, is_default) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssssi", $user_id, $full_name, $phone, $street, $city, $state, $zip, $country, $is_default);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Address added successfully!";
        } else {
            $_SESSION['error_message'] = "Failed to add address.";
        }
        header("Location: ../customer/addresses.php");
        exit();

    case 'update':
        $address_id = intval($_POST['address_id'] ?? 0);
        $full_name = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $street = trim($_POST['street_address'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $state = trim($_POST['state'] ?? '');
        $zip = trim($_POST['zip_code'] ?? '');
        $country = trim($_POST['country'] ?? 'Sri Lanka');
        $is_default = isset($_POST['is_default']) && $_POST['is_default'] == '1' ? 1 : 0;

        if (!$address_id || empty($full_name) || empty($street)) {
            $_SESSION['error_message'] = "Please fill all required fields.";
            header("Location: ../customer/addresses.php");
            exit();
        }

        // Verify ownership
        $own = $conn->prepare("SELECT id FROM addresses WHERE id = ? AND user_id = ?");
        $own->bind_param("ii", $address_id, $user_id);
        $own->execute();
        if ($own->get_result()->num_rows === 0) {
            $_SESSION['error_message'] = "Address not found.";
            header("Location: ../customer/addresses.php");
            exit();
        }

        if ($is_default) {
            clearDefaults($conn, $user_id);
        }

        $stmt = $conn->prepare("UPDATE addresses SET full_name=?, phone=?, street_address=?, city=?, state=?, zip_code=?, country=?, is_default=? WHERE id=? AND user_id=?");
        $stmt->bind_param("sssssssiis", $full_name, $phone, $street, $city, $state, $zip, $country, $is_default, $address_id, $user_id);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Address updated successfully!";
        } else {
            $_SESSION['error_message'] = "Failed to update address.";
        }
        header("Location: ../customer/addresses.php");
        exit();

    case 'delete':
        $address_id = intval($_GET['id'] ?? 0);
        if (!$address_id) {
            header("Location: ../customer/addresses.php");
            exit();
        }

        // Verify ownership
        $own = $conn->prepare("SELECT id, is_default FROM addresses WHERE id = ? AND user_id = ?");
        $own->bind_param("ii", $address_id, $user_id);
        $own->execute();
        $addr = $own->get_result()->fetch_assoc();

        if (!$addr) {
            $_SESSION['error_message'] = "Address not found.";
            header("Location: ../customer/addresses.php");
            exit();
        }

        $stmt = $conn->prepare("DELETE FROM addresses WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $address_id, $user_id);

        if ($stmt->execute()) {
            // If deleted was default, set next one as default
            if ($addr['is_default']) {
                $next = $conn->prepare("SELECT id FROM addresses WHERE user_id = ? LIMIT 1");
                $next->bind_param("i", $user_id);
                $next->execute();
                $nxt = $next->get_result()->fetch_assoc();
                if ($nxt) {
                    $upd = $conn->prepare("UPDATE addresses SET is_default = 1 WHERE id = ?");
                    $upd->bind_param("i", $nxt['id']);
                    $upd->execute();
                }
            }
            $_SESSION['success_message'] = "Address deleted.";
        } else {
            $_SESSION['error_message'] = "Failed to delete address.";
        }
        header("Location: ../customer/addresses.php");
        exit();

    case 'set_default':
        $address_id = intval($_GET['id'] ?? 0);
        if (!$address_id) {
            header("Location: ../customer/addresses.php");
            exit();
        }

        // Verify ownership
        $own = $conn->prepare("SELECT id FROM addresses WHERE id = ? AND user_id = ?");
        $own->bind_param("ii", $address_id, $user_id);
        $own->execute();
        if ($own->get_result()->num_rows === 0) {
            header("Location: ../customer/addresses.php");
            exit();
        }

        clearDefaults($conn, $user_id);
        $stmt = $conn->prepare("UPDATE addresses SET is_default = 1 WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $address_id, $user_id);
        $stmt->execute();
        $_SESSION['success_message'] = "Default address updated!";
        header("Location: ../customer/addresses.php");
        exit();

    default:
        header("Location: ../customer/addresses.php");
        exit();
}
