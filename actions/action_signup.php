<?php
session_start();
require_once(__DIR__ . '/../database/connection.php');

$db = getDatabaseConnection();

$username = trim($_POST['username'] ?? '');
$name = trim($_POST['name'] ?? '');
$password = $_POST['password'] ?? '';
$email = trim($_POST['email'] ?? '');

if (!preg_match("/^[a-zA-Z0-9_]{3,20}$/", $username) || strlen($password) < 6) {
    $_SESSION['error'] = 'Invalid username or password too short.';
    header('Location: ../pages/signup.php');
    exit();
}

try {
    $stmt = $db->prepare('SELECT username FROM users WHERE username = ?');
    $stmt->execute([$username]);

    if ($stmt->fetch()) {
        $_SESSION['error'] = 'Username already taken.';
        header('Location: ../pages/signup.php');
        exit();
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $db->prepare('
        INSERT INTO users (username, name, password, email, admin_power)
        VALUES (?, ?, ?, ?, 0)
    ');
    $stmt->execute([$username, $name, $hashedPassword, $email]);

    session_regenerate_id(true);
    $_SESSION['username'] = $username;
    header('Location: ../pages/home.php');
    exit();

} catch (PDOException $e) {
    $_SESSION['error'] = 'Failed to register user.';
    header('Location: ../pages/signup.php');
    exit();
}
?>
