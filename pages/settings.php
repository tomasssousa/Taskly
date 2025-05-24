<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Taskly | Edit your account settings</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="css/style.css">
        <link rel="stylesheet" href="css/container.css">
        <link rel="stylesheet" href="css/settings.css">
        <script src="js/settings.js" defer></script>
        <link rel="icon" href="../docs/logo.png" type="icon/x-icon">
    </head>
<body class="settings-page">
<?php 
session_start();
include_once(__DIR__ . '/../components/header.php'); 

// Get current user data
require_once(__DIR__ . '/../database/user_db.php');
$current_user = null;
if (isset($_SESSION['username'])) {
    $current_user = User::get_user($_SESSION['username']);
}
?>

<main>
    <div class="settings-tabs">
        <button class="tab-btn active" data-tab="analytics">Analytics</button>
        <button class="tab-btn" data-tab="settings">Settings</button>
    </div>
    <div class="container">
        <div class="tab-content" id="analytics-tab">
            <h3>Analytics</h3>
            <p>Coming soon! Here you'll see your account and task stats.</p>
        </div>
        <div class="tab-content" id="settings-tab" style="display:none;">
            <h3>Edit Account Details</h3>
            
            <!-- Display messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?= $_SESSION['error'] ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['info'])): ?>
                <div class="alert alert-info"><?= $_SESSION['info'] ?></div>
                <?php unset($_SESSION['info']); ?>
            <?php endif; ?>
            
            <form action="../actions/action_update_account.php" method="post">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" value="<?= htmlspecialchars($current_user['username'] ?? '') ?>" readonly>
                </div>
                
                <div class="form-group">
                    <label for="current_password">Current Password:</label>
                    <input type="password" id="current_password" name="current_password" placeholder="Enter current password" required>
                </div>
                
                <div class="form-group">
                    <label for="new_email">New Email (optional):</label>
                    <input type="email" id="new_email" name="new_email" placeholder="<?= htmlspecialchars($current_user['email'] ?? 'Enter new email') ?>">
                </div>
                
                <div class="form-group">
                    <label for="new_password">New Password (optional):</label>
                    <input type="password" id="new_password" name="new_password" placeholder="Enter new password (leave blank to keep current)">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password">
                </div>
                
                <div class="toggle-row">
                    <label>Account Type:</label>
                    <div class="account-type-buttons">
                        <button type="button" class="account-type-btn <?= (!$current_user['task_mode']) ? 'active' : '' ?>" data-type="user">User</button>
                        <button type="button" class="account-type-btn <?= ($current_user['task_mode']) ? 'active' : '' ?>" data-type="tasker">Tasker</button>
                    </div>
                    <input type="hidden" id="account_type" name="account_type" value="<?= $current_user['task_mode'] ? 'tasker' : 'user' ?>">
                </div>
                
                <button type="submit" class="update-btn">Update Account</button>
            </form>
            
            <form action="../actions/action_delete_account.php" method="post" style="margin-top: 1em;">
                <button type="submit" class="delete-account-btn" onclick="return confirm('Are you sure you want to delete your account? This action cannot be undone.')">Delete Account</button>
            </form>
        </div>
    </div>
</main>

</body>
</html>