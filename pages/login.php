<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Taskly | Login</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/container.css">
    <link rel="icon" href="../docs/logo.png" type="icon/x-icon">
</head>
<body>
<?php include_once(__DIR__ . '/../components/header.php'); ?>
<main>
    <div class="container">
        <h2>Login</h2>
        <?php if (isset($_SESSION['error'])): ?>
            <p class="error"><?= htmlspecialchars($_SESSION['error']) ?></p>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        <form action="../actions/action_login.php" method="post">
            <input type="text" placeholder="Username" name="username" required>
            <input type="password" placeholder="Password" name="password" required>
            <?php if (isset($_GET['redirect'])): ?>
                <input type="hidden" name="redirect" value="<?= htmlspecialchars($_GET['redirect']) ?>">
            <?php endif; ?>
            <button type="submit">Login</button>
            <p>Not a Member? <a href="signup.php">Sign up</a></p>
        </form>
    </div>
</main>
</body>
</html>
