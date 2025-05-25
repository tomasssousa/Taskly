<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$userPhoto = '../docs/user_icon.png'; 
$isAdmin = false;
$isTasker = false;

if (isset($_SESSION['username'])) {
    require_once(__DIR__ . '/../database/user_db.php');
    try {
        $result = User::get_user($_SESSION['username']);
        
        if ($result) {
            if (!empty($result['profile_photo'])) {
                $userPhoto = '../docs/' . $result['profile_photo'];
            }
            $isAdmin = $result['admin_power'] ?? false;
            $isTasker = $result['task_mode'] ?? false;
        }
    } catch (PDOException $e) {
        error_log("Error fetching user data: " . $e->getMessage());
    }
}
?>

<header>
    <a href="home.php"><img src="../docs/logo.png" alt="Taskly Logo" class="logo"></a>
    <div class="header-right">
        <?php if (isset($_SESSION['username'])): ?>
            <?php if ($isTasker): ?>
                <a href="new_task.php" class="new-task-button">New Task</a>
            <?php endif; ?>
            <a href="message_hub.php" class="messages-button">
                <img src="../docs/message.png" alt="Messages" class="message-icon">
            </a>
            <a href="favorites.php" class="favorites-button">
                <img src="../docs/star.png" alt="Favorites" class="star-icon">
            </a>
        <?php else: ?>
            <a href="login.php?redirect=messages" class="messages-button">
                <img src="../docs/message.png" alt="Messages" class="message-icon">
            </a>
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
                <?php if ($isAdmin): ?>
                    <a href="admin.php">
                        <img src="../docs/dashboard.png" alt="" class="menu-icon">
                        Admin Dashboard
                    </a>
                <?php endif; ?>
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
