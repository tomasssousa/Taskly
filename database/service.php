<?php
declare(strict_types=1);        # Se Alguém mudar os ficheiros de sitio, que tenha atenção a atualizar o path em baixo
require_once(__DIR__ . '/../database/database.php');  # <--

class Service {
    public int $id;
    public int $pricing;
    public string $delivery_time;
    public string $description;
    public int $rating;
    public string $user;

    public function __construct(int $id, int $pricing, string $delivery_time, string $description, int $rating, string $user){
        $this->id = $id;
        $this->pricing = $pricing;
        $this->delivery_time = $delivery_time;
        $this->description = $description;
        $this->rating = $rating;
        $this->user = $user;
    }

    public static function create(int $pricing, string $delivery_time, string $description, int $rating, string $user){
        $db = Database::getInstance();
        $stmt = $db->prepare('INSERT INTO services (pricing, delivery_time, description, rating, user) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$pricing, $delivery_time, $description, $rating, $user]);
    }
    
    public static function get_service(int $id){
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM services WHERE id = ?');
        $stmt->execute([$id]);
    }

    #get the services that belong to the desired category. Singular category search.
    public static function get_service_by_category(int $category, int $count){
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT services.* FROM cate_serv JOIN services ON cate_serv.category=services.id AND cate_serv.category  LIKE ? LIMIT ?');
        $stmt->execute(["$category%", $count]);

        $services = [];

        while ($service = $stmt->fetch()) {
            $services[] = new Service($service['id'], $service['pricing'], $service['delivery_time'], $service['description'], $service['rating'], $service['user']);
        }
        return $services;
    }

    #obtain the services that contain a certain word($description) in their description.
    #The input is not separated in any way and, as such, it does not work well with more a word, with no spaces before or after, and with caligraphy errors (asssuming the service description is well written)
    public static function get_service_by_description(int $description, int $count){
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM  services WHERE description LIKE ? LIMIT ?');
        $stmt->execute(["$description%", $count]);

        $services = [];

        while ($service = $stmt->fetch()) {
            $services[] = new Service($service['id'], $service['pricing'], $service['delivery_time'], $service['description'], $service['rating'], $service['user']);
        }
        return $services;
    }

    public static function update_service(int $id, int $pricing, string $delivery_time, string $description, string $user){
        $db = Database::getInstance();
        $stmt = $db->prepare('UPDATE services SET pricing = ?, delivery_time = ?, description = ? WHERE id = ? AND user = ?');
        $stmt->execute([$pricing],[$delivery_time],[$description],[$id],[$user]);
    }
}
?>