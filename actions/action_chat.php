<?php
session_start();
require_once(__DIR__ . '/../database/connection.php');

// Check if this is an AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if (!isset($_SESSION['username'])) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Not authenticated']);
        exit();
    }
    $_SESSION['error'] = 'Not authenticated';
    header('Location: ../pages/login.php');
    exit();
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    $db = getDatabaseConnection();
    
    switch ($action) {
        case 'send_message':
            $receiver = $_POST['receiver'] ?? '';
            $message = trim($_POST['message'] ?? '');
            
            if (empty($receiver) || empty($message)) {
                throw new Exception('Receiver and message are required');
            }
            
            // Verify if the selected user exists
            $stmt = $db->prepare('SELECT username FROM users WHERE username = ?');
            $stmt->execute([$receiver]);
            if (!$stmt->fetch()) {
                throw new Exception('User not found');
            }
            
            // Write the message to the database
            $stmt = $db->prepare('INSERT INTO chat (text, sender, receiver) VALUES (?, ?, ?)');
            $stmt->execute([$message, $_SESSION['username'], $receiver]);

            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit();
            
        case 'get_messages':
            $other_user = $_GET['user'] ?? '';
            
            if (empty($other_user)) {
                throw new Exception('User parameter required');
            }
            
            $stmt = $db->prepare('
                SELECT * FROM chat 
                WHERE (sender = ? AND receiver = ?) OR (sender = ? AND receiver = ?) 
                ORDER BY id ASC
            ');
            $stmt->execute([$_SESSION['username'], $other_user, $other_user, $_SESSION['username']]);
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'messages' => $messages]);
            exit();
            
        case 'get_conversations':
            $stmt = $db->prepare('
                SELECT DISTINCT 
                    CASE 
                        WHEN sender = ? THEN receiver 
                        ELSE sender 
                    END as other_user,
                    MAX(id) as last_message_id
                FROM chat 
                WHERE sender = ? OR receiver = ?
                GROUP BY other_user
                ORDER BY last_message_id DESC
            ');
            $stmt->execute([$_SESSION['username'], $_SESSION['username'], $_SESSION['username']]);
            $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $conversationDetails = [];
            foreach ($conversations as $conv) {
                $stmt = $db->prepare('SELECT name, profile_photo FROM users WHERE username = ?');
                $stmt->execute([$conv['other_user']]);
                $userDetails = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $conversationDetails[] = [
                    'username' => $conv['other_user'],
                    'name' => $userDetails['name'] ?? $conv['other_user'],
                    'profile_photo' => $userDetails['profile_photo'] ?? 'user_icon.png'
                ];
            }
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'conversations' => $conversationDetails]);
            exit();
            
        case 'search_users':
            $search = trim($_GET['search'] ?? '');
            
            if (strlen($search) < 2) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'users' => []]);
                exit();
            }
            
            $searchTerm = "%$search%";
            $stmt = $db->prepare('
                SELECT username, name, profile_photo 
                FROM users 
                WHERE username != ? AND (username LIKE ? OR name LIKE ?)
                ORDER BY username
                LIMIT 10
            ');
            $stmt->execute([$_SESSION['username'], $searchTerm, $searchTerm]);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'users' => $users]);
            exit();
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit();
    }
    
    $_SESSION['error'] = $e->getMessage();
    header('Location: ../pages/message_hub.php');
    exit();
}
?>