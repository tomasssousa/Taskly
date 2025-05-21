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
        <a href="favorites.php" class="favorites-button">
            <svg class="star-icon" viewBox="0 0 24 24" width="24" height="24">
                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" fill="currentColor"/>
            </svg>
        </a>
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
