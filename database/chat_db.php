<?php
declare(strict_types=1);        # Se Alguém mudar os ficheiros de sitio, que tenha atenção a atualizar o path em baixo
require_once(__DIR__ . '/../database/connection.php');  # <--

class Chat {
    public int $id;
    public string $text;
    public int $chat_id;
    public string $sender;
    public string $receiver;
    

    public function __construct(int $id, string $text, int $chat_id, string $sender, int $receiver){
        $this->id = $id;
        $this->text = $text;
        $this->chat_id = $chat_id;
        $this->sender = $sender;
        $this->receiver = $receiver;
    }

    #create a chat message
    public static function create_chat($text, $sender, $receiver){
        $db = Database::getInstance();
        $stmt = $db->prepare('INSERT INTO chat (text, sender, receiver) VALUES (?, ?, ?)');
        $stmt->execute([$text, $sender, $receiver]);
    }

    public static function create_reply($text, $chat_id, $sender, $receiver){
        $db = Database::getInstance();
        $stmt = $db->prepare('INSERT INTO chat (text, chat_id, sender, receiver) VALUES (?, ?, ?, ?)');
        $stmt->execute([$text, $chat_id, $sender, $receiver]); 
    }

    public static function get_chat($sender, $receiver): array {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM chat WHERE (sender = ? AND receiver = ?) OR (sender = ? AND receiver = ?) ORDER BY id');
        $stmt->execute([$sender, $receiver, $receiver, $sender]);

        $chats = [];

        while ($chat = $stmt->fetch()) {
            $chats[] = new Chat($chat['id'], $chat['text'], $chat['chat_id'], $chat['sender'], $chat['receiver']);
        }
        return $chats;
    }
}
?>