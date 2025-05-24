<?php
session_start();
require_once(__DIR__ . '/../database/connection.php');
require_once(__DIR__ . '/../database/chat_db.php');
require_once(__DIR__ . '/../database/user_db.php');

if (!isset($_SESSION['username'])) {
    $_SESSION['error'] = 'Not authenticated';
    header('Location: ../pages/login.php');
    exit();
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    $db = Database::getInstance();
    
    switch ($action) {
        case 'send_message':
            $receiver = $_POST['receiver'] ?? '';
            $message = trim($_POST['message'] ?? '');
            
            if (empty($receiver) || empty($message)) {
                throw new Exception('Receiver and message are required');
            }
            
            // Verify if the selected user exists using User class
            if (!User::get_user($receiver)) {
                throw new Exception('User not found');
            }
            
            // Use Chat class to create message
            Chat::create_chat($message, $_SESSION['username'], $receiver);

            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit();
            
        case 'get_messages':
            header('Content-Type: application/json');
            
            $other_user = $_GET['user'] ?? '';
            
            if (empty($other_user)) {
                throw new Exception('User parameter required');
            }
            
            // Use Chat class to get messages
            $chatObjects = Chat::get_chat($_SESSION['username'], $other_user);
            
            $messages = [];
            foreach ($chatObjects as $chat) {
                $messages[] = [
                    'id' => $chat->id,
                    'text' => $chat->text,
                    'sender' => $chat->sender,
                    'receiver' => $chat->receiver
                ];
            }
            
            echo json_encode(['success' => true, 'messages' => $messages]);
            exit();
            
        case 'get_conversations':
            header('Content-Type: application/json');
            
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
                $userDetails = User::get_user($conv['other_user']);
                
                $conversationDetails[] = [
                    'username' => $conv['other_user'],
                    'name' => $userDetails['name'] ?? $conv['other_user'],
                    'profile_photo' => $userDetails['profile_photo'] ?? 'user_icon.png'
                ];
            }
            
            echo json_encode(['success' => true, 'conversations' => $conversationDetails]);
            exit();
            
        case 'search_users':
            header('Content-Type: application/json');
            
            $search = $_GET['search'] ?? '';
            
            if (strlen($search) < 2) {
                echo json_encode(['success' => true, 'users' => []]);
                exit();
            }
            
            $stmt = $db->prepare('
                SELECT username, name, profile_photo 
                FROM users 
                WHERE username != ? AND (username LIKE ? OR name LIKE ?)
                LIMIT 10
            ');
            $stmt->execute([$_SESSION['username'], "%$search%", "%$search%"]);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'users' => $users]);
            exit();
            
        default:
            $_SESSION['error'] = 'Invalid action';
            header('Location: ../pages/message_hub.php');
            exit();
    }
    
} catch (Exception $e) {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit();
    }
    
    $_SESSION['error'] = $e->getMessage();
    header('Location: ../pages/message_hub.php');
    exit();
}
?>