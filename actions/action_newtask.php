<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: ../pages/login.php');
    exit();
}

require_once(__DIR__ . '/../database/connection.php');
require_once(__DIR__ . '/../database/service_db.php');
require_once(__DIR__ . '/../database/media_db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $taskTitle = trim($_POST['taskname']); // Form sends 'taskname'
    $taskDescription = trim($_POST['taskdesc']); 
    $pricing = (int)trim($_POST['pricing']);
    $deliveryTime = $_POST['deltime'];
    $category = $_POST['category'];
    $username = $_SESSION['username'];

    if (empty($taskTitle) || empty($taskDescription) || empty($pricing)) {
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
        $db = getDatabaseConnection();
        $db->beginTransaction();

        // Insert into services table using taskname column
        $stmt = $db->prepare("
            INSERT INTO services (taskname, description, pricing, delivery_time, category, user, rating, Nrating) 
            VALUES (?, ?, ?, ?, ?, ?, 5, 1)
        ");
        
        $success = $stmt->execute([
            $taskTitle,      // maps to taskname column
            $taskDescription, 
            $pricing,
            $deliveryTime,
            $category,
            $username
        ]);
        
        if ($success) {
            $serviceId = $db->lastInsertId();
            
            // Handle file uploads if any
            if (isset($_FILES['taskphoto']) && !empty($_FILES['taskphoto']['name'][0])) {
                $uploadDir = __DIR__ . '/../docs/uploads';
                if (!is_dir($uploadDir)) {
                    if (!mkdir($uploadDir, 0755, true)) {
                        $_SESSION['error'] = 'Failed to create uploads directory';
                        header('Location: ../pages/new_task.php');
                        exit();
                    }
                }
                
                foreach ($_FILES['taskphoto']['tmp_name'] as $index => $tmpName) {
                    if (!empty($tmpName)) {
                        $fileName = uniqid() . '_' . $_FILES['taskphoto']['name'][$index];
                        $uploadPath = $uploadDir . '/' . $fileName;
                        
                        if (move_uploaded_file($tmpName, $uploadPath)) {
                            // Insert into media table
                            $mediaStmt = $db->prepare("INSERT INTO media (service_id, file_path, file_type) VALUES (?, ?, 'image')");
                            $mediaStmt->execute([$serviceId, 'uploads/' . $fileName]);
                        }
                    }
                }
            }
            
            $_SESSION['success'] = 'Task created successfully!';
            header('Location: ../pages/user.php');
        } else {
            $_SESSION['error'] = 'Failed to create task.';
            header('Location: ../pages/new_task.php');
        }

        $db->commit();
        
    } catch (PDOException $e) {
        $db->rollBack();
        error_log("Database error: " . $e->getMessage());
        $_SESSION['error'] = 'Failed to create task: ' . $e->getMessage();
        header('Location: ../pages/new_task.php');
    }
} else {
    header('Location: ../pages/new_task.php');
}
exit();
?>