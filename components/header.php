<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Get user's profile photo if logged in
$userPhoto = '../docs/user_icon.png'; // Default photo
if (isset($_SESSION['username'])) {
    require_once(__DIR__ . '/../database/user_db.php');
    try {
        $result = User::get_user($_SESSION['username']);
        
        if ($result && !empty($result['profile_photo'])) {
            $userPhoto = '../docs/' . $result['profile_photo'];
        }
    } catch (PDOException $e) {
        error_log("Error fetching user photo: " . $e->getMessage());
    }
}
?>

<head>
    <link rel="stylesheet" href="../components/warning_popup.css">
</head>

<header>
    <a href="home.php"><img src="../docs/logo.png" alt="Taskly Logo" class="logo"></a>
    <div class="header-right">
        <?php if (isset($_SESSION['username'])): ?>
            <a href="new_task.php" class="new-task-button">New Task</a>
            <a href="favorites.php" class="favorites-button">
                <img src="../docs/star.png" alt="Favorites" class="star-icon">
            </a>
        <?php else: ?>
            <a href="login.php?redirect=new_task" class="new-task-button">New Task</a>
            <a href="login.php?redirect=favorites" class="favorites-button">
                <img src="../docs/star.png" alt="Favorites" class="star-icon">
            </a>
        <?php endif; ?>
        <img src="<?= htmlspecialchars($userPhoto) ?>" alt="User Profile" class="user-icon" onclick="toggleUserMenu()">
        <div class="user-menu" id="userMenu">
            <?php if (isset($_SESSION['username'])): ?>
                <a href="user.php">
                    <img src="../docs/user_logo.png" alt="" class="menu-icon">
                    Profile
                </a>
                <a href="settings.php">
                    <img src="../docs/settings.png" alt="" class="menu-icon">
                    Settings
                </a>
                <a href="../actions/action_logout.php">
                    <img src="../docs/logout.png" alt="" class="menu-icon">
                    Logout
                </a>
            <?php else: ?>
                <a href="login.php">
                    <img src="../docs/login.png" alt="" class="menu-icon">
                    Log In
                </a>
                <a href="signup.php">
                    <img src="../docs/signup.png" alt="" class="menu-icon">
                    Sign Up
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>

<script>
function toggleUserMenu() {
    const menu = document.getElementById("userMenu");
    menu.style.display = menu.style.display === "block" ? "none" : "block";
}

// Close user menu when clicking outside
document.addEventListener('click', function(event) {
    const userMenu = document.getElementById('userMenu');
    const userIcon = document.querySelector('.user-icon');
    if (!userMenu) return;
    if (
        userMenu.style.display === 'block' &&
        !userMenu.contains(event.target) &&
        !userIcon.contains(event.target)
    ) {
        userMenu.style.display = 'none';
    }
});
</script>
