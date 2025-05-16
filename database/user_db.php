<?php
declare(strict_types=1);        # Se Alguém mudar os ficheiros de sitio, que tenha atenção a atualizar o path em baixo
require_once(__DIR__ . '/../database/connection.php');  # <--

class User {
    public string $username;
    public string $name;
    public string $email;
    public bool $admin;
    public string $bio;
    public string $profile_foto;
    public string $skills;

    public function __construct(string $username, string $name, string $email, bool $admin, string $bio, string $profile_foto, string $skills) {
        $this->username = $username;
        $this->name = $name;
        $this->email = $email;
        $this->admin = $admin;
        $this->bio = $bio;
        $this->profile_foto = $profile_foto;
        $this->skills = $skills;
    }

    public static function create($username, $name, $password, $email, $admin, $bio, $profile_foto, $skills) {
        $db = Database::getInstance();
        $stmt = $db->prepare('INSERT INTO users (username, name, password, email, admin, bio, profile_foto, skills) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$username, $name, $sha1($password), $email, $admin, $bio, $profile_foto, $skills]);
    }

    #it does not return a new User beacuse it requires further investigation on how it will be used
    public static function get_user($username) {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);

        return $stmt->fetch();
    }

    public static function promote_to_admin($username) {
        $db = Database::getInstance();
        $stmt = $db->prepare('UPDATE users SET admin = TRUE WHERE username = ?');
        $stmt->execute([$username]);
    }
}
?>