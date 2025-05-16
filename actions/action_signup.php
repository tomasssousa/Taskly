<?php
session_start();
require_once(__DIR__ . '/../database/user_db.php');


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
    
    if (User::get_user($username)) {
        $_SESSION['error'] = 'Username already taken.';
        header('Location: ../pages/signup.php');
        exit();
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    User::create($username, $name, $hashedPassword, $email, 0);

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
