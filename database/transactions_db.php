<?php
declare(strict_types=1);        # Se Alguém mudar os ficheiros de sitio, que tenha atenção a atualizar o path em baixo
require_once(__DIR__ . '/../database/connection.php');  # <--

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

    public static function get_transaction_by_id($id): Transaction {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM transactions WHERE id = ?');
        $stmt->execute([$id]);
        $transaction = $stmt->fetch();
        return new Transaction($transaction['id'], $transaction['user'], $transaction['service_id'], $transaction['status'], $transaction['rating']);
    }

    #used to get all (or limitited by count) transactions between the user and the service
    public static function get_transaction($user, $service_id, $count): array {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM transactions WHERE user = ? AND service_id = ? LIMIT ?');
        $stmt->execute([$user, $service_id, $count]);

        $transactions = [];

        while ($transaction = $stmt->fetch()) {
            $transactions[] = new Transaction($transaction['id'], $transaction['user'], $transaction['service_id'], $transaction['status'], $transaction['rating']);
        }
        return $transactions;
    }

    #used to get the transactions between the user and service that are in a certain status (primarily imagined for getting pending transacions)
    public static function get_pending_transaction($user, $service_id, $status, $count): array {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM transactions WHERE user = ? AND service_id = ? AND status = ? LIMIT ?');
        $stmt->execute([$user, $service_id, $status, $count]);

        $transactions = [];

        while ($transaction = $stmt->fetch()) {
            $transactions[] = new Transaction($transaction['id'], $transaction['user'], $transaction['service_id'], $transaction['status'], $transaction['rating']);
        }
        return $transactions;
    }

    public static function update_rating($id, $user, $rating){
        $db = Database::getInstance();
        $stmt = $db->prepare('UPDATE transactions SET rating = ? WHERE id = ? AND user = ?');
        $stmt->execute([$rating, $id, $user]);
    }
}
?>