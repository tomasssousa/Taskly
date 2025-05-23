<?php
session_start();
require_once(__DIR__ . '/../database/service_db.php');

// Set JSON header before any output
header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

$taskId = $_GET['id'] ?? null;
if (!$taskId) {
    echo json_encode(['success' => false, 'error' => 'No task ID provided']);
    exit();
}

try {
    
    // Verify ownership
    $task = Service::get_service($taskid);
    
    if (!$task || $task->user !== $_SESSION['username']) {
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit();
    }
    
    // Delete associated media first by ON DELETE CASCADE
    
    // Delete task
    Service::delete($taskId);
    
    echo json_encode(['success' => true]);
    
} catch (PDOException $e) {
    error_log("Error deleting task: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}