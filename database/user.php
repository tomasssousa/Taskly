<?php
declare(strict_types=1);        # Se Alguém mudar os ficheiros de sitio, que tenha atenção a atualizar o path em baixo
require_once(__DIR__ . '/../database/database.php');  # <--

class User {
    public string $username;
    public string $name;
    public string $email;
    public bool $admin;

    public function __construct(string $username, string $name, string $email, bool $admin) {
        $this->username = $username;
        $this->name = $name;
        $this->email = $email;
        $this->admin = $admin;
    }

    public static function create($username, $name, $password, $email, $admin) {
        $db = Database::getInstance();
        $stmt = $db->prepare('INSERT INTO users (username, name, password, email, admin) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$username, $name, $sha1($password), $email, $admin]);
    }

    public static function get_user($username, $password) {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM users WHERE username = ? AND password = ?');
        $stmt->execute([$username, sha1($password)]);

        return $stmt->fetch();
    }

    public static function promote_to_admin($username) {
        $db = Database::getInstance();
        $stmt = $db->prepare('UPDATE users SET admin = TRUE WHERE username = ?');
        $stmt->execute([$username]);
    }
}