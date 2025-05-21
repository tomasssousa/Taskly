<?php
declare(strict_types=1);        # Se Alguém mudar os ficheiros de sitio, que tenha atenção a atualizar o path em baixo
require_once(__DIR__ . '/../database/connection.php');  # <--

class Service {
    public int $id;
    public int $pricing;
    public string $delivery_time;
    public string $description;
    public int $rating;
    public string $user;
    public string $title;

    public function __construct(int $id, int $pricing, string $delivery_time, string $description, int $rating, string $user, string $title){
        $this->id = $id;
        $this->pricing = $pricing;
        $this->delivery_time = $delivery_time;
        $this->description = $description;
        $this->rating = $rating;
        $this->user = $user;
        $this->title = $title;
    }

    public static function create(int $pricing, string $delivery_time, string $description, int $rating, string $user, string $title){
        $db = Database::getInstance();
        $stmt = $db->prepare('INSERT INTO services (pricing, delivery_time, description, rating, user, title) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$pricing, $delivery_time, $description, $rating, $user, $title]);
    }

    public static function get_service(int $id): Service {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM services WHERE id = ?');
        $stmt->execute([$id]);
        $service = $stmt->fetch();
        return new Service($service['id'], $service['pricing'], $service['delivery_time'], $service['description'], $service['rating'], $service['user'], $service['title']);
    }

    #get the services that belong to the desired category. Singular category search.
    public static function get_service_by_category(string $category, int $count): array {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT services.* FROM cate_serv JOIN services ON cate_serv.category=services.id AND cate_serv.category  LIKE ? LIMIT ?');
        $stmt->execute(["$category%", $count]);

        $services = [];

        while ($service = $stmt->fetch()) {
            $services[] = new Service($service['id'], $service['pricing'], $service['delivery_time'], $service['description'], $service['rating'], $service['user'], $service['title']);
        }
        return $services;
    }

    #get the services that belong to a desired delivery time. Singular delivery time search.
    public static function get_service_by_delivery_time(string $delivery_time, int $count): array {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM services WHERE delivery_time LIKE ? LIMIT ?');
        $stmt->execute(["$delivery_time%", $count]);

        $services = [];

        while ($service = $stmt->fetch()) {
            $services[] = new Service($service['id'], $service['pricing'], $service['delivery_time'], $service['description'], $service['rating'], $service['user'], $service['title']);
        }
        return $services;
    }

    #obtain the services that contain a certain word($description) in their description.
    #The input is not separated in any way and, as such, it does not work well with more than a word, with no spaces before or after, and without caligraphy errors (asssuming the service description is well written)
    public static function get_service_by_description(string $description, int $count): array {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM  services WHERE description LIKE ? LIMIT ?');
        $stmt->execute(["%$description%", $count]);

        $services = [];

        while ($service = $stmt->fetch()) {
            $services[] = new Service($service['id'], $service['pricing'], $service['delivery_time'], $service['description'], $service['rating'], $service['user'], $service['title']);
        }
        return $services;
    }

    #obtain the services that contain a certain word($title) in their title.
    #The input is not separated in any way and, as such, it does not work well with more than a word, with no spaces before or after, and without caligraphy errors (asssuming the service title is well written)
    public static function get_service_by_title(string $title, int $count): array {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM  services WHERE title LIKE ? LIMIT ?');
        $stmt->execute(["%$title%", $count]);

        $services = [];

        while ($service = $stmt->fetch()) {
            $services[] = new Service($service['id'], $service['pricing'], $service['delivery_time'], $service['description'], $service['rating'], $service['user'], $service['title']);
        }
        return $services;
    }

    public static function update_service(int $id, int $pricing, string $delivery_time, string $description, string $user){
        $db = Database::getInstance();
        $stmt = $db->prepare('UPDATE services SET pricing = ?, delivery_time = ?, description = ? WHERE id = ? AND user = ?');
        $stmt->execute([$pricing],[$delivery_time],[$description],[$id],[$user]);
    }

    public static function add_as_favourite(string $username, int $id){
        $db = Database::getInstance();
        $stmt = $db->prepare('INSERT INTO favourite_services (username, id) VALUES (?, ?)');
        $stmt->execute([$username, $id]);
    }

    public static function remove_from_favourite(string $username, int $id){
        $db = Database::getInstance();
        $stmt = $db->prepare('DELETE FROM favourite_services WHERE username = ? AND id = ?');
        $stmt->execute([$username, $id]);
    }
    
    #obtain all of a users favourited services
    public static function get_favourites(string $username, int $count): array {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT services.* FROM  services JOIN favourite_services ON favourite_services.id = services.id WHERE favourite_services.username = ? LIMIT ?');
        $stmt->execute([$username, $count]);

        $services = [];

        while ($service = $stmt->fetch()) {
            $services[] = new Service($service['id'], $service['pricing'], $service['delivery_time'], $service['description'], $service['rating'], $service['user'], $service['title']);
        }
        return $services;
    }

    #get the services that belong to the desired category, from the favourited services. Singular category search.
    public static function get_favourite_service_by_category(string $username, string $category, int $count): array {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT services.* FROM cate_serv JOIN services ON cate_serv.category=services.id JOIN favourite_services ON favourite_services.id = services.id WHERE favourite_services.username = ? AND cate_serv.category  LIKE ? LIMIT ?');
        $stmt->execute([$username,"$category%", $count]);

        $services = [];

        while ($service = $stmt->fetch()) {
            $services[] = new Service($service['id'], $service['pricing'], $service['delivery_time'], $service['description'], $service['rating'], $service['user'], $service['title']);
        }
        return $services;
    }

    #get the services that belong to a desired delivery time, from the favourited services. Singular delivery time search.
    public static function get_favourite_service_by_delivery_time(string $username, string $delivery_time, int $count): array {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT services.* FROM services JOIN favourite_services ON favourite_services.id = services.id WHERE favourite_services.username = ? AND delivery_time LIKE ? LIMIT ?');
        $stmt->execute([$username, "$delivery_time%", $count]);

        $services = [];

        while ($service = $stmt->fetch()) {
            $services[] = new Service($service['id'], $service['pricing'], $service['delivery_time'], $service['description'], $service['rating'], $service['user'], $service['title']);
        }
        return $services;
    }

    #obtain the services that contain a certain word($description) in their description, from the favourited services.
    #The input is not separated in any way and, as such, it does not work well with more than a word, with no spaces before or after, and without caligraphy errors (asssuming the service description is well written)
    public static function get_favourite_service_by_description(string $username, string $description, int $count): array {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT services.* FROM services JOIN favourite_services ON favourite_services.id = services.id WHERE favourite_services.username = ? AND description LIKE ? LIMIT ?');
        $stmt->execute([$username, "%$description%", $count]);

        $services = [];

        while ($service = $stmt->fetch()) {
            $services[] = new Service($service['id'], $service['pricing'], $service['delivery_time'], $service['description'], $service['rating'], $service['user'], $service['title']);
        }
        return $services;
    }

    #obtain the services that contain a certain word($title) in their title, from the favourited services.
    #The input is not separated in any way and, as such, it does not work well with more than a word, with no spaces before or after, and without caligraphy errors (asssuming the service title is well written)
    public static function get_favourite_service_by_title(string $username, string $title, int $count): array {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT services.* FROM  services JOIN favourite_services ON favourite_services.id = services.id WHERE favourite_services.username = ? AND title LIKE ? LIMIT ?');
        $stmt->execute([$username, "%$title%", $count]);

        $services = [];

        while ($service = $stmt->fetch()) {
            $services[] = new Service($service['id'], $service['pricing'], $service['delivery_time'], $service['description'], $service['rating'], $service['user'], $service['title']);
        }
        return $services;
    }
}
?>