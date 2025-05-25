<?php
declare(strict_types=1);        # Se Alguém mudar os ficheiros de sitio, que tenha atenção a atualizar o path em baixo
require_once(__DIR__ . '/../database/connection.php');  # <--

class Comment {
    public int $id;
    public string $text;
    public int $service_id;
    public string $user;
    public int $reply_to;

    public function __construct(int $id, string $comment, int $service_id, string $user, int $reply_to){
        $this->id = $id;
        $this->text = $comment;
        $this->service_id = $service_id;
        $this->user = $user;
        $this->reply_to = $reply_to;
    }

    #create a comment to a service
    public static function create_comment($comment, $service_id, $user){
        $db = Database::getInstance();
        $stmt = $db->prepare('INSERT INTO comments (text, service_id, user) VALUES (?, ?, ?)');
        $stmt->execute([$comment, $service_id, $user]);
    }

    #create a reply to a comment
    public static function create_reply($comment, $user, $reply_to){
        $db = Database::getInstance();
        $stmt = $db->prepare('INSERT INTO comments (text, user, reply_to) VALUES (?, ?, ?)');
        $stmt->execute([$comment, $user, $reply_to]); 
    }

    public static function get_comment_by_id($id): Comment{
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM comments WHERE id = ?');
        $stmt->execute([$id]);
        $comment = $stmt->fetch();
        return new Comment($comment['id'], $comment['text'], $comment['service_id'], $comment['user'], $comment['reply_to']);
    }

    #get the comments to a service
    public static function get_comments($service_id, $count): array {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM comments WHERE service_id = ? LIMIT ?');
        $stmt->execute([$service_id, $count]);

        $comments = [];

        while ($comment = $stmt->fetch()) {
            $comments[] = new Comment($comment['id'], $comment['text'], $comment['service_id'], $comment['user'], $comment['reply_to']);
        }
        return $comments;
    }

    #get the comments that are a reply to a certain comment
    public static function get_replies($id, $count): array {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM comments WHERE reply_to = ? LIMIT ?');
        $stmt->execute([$id, $count]);

        $comments = [];

        while ($comment = $stmt->fetch()) {
            $comments[] = new Comment($comment['id'], $comment['text'], $comment['service_id'], $comment['user'], $comment['reply_to']);
        }
        return $comments;
    }

    #get all the users made comments and replies
    public static function get_comments_by_user($user, $count): array { #lembrete que algumas destas funções podem não vir a ser usadas, uma vez que determinadas coisas podem não ser desejáveis de implementar.
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM comments WHERE user = ? LIMIT ?');
        $stmt->execute([$user, $count]);

        $comments = [];

        while ($comment = $stmt->fetch()) {
            $comments[] = new Comment($comment['id'], $comment['text'], $comment['service_id'], $comment['user'], $comment['reply_to']);
        }
        return $comments;
    }

    public static function get_user_comments_for_service($user, $service_id): array {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM comments WHERE user = ? AND service_id = ?');
        $stmt->execute([$user, $service_id]);
        
        $comments = [];
        while ($comment = $stmt->fetch()) {
            $comments[] = new Comment($comment['id'], $comment['text'], $comment['service_id'], $comment['user'], $comment['reply_to']);
        }
        return $comments;
    }

    #specific function used in action_add_review.php
    public static function has_user_reviewed_service($service_id, $user): bool {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT COUNT(*) FROM comments WHERE service_id = ? AND user = ?');
        $stmt->execute([$service_id, $user]);
        return $stmt->fetchColumn() > 0;
    }
}
?>