<?php
// customer/profile.php
require_once '../includes/db.php';
require_once '../includes/customer_check.php';
require_once '../includes/header.php';

$user_id = $_SESSION['user_id'];

// Load user data
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Split name for display
$name_parts = explode(' ', $user['full_name'], 2);
$first_name = $name_parts[0] ?? '';
$last_name = $name_parts[1] ?? '';

// Get initials for avatar
$initials = strtoupper(substr($first_name, 0, 1) . substr($last_name, 0, 1));
if (empty($initials))
    $initials = '?';
?>
<link rel="stylesheet" href="../assets/css/customer.css">

<div class="account-wrapper">

    <!-- Sidebar -->
    <aside class="account-sidebar">
        <div class="account-sidebar-card">
            <div class="sidebar-avatar-section">
                <div class="avatar-circle" id="profile_pic_preview">
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
                <a href="profile.php" class="active"><span class="nav-icon">👤</span> My Profile</a>
                <a href="wishlist.php"><span class="nav-icon">❤️</span>My Wishlist</a>
                <a href="orders.php"><span class="nav-icon">📦</span> Order History</a>
                <a href="addresses.php"><span class="nav-icon">📍</span> Saved Addresses</a>
                <a href="../pages/logout.php" class="logout-link"><span class="nav-icon">🚪</span> Sign Out</a>
            </nav>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="account-main">
        <div class="account-page-header">
            <h2>My Profile</h2>
            <p>Manage your personal information and account security</p>
        </div>

        <div class="account-card">
            <div class="account-card-title">Profile Details</div>

            <form action="../actions/update_profile.php" method="POST" enctype="multipart/form-data">

                <!-- Profile Picture -->
                <div class="profile-pic-upload">
                    <div class="profile-pic-preview" id="profile_pic_preview" style="width:90px; height:90px; border-radius:50%; background:#fdfbf7; border:3px solid #C5A059; display:flex; align-items:center; justify-content:center; font-size:2rem; font-weight:bold; color:#C5A059; overflow:hidden; flex-shrink:0;">
                        <?php if (!empty($user['profile_pic']) && file_exists('../' . $user['profile_pic'])): ?>
                            <img src="../<?php echo htmlspecialchars($user['profile_pic']); ?>" alt="Profile" style="width:100%;height:100%;object-fit:cover;">
                        <?php
else: ?>
                            <?php echo htmlspecialchars($initials); ?>
                        <?php
endif; ?>
                    </div>
                    <div class="profile-pic-actions">
                        <label for="profile_pic_input">📂 Change Photo</label>
                        <input type="file" id="profile_pic_input" name="profile_pic" accept="image/*" style="display:none">
                        <span>JPG, PNG, GIF — max 2MB</span>
                        <?php if (!empty($user['profile_pic'])): ?>
                            <label style="background:#fff; border:1px solid #d32f2f; color:#d32f2f; cursor:pointer; padding:0.4rem 0.9rem; border-radius:5px; font-size:0.82rem; margin-top:0.3rem; display:inline-block;">
                                <input type="checkbox" name="remove_pic" value="1" style="margin-right:0.3rem;">Remove photo
                            </label>
                        <?php
endif; ?>
                    </div>
                </div>

                <!-- Name fields -->
                <div class="profile-form-row">
                    <div class="account-form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>" required placeholder="First name">
                    </div>
                    <div class="account-form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>" placeholder="Last name">
                    </div>
                </div>

                <!-- Email -->
                <div class="account-form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required placeholder="you@email.com">
                </div>

                <!-- Phone (display only, no OTP involved) -->
                <div class="account-form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" placeholder="+91 00000 00000">
                </div>

                <div style="display:flex; gap:1rem; flex-wrap:wrap; margin-top:0.5rem;">
                    <button type="submit" class="account-btn">Save Changes</button>
                    <a href="profile.php" class="account-btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../assets/js/customer.js"></script>
<?php require_once '../includes/footer.php'; ?>
