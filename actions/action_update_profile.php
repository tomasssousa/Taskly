<?php
require_once(__DIR__ . '/../database/user_db.php');
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    die(json_encode(['success' => false, 'error' => 'Not authenticated']));
}

try {
    
    // Create uploads directory if it doesn't exist
    $uploadDir = __DIR__ . '/../docs/uploads';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            throw new Exception('Failed to create uploads directory');
        }
    }

    $profile_photo = null;
    if (isset($_FILES['profile_photo'])) {
        if ($_FILES['profile_photo']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Upload error: ' . $_FILES['profile_photo']['error']);
        }

        $tempFile = $_FILES['profile_photo']['tmp_name'];
        if (!is_uploaded_file($tempFile)) {
            throw new Exception('Invalid upload attempt');
        }

        // Get file extension
        $fileInfo = pathinfo($_FILES['profile_photo']['name']);
        $extension = strtolower($fileInfo['extension']);
        
        // Validate extension
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($extension, $allowedExtensions)) {
            throw new Exception('Invalid file type. Allowed: ' . implode(', ', $allowedExtensions));
        }

        // Generate unique filename
        $filename = 'profile_' . $_SESSION['username'] . '_' . time() . '.' . $extension;
        $targetPath = $uploadDir . '/' . $filename;
        
        // Move uploaded file
        if (!move_uploaded_file($tempFile, $targetPath)) {
            throw new Exception('Failed to save uploaded file');
        }

        $profile_photo = 'uploads/' . $filename;
    }
    
    // Update database
    
    if ($profile_photo) {
        if(!User::update_profile_photo($_SESSION['username'], $profile_photo) || !User::update_user($_SESSION['username'],$_POST['name'] ?? '', $_POST['bio'] ?? '', $_POST['skills'] ?? '')){
            throw new Exception('Database update failed'); 
        }
    }

    else {
        if(!User::update_user($_SESSION['username'],$_POST['name'] ?? '', $_POST['bio'] ?? '', $_POST['skills'] ?? '')){
            throw new Exception('Database update failed');
        }
    }

    echo json_encode([
        'success' => true,
        'profile_photo' => $profile_photo
    ]);

} catch (Exception $e) {
    error_log('Profile update error: ' . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'error' => $e->getMessage()
    ]);
}
?>