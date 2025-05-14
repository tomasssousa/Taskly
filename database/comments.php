<?php
declare(strict_types=1);        # Se Alguém mudar os ficheiros de sitio, que tenha atenção a atualizar o path em baixo
require_once(__DIR__ . '/../database/database.php');  # <--

class Comment {
    public int $id;
    public string $comment;
    public int $service_id;
    public string $user;
    public int $reply_to;

    public function __construct(int $id, string $comment, int $service_id, string $user, int $reply_to){
        $this->id = $id;
        $this->comment = $comment;
        $this->service_id = $service_id;
        $this->user = $user;
        $this->reply_to = $reply_to;
    }

    #create a comment to a service
    public static function create_comment($comment, $service_id, $user){
        $db = Database::getInstance();
        $stmt = $db->prepare('INSERT INTO comments (comment, service_id, user) VALUES (?, ?, ?)');
        $stmt->execute([$comment, $service_id, $user]);
    }

    #create a reply to a comment
    public static function create_reply($comment, $user, $reply_to){
        $db = Database::getInstance();
        $stmt = $db->prepare('INSERT INTO comments (comment, user, reply_to) VALUES (?, ?, ?)');
        $stmt->execute([$comment, $user, $reply_to]); 
    }

    #get the comments to a service
    public static function get_comments($service_id, $count){
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM comments WHERE service_id = ? LIMIT ?');
        $stmt->execute([$service_id, $count]);

        $comments = [];

        while ($comment = $stmt->fetch()) {
            $comments[] = new Comment($comment['id'], $comment['comment'], $comment['service_id'], $comment['user'], $comment['reply_to']);
        }
        return $comments;
    }

    #get the comments that are a reply to a certain comment
    public static function get_replies($id, $count){
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM comments WHERE reply_to = ? LIMIT ?');
        $stmt->execute([$id, $count]);

        $comments = [];

        while ($comment = $stmt->fetch()) {
            $comments[] = new Comment($comment['id'], $comment['comment'], $comment['service_id'], $comment['user'], $comment['reply_to']);
        }
        return $comments;
    }

    #get all the users made comments and replies
    public static function get_comments_by_user($user, $count){ #lembrete que algumas destas funções podem não vir a ser usadas, uma vez que determinadas coisas podem não ser desejáveis de implementar.
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM comments WHERE user = ? LIMIT ?');
        $stmt->execute([$user, $count]);

        $comments = [];

        while ($comment = $stmt->fetch()) {
            $comments[] = new Comment($comment['id'], $comment['comment'], $comment['service_id'], $comment['user'], $comment['reply_to']);
        }
        return $comments;
    }
}
?>