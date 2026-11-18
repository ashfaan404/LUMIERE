<?php
// customer/addresses.php
require_once '../includes/db.php';
require_once '../includes/customer_check.php';
require_once '../includes/header.php';

$user_id = $_SESSION['user_id'];

// Load user info for sidebar
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$name_parts = explode(' ', $user['full_name'], 2);
$first_name = $name_parts[0] ?? '';
$last_name = $name_parts[1] ?? '';
$initials = strtoupper(substr($first_name, 0, 1) . substr($last_name, 0, 1));

// Load all addresses for this user (table may not exist before migration)
$addresses_list = [];
$addresses_table_exists = true;
try {
    $stmt_addr = $conn->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, created_at ASC");
    $stmt_addr->bind_param("i", $user_id);
    $stmt_addr->execute();
    $addresses_result = $stmt_addr->get_result();
    while ($a = $addresses_result->fetch_assoc()) {
        $addresses_list[] = $a;
    }

    // Auto-migrate address from users table if addresses table is empty
    if (empty($addresses_list) && !empty($user['address'])) {
        $reg_address = $user['address'];
        $reg_name = $user['full_name'];
        $reg_phone = $user['phone'];
        $stmt_mig = $conn->prepare("INSERT INTO addresses (user_id, full_name, phone, street_address, city, state, zip_code, country, is_default) VALUES (?, ?, ?, ?, 'Colombo', 'Western Province', '00100', 'Sri Lanka', 1)");
        $stmt_mig->bind_param("isss", $user_id, $reg_name, $reg_phone, $reg_address);
        if ($stmt_mig->execute()) {
            $stmt_addr->execute();
            $addresses_result = $stmt_addr->get_result();
            // Refetch
            $addresses_list = [];
            while ($a = $addresses_result->fetch_assoc()) {
                $addresses_list[] = $a;
            }
        }
    }
}
catch (Throwable $e) {
    $addresses_table_exists = false;
}

// Handle edit: preload address if ?edit=ID
$edit_address = null;
if ($addresses_table_exists && isset($_GET['edit']) && intval($_GET['edit']) > 0) {
    $edit_id = intval($_GET['edit']);
    try {
        $stmt_edit = $conn->prepare("SELECT * FROM addresses WHERE id = ? AND user_id = ?");
        $stmt_edit->bind_param("ii", $edit_id, $user_id);
        $stmt_edit->execute();
        $edit_address = $stmt_edit->get_result()->fetch_assoc();
    }
    catch (Throwable $e) {
        $edit_address = null;
    }
}
?>
<link rel="stylesheet" href="../assets/css/customer.css">

<div class="account-wrapper">

    <!-- Sidebar -->
    <aside class="account-sidebar">
        <div class="account-sidebar-card">
            <div class="sidebar-avatar-section">
                <div class="avatar-circle">
                    <?php if (!empty($user['profile_pic']) && file_exists('../' . $user['profile_pic'])): ?>
                        <img src="../<?php echo htmlspecialchars($user['profile_pic']); ?>" alt="Profile">
                    <?php
else: ?>
                        <?php echo htmlspecialchars($initials); ?>
                    <?php
