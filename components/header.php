<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>

<header>
    <a href="home.php"><img src="../docs/logo.png" alt="Taskly Logo" class="logo"></a>
    <div class="header-right">
        <a href="new_task.php" class="new-task-button">New Task</a>
        <img src="../docs/user_icon.png" alt="User Profile" class="user-icon" onclick="toggleUserMenu()">
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
