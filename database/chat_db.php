<?php
declare(strict_types=1);
require_once(__DIR__ . '/../database/connection.php');

class Chat {
    public int $chat_id;
    public int $message_id;
    public string $text;
    public string $sender;
    public string $receiver;
    public string $first_sender;
    public string $first_receiver;
    

    public function __construct(int $chat_id, int $message_id, string $text, string $sender, string $receiver, string $first_sender, string $first_receiver){
        $this->chat_id = $chat_id;
        $this->message_id = $message_id;
        $this->text = $text;
        $this->sender = $sender;
        $this->receiver = $receiver;
        $this->first_sender = $first_sender;
        $this->first_receiver = $first_receiver;
    }

    // Create a new conversation and first message
    public static function create_conversation($text, $first_sender, $first_receiver): int {
        $db = Database::getInstance();

        $stmt = $db->prepare('SELECT COALESCE(MAX(chat_id), 0) + 1 as next_chat_id FROM chat');
        $stmt->execute();
        $result = $stmt->fetch();
        $chat_id = $result['next_chat_id'];
        
        // Insert first message with message_id = 1
        $stmt = $db->prepare('INSERT INTO chat (chat_id, message_id, text, sender, receiver, first_sender, first_receiver) VALUES (?, 1, ?, ?, ?, ?, ?)');
        $stmt->execute([$chat_id, $text, $first_sender, $first_receiver, $first_sender, $first_receiver]);
        
        return $chat_id;
    }

    // Add message to existing conversation
    public static function add_message($chat_id, $text, $sender, $receiver) {
        $db = Database::getInstance();
        
        // Get the conversation details to verify it exists and get first_sender and first_receiver
        $stmt = $db->prepare('SELECT first_sender, first_receiver, MAX(message_id) as last_message_id FROM chat WHERE chat_id = ? GROUP BY first_sender, first_receiver');
        $stmt->execute([$chat_id]);
        $conversation = $stmt->fetch();
        
        if (!$conversation) {
            throw new Exception('Conversation not found');
        }
        
        $next_message_id = $conversation['last_message_id'] + 1;
        
        // Insert new message with incremented message_id
        $stmt = $db->prepare('INSERT INTO chat (chat_id, message_id, text, sender, receiver, first_sender, first_receiver) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$chat_id, $next_message_id, $text, $sender, $receiver, $conversation['first_sender'], $conversation['first_receiver']]);
    }

    // Send message (create new conversation or add to an existing one)
    public static function send_message($text, $sender, $receiver) {
        $db = Database::getInstance();
        
        // Check if conversation already exists where sender is first_sender
        $stmt = $db->prepare('SELECT chat_id FROM chat WHERE first_sender = ? AND first_receiver = ? LIMIT 1');
        $stmt->execute([$sender, $receiver]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // Add to existing conversation
            self::add_message($existing['chat_id'], $text, $sender, $receiver);
            return $existing['chat_id'];
        } else {
            // Create new conversation
            return self::create_conversation($text, $sender, $receiver);
        }
    }

    // Check if user can start conversations (only task_mode = 0)
    public static function can_start_conversation($username): bool {
        $db = Database::getInstance();
        
        $stmt = $db->prepare('SELECT task_mode FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        return $user && $user['task_mode'] == 0;
    }

    // Get messages for a specific conversation
    public static function get_conversation_messages($chat_id): array {
        $db = Database::getInstance();
        
        $stmt = $db->prepare('SELECT * FROM chat WHERE chat_id = ? ORDER BY message_id ASC');
        $stmt->execute([$chat_id]);

        $messages = [];
        while ($chat = $stmt->fetch()) {
            $messages[] = new Chat(
                $chat['chat_id'],
                $chat['message_id'],
                $chat['text'],
                $chat['sender'],
                $chat['receiver'],
                $chat['first_sender'],
                $chat['first_receiver']
            );
        }
        return $messages;
    }

    // Get messages between two specific users
    public static function get_messages_between_users($user1, $user2): array {
        $db = Database::getInstance();
        
        $stmt = $db->prepare('SELECT * FROM chat WHERE first_sender = ? AND first_receiver = ? ORDER BY chat_id ASC, message_id ASC');
        $stmt->execute([$user1, $user2]);

        $messages = [];
        while ($chat = $stmt->fetch()) {
            $messages[] = new Chat(
                $chat['chat_id'],
                $chat['message_id'],
                $chat['text'],
                $chat['sender'],
                $chat['receiver'],
                $chat['first_sender'],
                $chat['first_receiver']
            );
        }
        return $messages;
    }

    // Get conversations for user based on task_mode
    public static function get_user_conversations($username): array {
        $db = Database::getInstance();
        
        // Get user's task_mode
        $userStmt = $db->prepare('SELECT task_mode FROM users WHERE username = ?');
        $userStmt->execute([$username]);
        $user = $userStmt->fetch();
        
        if (!$user) {
            return [];
        }
        
        if ($user['task_mode'] == 0) {
            // Show conversations where user is first_sender
            $stmt = $db->prepare('
                SELECT DISTINCT 
                    chat_id,
                    first_receiver as other_user,
                    MAX(message_id) as last_message_id
                FROM chat 
                WHERE first_sender = ?
                GROUP BY chat_id, first_receiver
                ORDER BY chat_id DESC, last_message_id DESC
            ');
            $stmt->execute([$username]);
        } else {
            // Show conversations where user is first_receiver
            $stmt = $db->prepare('
                SELECT DISTINCT 
                    chat_id,
                    first_sender as other_user,
                    MAX(message_id) as last_message_id
                FROM chat 
                WHERE first_receiver = ?
                GROUP BY chat_id, first_sender
                ORDER BY chat_id DESC, last_message_id DESC
            ');
            $stmt->execute([$username]);
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Check if a conversation between two users already exists
    public static function conversation_exists($user1, $user2): ?int {
        $db = Database::getInstance();
        
        $stmt = $db->prepare('SELECT chat_id FROM chat WHERE first_sender = ? AND first_receiver = ? LIMIT 1');
        $stmt->execute([$user1, $user2]);
        $result = $stmt->fetch();
        
        return $result ? $result['chat_id'] : null;
    }
}
?>