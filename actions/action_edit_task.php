<?php
session_start();

require_once(__DIR__ . '/../database/service_db.php');
require_once(__DIR__ . '/../database/user_db.php');

// Check if user is logged in and in tasker mode
if (!isset($_SESSION['username'])) {
    $_SESSION['error'] = 'You must be logged in.';
    header('Location: ../pages/login.php');
    exit();
}

$currentUser = User::get_user($_SESSION['username']);
if (!$currentUser || !$currentUser['task_mode']) {
    $_SESSION['error'] = 'You must be in Tasker mode to edit tasks.';
    header('Location: ../pages/settings.php');
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method.';
    header('Location: ../pages/home.php');
    exit();
}

// Get form data
$taskId = $_POST['task_id'] ?? null;
$taskname = trim($_POST['taskname'] ?? '');
$taskdesc = trim($_POST['taskdesc'] ?? '');
$pricing = $_POST['pricing'] ?? '';
$deltime = $_POST['deltime'] ?? '';
$category = $_POST['category'] ?? '';
$removeImages = $_POST['remove_images'] ?? [];

// Validate required fields
if (!$taskId || !$taskname || !$taskdesc || !$pricing || !$deltime || !$category) {
    $_SESSION['error'] = 'All fields are required.';
    header('Location: ../pages/edit_task.php?id=' . urlencode($taskId));
    exit();
}

// Validate pricing
if (!is_numeric($pricing) || $pricing <= 0) {
    $_SESSION['error'] = 'Please enter a valid price.';
    header('Location: ../pages/edit_task.php?id=' . urlencode($taskId));
    exit();
}

try {
    // Get existing task using Service class
    $existingTask = Service::get_service_with_media($taskId);
    
    if (!$existingTask || $existingTask['user'] !== $_SESSION['username']) {
        $_SESSION['error'] = 'Task not found or you do not have permission to edit this task.';
        header('Location: ../pages/home.php');
        exit();
    }
    
    // Handle image uploads
    $newImages = [];
    if (isset($_FILES['taskphoto']) && $_FILES['taskphoto']['error'][0] !== UPLOAD_ERR_NO_FILE) {
        foreach ($_FILES['taskphoto']['tmp_name'] as $index => $tmpName) {
            if ($_FILES['taskphoto']['error'][$index] === UPLOAD_ERR_OK) {
                $originalName = $_FILES['taskphoto']['name'][$index];
                $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (in_array(strtolower($extension), $allowedExtensions)) {
                    $filename = 'uploads/' . uniqid() . '_' . time() . '.' . $extension;
                    $uploadPath = __DIR__ . '/../docs/' . $filename;
                    
                    if (!is_dir(dirname($uploadPath))) {
                        mkdir(dirname($uploadPath), 0777, true);
                    }
                    
                    if (move_uploaded_file($tmpName, $uploadPath)) {
                        $newImages[] = $filename;
                    }
                }
            }
        }
    }
    
    // Get current images from all_images (what the user sees)
    $currentImages = [];
    if (!empty($existingTask['all_images'])) {
        $currentImages = array_filter(array_map('trim', explode(',', $existingTask['all_images'])));
    }
    
    // Remove selected images
    if (!empty($removeImages)) {
        foreach ($removeImages as $imageToRemove) {
            // Remove from filesystem
            $imagePath = __DIR__ . '/../docs/' . trim($imageToRemove);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
            
            // Remove from array
            $currentImages = array_filter($currentImages, function($img) use ($imageToRemove) {
                return trim($img) !== trim($imageToRemove);
            });
        }
    }
    
    $finalImages = array_merge($currentImages, $newImages);
    $imageString = !empty($finalImages) ? implode(',', $finalImages) : '';
    
    $success = Service::update_service($taskId, $taskname, $taskdesc, $pricing, $deltime, $category, $imageString, $_SESSION['username']);
    
    if ($success) {
        $_SESSION['success'] = 'Task updated successfully!';
        header('Location: ../pages/task.php?id=' . urlencode($taskId));
    } else {
        $_SESSION['error'] = 'Failed to update task. Please try again.';
        header('Location: ../pages/edit_task.php?id=' . urlencode($taskId));
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = 'Error updating task: ' . $e->getMessage();
    header('Location: ../pages/edit_task.php?id=' . urlencode($taskId));
}
?>