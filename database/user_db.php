<?php
declare(strict_types=1);        # Se Alguém mudar os ficheiros de sitio, que tenha atenção a atualizar o path em baixo
require_once(__DIR__ . '/../database/connection.php');  # <--

class User {
    public string $username;
    public string $name;
    public string $email;
    public bool $admin_power;
    public bool $task_mode;
    public string $bio;
    public string $profile_photo;
    public string $skills;
    public string $money;

    public function __construct(string $username, string $name, string $email, bool $admin, bool $task_mode , string $bio, string $profile_photo, string $skills, string $money) {
        $this->username = $username;
        $this->name = $name;
        $this->email = $email;
        $this->admin_power = $admin;
        $this->task_mode = $task_mode;
        $this->bio = $bio;
        $this->profile_photo = $profile_photo;
        $this->skills = $skills;
        $this->money = $money;
    }

    public static function create($username, $name, $password, $email) {
        $db = Database::getInstance();
        $stmt = $db->prepare('INSERT INTO users (username, name, password, email, admin_power, task_mode, money) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$username, $name, $password, $email, FALSE, FALSE, '0 euros']);
    }

    #it does not return a new User beacuse it requires further investigation on how it will be used
    public static function get_user($username) {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);

        return $stmt->fetch();
    }

    public static function update_user($username, $name, $bio, $skills) {
        $db = Database::getInstance();
        $stmt = $db->prepare('UPDATE users SET name = ?, bio = ?, skills = ? WHERE username = ?');
        return $stmt->execute([$name, $bio, $skills, $username]);
    }

    public static function update_profile_photo($username, $profile_photo) {
        $db = Database::getInstance();
        $stmt = $db->prepare('UPDATE users SET profile_photo = ? WHERE username = ?');
        return $stmt->execute([$profile_photo, $username]);
    }

    public static function update_bio($username,$bio){
        $db = Database::getInstance();
        $stmt = $db->prepare('UPDATE users SET bio = ? WHERE username = ?');
        $stmt->execute([$bio,$username]);
    }

    public static function update_skills($username,$skills){
        $db = Database::getInstance();
        $stmt = $db->prepare('UPDATE users SET skills = ? WHERE username = ?');
        $stmt->execute([$skills,$username]);
    }

    public static function promote_to_admin($username) {
        $db = Database::getInstance();
        $stmt = $db->prepare('UPDATE users SET admin_power = TRUE WHERE username = ?');
        $stmt->execute([$username]);
    }

    public static function task_mode_on($username) {
        $db = Database::getInstance();
        $stmt = $db->prepare('UPDATE users SET task_mode = TRUE WHERE username = ?');
        $stmt->execute([$username]);
    }

    public static function task_mode_off($username) {
        $db = Database::getInstance();
        $stmt = $db->prepare('UPDATE users SET task_mode = FALSE WHERE username = ?');
        $stmt->execute([$username]);
    }

    public static function delete_user($username) {
        $db = Database::getInstance();
        $stmt = $db->prepare('DELETE FROM users WHERE username = ?');
        $stmt->execute([$username]);
    }

    public static function update_money($username, $money){
        $db = Database::getInstance();
        $stmt = $db->prepare('UPDATE users SET money = ? WHERE username = ?');
        $stmt->execute([$money, $username]); 
    }
}
?>