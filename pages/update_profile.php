<?php
session_start();  // This is enough
include_once('database/connection.php');

// Handle the profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $name = $_POST['name'] ?? '';
    $bio = $_POST['bio'] ?? '';
    $skills = $_POST['skills'] ?? '';

    // Handle profile photo upload
    $profilePhotoPath = null;
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../docs/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Generate unique filename
        $ext = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
        $filename = 'profile_' . $username . '_' . time() . '.' . $ext;
        $uploadFile = $uploadDir . $filename;
        
        if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $uploadFile)) {
            $profilePhotoPath = $filename;
        }
    }

    // Update database
    try {
        if ($profilePhotoPath) {
            $sql = "UPDATE users SET name = ?, bio = ?, skills = ?, profile_photo = ? WHERE username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sssss', $name, $bio, $skills, $profilePhotoPath, $username);
        } else {
            $sql = "UPDATE users SET name = ?, bio = ?, skills = ? WHERE username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssss', $name, $bio, $skills, $username);
        }

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $stmt->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
?>
