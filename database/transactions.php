<?php
declare(strict_types=1);        # Se Alguém mudar os ficheiros de sitio, que tenha atenção a atualizar o path em baixo
require_once(__DIR__ . '/../database/database.php');  # <--

class Transaction{
    public int $id;
    public string $user;
    public int $service_id;
    public string $status;
    public int $rating;

    public function __construct(int $id, string $user, int $service_id, string $status, int $rating){
        $this->id = $id;
        $this->user = $user;
        $this->service_id = $service_id;
        $this->status = $status;
        $this->rating = $rating;
    }

    public static function create_transaction($user, $service_id, $status, $rating){
        $db = Database::getInstance();
        $stmt = $db->prepare('INSERT INTO transactions (user, service_id, status, rating) VALUES (?, ?, ?, ?)');
        $stmt->execute([$user, $service_id, $status, $rating]);
    }

    public static function get_transaction(){
        
    }

    public static function update_rating($id, $user, $rating){
        $db = Database::getInstance();
        $stmt = $db->prepare('UPDATE transactions SET rating = ? WHERE id = ? AND user = ?');
        $stmt->execute([$rating],[$id],[$user]);
    }
}
?>