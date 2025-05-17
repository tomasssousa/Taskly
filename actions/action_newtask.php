<?php
session_start();
require_once(__DIR__ . '/../database/connection.php');

$db = getDatabaseConnection();

$taskname = trim($_POST['taskname'] ?? '');
$taskdesc = trim($_POST['taskdesc'] ?? '');
$pricing = trim($_POST['pricing'] ?? '');
$deltime = $_POST['deltime'] ?? '';
$category = $_POST['category'] ?? '';

if (empty($taskname) || empty($taskdesc) || empty($pricing)) {
    $_SESSION['error'] = 'All fields are required.';
    header('Location: ../pages/new_task.php');
    exit();
}

if (!is_numeric($pricing)) {
    $_SESSION['error'] = 'Price must be a number.';
    header('Location: ../pages/new_task.php');
    exit();
}

try {
    $stmt = $db->prepare('
        INSERT INTO services (pricing, delivery_time, description, user, status)
        VALUES (?, ?, ?, ?, ?)
    ');
    
    $stmt->execute([
        $pricing,
        $deltime,
        $taskdesc,
        $_SESSION['username'], 
        'open'
    ]);

    $serviceId = $db->lastInsertId();

    if (isset($_FILES['taskphoto'])) {
        foreach ($_FILES['taskphoto']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['taskphoto']['error'][$key] === UPLOAD_ERR_OK) {
                $filename = uniqid() . '_' . $_FILES['taskphoto']['name'][$key];
                $uploadPath = __DIR__ . '/../uploads/' . $filename;
                
                if (move_uploaded_file($tmp_name, $uploadPath)) {
                    // Insert into media table
                    $stmt = $db->prepare('
                        INSERT INTO media (service_id, file_path, media_type)
                        VALUES (?, ?, ?)
                    ');
                    $stmt->execute([$serviceId, $filename, 'image']);
                }
            }
        }
    }

    if (!empty($category)) {
        $stmt = $db->prepare('
            INSERT INTO cate_serv (service_id, categorie)
            VALUES (?, ?)
        ');
        $stmt->execute([$serviceId, $category]);
    }

    header('Location: ../pages/home.php');
    exit();

} catch (PDOException $e) {
    $_SESSION['error'] = 'Failed to create task: ' . $e->getMessage();
    header('Location: ../pages/new_task.php');
    exit();
}
?>