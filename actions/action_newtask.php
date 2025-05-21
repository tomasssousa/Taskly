<?php
session_start();
require_once(__DIR__ . '/../database/connection.php');

$db = getDatabaseConnection();

// Create uploads directory if it doesn't exist
$uploadDir = __DIR__ . '/../docs/uploads';
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        $_SESSION['error'] = 'Failed to create uploads directory';
        header('Location: ../pages/new_task.php');
        exit();
    }
}

// Validate form data
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
    $db->beginTransaction();

    // Insert service
    $stmt = $db->prepare('
        INSERT INTO services (taskname, pricing, delivery_time, description, user, status)
        VALUES (?, ?, ?, ?, ?, ?)
    ');
    
    $stmt->execute([
        $taskname,      // Add taskname here
        $pricing,
        $deltime,
        $taskdesc,
        $_SESSION['username'], 
        'open'
    ]);

    $serviceId = $db->lastInsertId();

    // Handle image uploads
    if (isset($_FILES['taskphoto']) && is_array($_FILES['taskphoto']['tmp_name'])) {
        foreach ($_FILES['taskphoto']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['taskphoto']['error'][$key] === UPLOAD_ERR_OK) {
                $tempFile = $tmp_name;
                if (!is_uploaded_file($tempFile)) {
                    throw new Exception('Invalid upload attempt');
                }

                // Get file extension
                $fileInfo = pathinfo($_FILES['taskphoto']['name'][$key]);
                $extension = strtolower($fileInfo['extension']);
                
                // Validate extension
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                if (!in_array($extension, $allowedExtensions)) {
                    throw new Exception('Invalid file type. Allowed: ' . implode(', ', $allowedExtensions));
                }

                // Generate unique filename
                $filename = 'task_' . $serviceId . '_' . uniqid() . '.' . $extension;
                $targetPath = $uploadDir . '/' . $filename;
                
                // Move uploaded file
                if (!move_uploaded_file($tempFile, $targetPath)) {
                    throw new Exception('Failed to save uploaded file');
                }

                // Insert into media table with consistent path format
                $stmt = $db->prepare('
                    INSERT INTO media (service_id, file_path, media_type)
                    VALUES (?, ?, ?)
                ');
                $stmt->execute([$serviceId, 'uploads/' . $filename, 'image']);

                // Set first image as service's main image
                if ($key === 0) {
                    $stmt = $db->prepare('
                        UPDATE services 
                        SET images = ? 
                        WHERE id = ?
                    ');
                    $stmt->execute(['uploads/' . $filename, $serviceId]);
                }
            }
        }
    }

    // Handle category
    if (!empty($category)) {
        $stmt = $db->prepare('
            INSERT INTO cate_serv (service_id, categorie)
            VALUES (?, ?)
        ');
        $stmt->execute([$serviceId, $category]);
    }

    $db->commit();
    header('Location: ../pages/home.php');
    exit();

} catch (Exception $e) {
    $db->rollBack();
    $_SESSION['error'] = 'Failed to create task: ' . $e->getMessage();
    header('Location: ../pages/new_task.php');
    exit();
}
?>