<?php
session_start();

require_once(__DIR__ . '/../database/connection.php');
require_once(__DIR__ . '/../database/service_db.php');
require_once(__DIR__ . '/../database/user_db.php');
require_once(__DIR__ . '/../database/transactions_db.php');
require_once(__DIR__ . '/../database/comments_db.php');

header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['taskId']) || !isset($input['rating']) || !isset($input['reviewText'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit();
}

$taskId = $input['taskId'];
$rating = intval($input['rating']);
$reviewText = trim($input['reviewText']);
$username = $_SESSION['username'];

if ($rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'error' => 'Invalid rating']);
    exit();
}

if (empty($reviewText)) {
    echo json_encode(['success' => false, 'error' => 'Review text cannot be empty']);
    exit();
}

try {
    
    
    if (Comment::has_user_reviewed_service($taskId, $username)) {
        echo json_encode(['success' => false, 'error' => 'You have already reviewed this service']);
        exit();
    }
    
    Comment::create_comment($reviewText, $taskId, $username);
    
    $transaction = $completedTransactions[0];
    Transaction::update_rating($transaction->id, $username, $rating);
    
    echo json_encode(['success' => true, 'message' => 'Review added successfully']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>