endif; ?>
                </div>
                <div class="sidebar-user-name"><?php echo htmlspecialchars($user['full_name']); ?></div>
                <div class="sidebar-user-email"><?php echo htmlspecialchars($user['email']); ?></div>
            </div>
            <nav class="sidebar-nav">
                <a href="profile.php"><span class="nav-icon">👤</span> My Profile</a>
                <a href="wishlist.php"><span class="nav-icon">❤️</span>My Wishlist</a>
                <a href="orders.php"><span class="nav-icon">📦</span> Order History</a>
                <a href="addresses.php" class="active"><span class="nav-icon">📍</span> Saved Addresses</a>
                <a href="../pages/logout.php" class="logout-link"><span class="nav-icon">🚪</span> Sign Out</a>
            </nav>
        </div>
    </aside>

    <!-- Main -->
    <div class="account-main">
        <div class="account-page-header">
            <h2>Saved Addresses</h2>
            <p>Manage your delivery addresses for faster checkout</p>
        </div>

        <div class="account-card">
            <div class="account-card-title">
                Your Addresses
                <button class="account-btn" onclick="openAddressModal('addModal')" style="font-size:0.82rem; padding:0.5rem 1.2rem;">+ Add New</button>
            </div>

            <div class="address-grid">
                <?php if (empty($addresses_list)): ?>
                <div class="empty-state" style="padding:2rem; grid-column:1/-1;">
                    <div class="empty-state-icon">📍</div>
                    <p>You have no saved addresses yet.<br>Add one using the button above!</p>
                </div>
                <?php
else: ?>
                <?php foreach ($addresses_list as $addr): ?>
                <div class="address-card <?php echo $addr['is_default'] ? 'is-default' : ''; ?>">
                    <div class="address-card-label">
                        <span class="address-label-text">
                            <?php echo htmlspecialchars($addr['full_name']); ?>
                        </span>
                        <?php if ($addr['is_default']): ?>
                            <span class="badge-default">✓ Default</span>
                        <?php
        endif; ?>
                    </div>
                    <div class="address-card-text">
                        <?php echo htmlspecialchars($addr['street_address']); ?><br>
                        <?php echo htmlspecialchars($addr['city']); ?>, <?php echo htmlspecialchars($addr['state']); ?> <?php echo htmlspecialchars($addr['zip_code']); ?><br>
                        <?php echo htmlspecialchars($addr['country']); ?><br>
                        <strong>📞</strong> <?php echo htmlspecialchars($addr['phone']); ?>
                    </div>
                    <div class="address-card-actions" style="display:flex; gap:0.7rem; flex-wrap:wrap; margin-top:0.8rem; align-items:center;">
                        <a href="addresses.php?edit=<?php echo $addr['id']; ?>" 
                           style="padding:0.4rem 0.9rem; border:1.5px solid #3E2723; border-radius:6px; font-size:0.8rem; font-weight:600; color:#3E2723; text-decoration:none;">✏️ Edit</a>
                        <?php if (!$addr['is_default']): ?>
                            <a href="../actions/manage_address.php?action=set_default&id=<?php echo $addr['id']; ?>" 
                               style="padding:0.4rem 0.9rem; border:1.5px solid #C5A059; border-radius:6px; font-size:0.8rem; font-weight:600; color:#C5A059; text-decoration:none;">⭐ Set Default</a>
                        <?php
        endif; ?>
                        <button type="button" 
                            onclick="if(confirmDelete('Delete this address?')) window.location='../actions/manage_address.php?action=delete&id=<?php echo $addr['id']; ?>'"
                            style="padding:0.4rem 0.9rem; border:1.5px solid #d32f2f; border-radius:6px; font-size:0.8rem; font-weight:600; color:#d32f2f; background:none; cursor:pointer;">🗑 Delete</button>
                    </div>
                </div>
                <?php
    endforeach; ?>
                <?php
endif; ?>

                <!-- Add new card -->
                <div class="address-card-add" onclick="openAddressModal('addModal')">
                    <div class="address-add-icon">+</div>
                    <div class="address-add-text">Add New Address</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Address Modal -->
