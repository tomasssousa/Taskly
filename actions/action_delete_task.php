<?php
session_start();
require_once(__DIR__ . '/../database/connection.php');

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
    $db = getDatabaseConnection();
    
    // Verify ownership
    $stmt = $db->prepare('SELECT user FROM services WHERE id = ?');
    $stmt->execute([$taskId]);
    $task = $stmt->fetch();
    
    if (!$task || $task['user'] !== $_SESSION['username']) {
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit();
    }
    
    // Delete associated media first
    $stmt = $db->prepare('DELETE FROM media WHERE service_id = ?');
    $stmt->execute([$taskId]);
    
    // Delete task
    $stmt = $db->prepare('DELETE FROM services WHERE id = ?');
    $stmt->execute([$taskId]);
    
    echo json_encode(['success' => true]);
    
} catch (PDOException $e) {
    error_log("Error deleting task: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}