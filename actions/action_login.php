<?php
session_start();
require_once(__DIR__ . '/../db/connection.php');

$db = getDatabaseConnection();

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

try {
    $stmt = $db->prepare('SELECT * FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['username'] = $user['username'];
        $_SESSION['admin'] = $user['admin_power'];
        header('Location: ../pages/home.php');
        exit();
    } else {
        $_SESSION['error'] = 'Incorrect username or password.';
        header('Location: ../pages/login.php');
        exit();
    }

} catch (PDOException $e) {
    $_SESSION['error'] = 'Login failed.';
    header('Location: ../pages/login.php');
    exit();
}
?>