<div class="modal-overlay" id="addModal">
    <div class="modal-box">
        <button class="modal-close" onclick="closeAddressModal('addModal')">✕</button>
        <h3 style="margin-bottom:1.5rem; font-family:'Georgia',serif; color:#3E2723;">Add New Address</h3>
        <form action="../actions/manage_address.php" method="POST">
            <input type="hidden" name="action" value="add">
            <div class="profile-form-row">
                <div class="account-form-group">
                    <label>Full Name *</label>
                    <input type="text" name="full_name" required placeholder="Your full name">
                </div>
                <div class="account-form-group">
                    <label>Phone *</label>
                    <input type="tel" name="phone" required placeholder="+94 00000 00000">
                </div>
            </div>
            <div class="account-form-group">
                <label>Street Address *</label>
                <input type="text" name="street_address" required placeholder="House no, street, area">
            </div>
            <div class="profile-form-row">
                <div class="account-form-group">
                    <label>City *</label>
                    <input type="text" name="city" required placeholder="City">
                </div>
                <div class="account-form-group">
                    <label>State *</label>
                    <input type="text" name="state" required placeholder="State">
                </div>
            </div>
            <div class="profile-form-row">
                <div class="account-form-group">
                    <label>ZIP / Postal Code *</label>
                    <input type="text" name="zip_code" required placeholder="600001">
                </div>
                <div class="account-form-group">
                    <label>Country *</label>
                    <input type="text" name="country" required value="Sri Lanka" placeholder="Country">
                </div>
            </div>
            <div class="account-form-group">
                <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer; font-size:0.9rem; text-transform:none; letter-spacing:0;">
                    <input type="checkbox" name="is_default" value="1"> Set as default address
                </label>
            </div>
            <button type="submit" class="account-btn" style="width:100%;">Save Address</button>
        </form>
    </div>
</div>

<?php if ($edit_address): ?>
<!-- Edit Address Modal -->
<script>document.addEventListener('DOMContentLoaded', function() { openAddressModal('editModal'); });</script>
<div class="modal-overlay" id="editModal">
    <div class="modal-box">
        <button class="modal-close" onclick="closeAddressModal('editModal')">✕</button>
        <h3 style="margin-bottom:1.5rem; font-family:'Georgia',serif; color:#3E2723;">Edit Address</h3>
        <form action="../actions/manage_address.php" method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="address_id" value="<?php echo $edit_address['id']; ?>">
            <div class="profile-form-row">
                <div class="account-form-group">
                    <label>Full Name *</label>
                    <input type="text" name="full_name" required value="<?php echo htmlspecialchars($edit_address['full_name']); ?>">
                </div>
                <div class="account-form-group">
                    <label>Phone *</label>
                    <input type="tel" name="phone" required value="<?php echo htmlspecialchars($edit_address['phone']); ?>">
                </div>
            </div>
            <div class="account-form-group">
                <label>Street Address *</label>
                <input type="text" name="street_address" required value="<?php echo htmlspecialchars($edit_address['street_address']); ?>">
            </div>
            <div class="profile-form-row">
                <div class="account-form-group">
                    <label>City *</label>
                    <input type="text" name="city" required value="<?php echo htmlspecialchars($edit_address['city']); ?>">
                </div>
                <div class="account-form-group">
                    <label>State *</label>
                    <input type="text" name="state" required value="<?php echo htmlspecialchars($edit_address['state']); ?>">
                </div>
            </div>
            <div class="profile-form-row">
                <div class="account-form-group">
                    <label>ZIP Code *</label>
                    <input type="text" name="zip_code" required value="<?php echo htmlspecialchars($edit_address['zip_code']); ?>">
                </div>
                <div class="account-form-group">
                    <label>Country *</label>
                    <input type="text" name="country" required value="<?php echo htmlspecialchars($edit_address['country']); ?>">
                </div>
            </div>
            <div class="account-form-group">
                <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer; font-size:0.9rem; text-transform:none; letter-spacing:0;">
                    <input type="checkbox" name="is_default" value="1" <?php echo $edit_address['is_default'] ? 'checked' : ''; ?>> Set as default address
                </label>
            </div>
            <button type="submit" class="account-btn" style="width:100%;">Update Address</button>
        </form>
    </div>
</div>
<?php
endif; ?>

<script src="../assets/js/customer.js"></script>
<?php require_once '../includes/footer.php'; ?>
