<?php
session_start();
require_once(__DIR__ . '/../database/chat_db.php');
require_once(__DIR__ . '/../database/user_db.php');

// Check if any user is logged in
if (!isset($_SESSION['username'])) {
    $_SESSION['error'] = 'Not authenticated';
    header('Location: ../pages/login.php');
    exit();
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Handle different actions
switch ($action) {
    case 'send_message':
        handleSendMessage();
        break;
        
    case 'get_messages':
        handleGetMessages();
        break;
        
    case 'get_conversations':
        handleGetConversations();
        break;
        
    case 'search_users':
        handleSearchUsers();
        break;
        
    default:
        $_SESSION['error'] = 'Invalid action';
        header('Location: ../pages/message_hub.php');
        exit();
}

function handleSendMessage() {
    try {
        $receiver = $_POST['receiver'] ?? '';
        $message = trim($_POST['message'] ?? '');
        
        if (empty($receiver) || empty($message)) {
            throw new Exception('Receiver and message are required');
        }

        if (!User::get_user($receiver)) {
            throw new Exception('User not found');
        }

        // Check if user can start conversations
        if (!Chat::can_start_conversation($_SESSION['username'])) {
            // Check if conversation exists where this user can participate
            $existing_chat_id = Chat::conversation_exists($receiver, $_SESSION['username']);
            
            if ($existing_chat_id) {
                // User can reply to existing conversation where they are first_receiver
                Chat::add_message($existing_chat_id, $message, $_SESSION['username'], $receiver);
            } else {
                throw new Exception('You cannot start new conversations. Only users with task_mode = 0 can initiate conversations.');
            }
        } else {
            // User can start conversations, send message normally
            Chat::send_message($message, $_SESSION['username'], $receiver);
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit();
        
    } catch (Exception $e) {
        handleError($e);
    }
}

function handleGetMessages() {
    try {
        header('Content-Type: application/json');
        
        $other_user = $_GET['user'] ?? '';
        $chat_id = $_GET['chat_id'] ?? '';
        
        if (empty($other_user)) {
            throw new Exception('User parameter required');
        }

        if (!empty($chat_id)) {
            $messages = Chat::get_conversation_messages($chat_id);
        } else {
            $messages = Chat::get_messages_between_users($_SESSION['username'], $other_user);

            if (empty($messages)) {
                $messages = Chat::get_messages_between_users($other_user, $_SESSION['username']);
            }
        }
        
        $messageArray = [];
        foreach ($messages as $msg) {
            $messageArray[] = [
                'chat_id' => $msg->chat_id,
                'message_id' => $msg->message_id,
                'text' => $msg->text,
                'sender' => $msg->sender,
                'receiver' => $msg->receiver
            ];
        }
        
        echo json_encode([
            'success' => true, 
            'messages' => $messageArray,
            'can_start_conversation' => Chat::can_start_conversation($_SESSION['username'])
        ]);
        exit();
        
    } catch (Exception $e) {
        handleError($e);
    }
}

function handleGetConversations() {
    try {
        header('Content-Type: application/json');
        
        $conversations = Chat::get_user_conversations($_SESSION['username']);
        
        $conversationDetails = [];
        foreach ($conversations as $conv) {
            $userDetails = User::get_user($conv['other_user']);
            
            if ($userDetails) {
                $conversationDetails[] = [
                    'chat_id' => $conv['chat_id'],
                    'username' => $conv['other_user'],
                    'name' => $userDetails['name'] ?? $conv['other_user'],
                    'profile_photo' => $userDetails['profile_photo'] ?? 'user_icon.png',
                    'last_message_id' => $conv['last_message_id']
                ];
            }
        }
        
        echo json_encode(['success' => true, 'conversations' => $conversationDetails]);
        exit();
        
    } catch (Exception $e) {
        handleError($e);
    }
}

function handleSearchUsers() {
    try {
        header('Content-Type: application/json');
        
        $search = $_GET['search'] ?? '';
        
        if (strlen($search) < 2) {
            echo json_encode(['success' => true, 'users' => []]);
            exit();
        }
        
        // Only allow task_mode = 0 users to start new conversations
        if (!Chat::can_start_conversation($_SESSION['username'])) {
            echo json_encode(['success' => true, 'users' => [], 'message' => 'Only task providers can start new conversations']);
            exit();
        }
        
        $users = User::search_users($search);
        
        echo json_encode(['success' => true, 'users' => $users]);
        exit();
        
    } catch (Exception $e) {
        handleError($e);
    }
}

function handleError($e) {
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