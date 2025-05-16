<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Get user's profile photo if logged in
$userPhoto = '../docs/user_icon.png'; // Default photo
if (isset($_SESSION['username'])) {
    require_once(__DIR__ . '/../database/connection.php');
    try {
        $db = getDatabaseConnection();
        $stmt = $db->prepare("SELECT profile_photo FROM users WHERE username = ?");
        $stmt->execute([$_SESSION['username']]);
        $result = $stmt->fetch();
        if ($result && !empty($result['profile_photo'])) {
            $userPhoto = '../docs/' . $result['profile_photo'];
        }
    } catch (PDOException $e) {
        error_log("Error fetching user photo: " . $e->getMessage());
    }
}
?>

<header>
    <a href="home.php"><img src="../docs/logo.png" alt="Taskly Logo" class="logo"></a>
    <div class="header-right">
        <a href="new_task.php" class="new-task-button">New Task</a>
        <img src="<?= htmlspecialchars($userPhoto) ?>" alt="User Profile" class="user-icon" onclick="toggleUserMenu()">
        <div class="user-menu" id="userMenu">
            <?php if (isset($_SESSION['username'])): ?>
                <a href="user.php">Profile</a>
                <a href="settings.php">Settings</a>
                <a href="../actions/action_logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Log In</a>
                <a href="signup.php">Sign Up</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<script>
function toggleUserMenu() {
    const menu = document.getElementById("userMenu");
    menu.style.display = menu.style.display === "block" ? "none" : "block";
}
</script>
