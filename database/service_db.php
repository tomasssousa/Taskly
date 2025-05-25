<?php
declare(strict_types=1);        # Se Alguém mudar os ficheiros de sitio, que tenha atenção a atualizar o path em baixo
require_once(__DIR__ . '/../database/connection.php');  # <--

class Service {
    public int $id;
    public string $taskname;
    public int $pricing;
    public string $delivery_time;
    public string $description;
    public int $rating;
    public int $Nrating;
    public string $category;
    public string $user;
    public string $media;

    public function __construct(int $id, int $pricing, string $delivery_time, string $description, int $rating, int $Nrating, string $user, string $taskname, string $category, string $media){
        $this->id = $id;
        $this->pricing = $pricing;
        $this->delivery_time = $delivery_time;
        $this->description = $description;
        $this->rating = $rating;
        $this->Nrating = $Nrating;
        $this->user = $user;
        $this->taskname = $taskname;
        $this->category = $category;
        $this->media = $media;
    }

    public static function create(int $pricing, string $delivery_time, string $description, string $user, string $title, string $category) {
        $db = Database::getInstance();
        $stmt = $db->prepare('INSERT INTO services (pricing, delivery_time, description, rating, Nrating, user, taskname, category) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$pricing, $delivery_time, $description, 5, 1, $user, $title, $category]);
    }

    public static function delete(int $id) {
        $db = Database::getInstance();
        $stmt = $db->prepare('DELETE FROM services WHERE id = ?');
        $stmt->execute([$id]);
    }

    public static function get_service(int $id): Service {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM services WHERE id = ?');
        $stmt->execute([$id]);
        $service = $stmt->fetch();
        return new Service($service['id'], $service['pricing'], $service['delivery_time'], $service['description'], $service['rating'], $service['Nrating'], $service['user'], $service['taskname'], $service['category'], $service['media'] ?? '');
    }

    public static function set_main_image(int $id, string $image_path) {
        $db = Database::getInstance();
        $stmt = $db->prepare('UPDATE services SET image = ? WHERE id = ?');
        $stmt->execute([$image_path, $id]);
    }

    #get the services that belong to the desired category. Singular category search.
    public static function get_service_by_category(string $category, int $count): array {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM services WHERE category  LIKE ? LIMIT ?');
        $stmt->execute(["$category%", $count]);

        $services = [];

        while ($service = $stmt->fetch()) {
            $services[] = new Service($service['id'], $service['pricing'], $service['delivery_time'], $service['description'], $service['rating'], $service['Nrating'], $service['user'], $service['taskname'], $service['category'], $service['media']);
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
            $services[] = new Service($service['id'], $service['pricing'], $service['delivery_time'], $service['description'], $service['rating'], $service['Nrating'], $service['user'], $service['taskname'], $service['category'], $service['media']);
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
            $services[] = new Service($service['id'], $service['pricing'], $service['delivery_time'], $service['description'], $service['rating'], $service['Nrating'], $service['user'], $service['taskname'], $service['category'], $service['media']);
        }
        return $services;
    }

    #obtain the services that contain a certain word($title) in their title.
    #The input is not separated in any way and, as such, it does not work well with more than a word, with no spaces before or after, and without caligraphy errors (asssuming the service title is well written)
    public static function get_service_by_name(string $title, int $count): array {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM  services WHERE taskname LIKE ? LIMIT ?');
        $stmt->execute(["%$title%", $count]);

        $services = [];

        while ($service = $stmt->fetch()) {
            $services[] = new Service($service['id'], $service['pricing'], $service['delivery_time'], $service['description'], $service['rating'], $service['Nrating'], $service['user'], $service['taskname'], $service['category'], $service['media']);
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
            $services[] = new Service($service['id'], $service['pricing'], $service['delivery_time'], $service['description'], $service['rating'], $service['Nrating'], $service['user'], $service['taskname'], $service['category'], $service['media']);
        }
        return $services;
    }

    #get the services that belong to the desired category, from the favourited services. Singular category search.
    public static function get_favourite_service_by_category(string $username, string $category, int $count): array {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT services.* FROM services JOIN favourite_services ON favourite_services.id = services.id WHERE favourite_services.username = ? AND services.category  LIKE ? LIMIT ?');
        $stmt->execute([$username,"$category%", $count]);

        $services = [];

        while ($service = $stmt->fetch()) {
            $services[] = new Service($service['id'], $service['pricing'], $service['delivery_time'], $service['description'], $service['rating'], $service['Nrating'], $service['user'], $service['taskname'], $service['category'], $service['media']);
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
            $services[] = new Service($service['id'], $service['pricing'], $service['delivery_time'], $service['description'], $service['rating'], $service['Nrating'], $service['user'], $service['taskname'], $service['category'], $service['media']);
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
            $services[] = new Service($service['id'], $service['pricing'], $service['delivery_time'], $service['description'], $service['rating'], $service['Nrating'], $service['user'], $service['taskname'], $service['category'], $service['media']);
        }
        return $services;
    }

    #obtain the services that contain a certain word($title) in their title, from the favourited services.
    #The input is not separated in any way and, as such, it does not work well with more than a word, with no spaces before or after, and without caligraphy errors (asssuming the service title is well written)
    public static function get_favourite_service_by_name(string $username, string $title, int $count): array {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT services.* FROM  services JOIN favourite_services ON favourite_services.id = services.id WHERE favourite_services.username = ? AND taskname LIKE ? LIMIT ?');
        $stmt->execute([$username, "%$title%", $count]);

        $services = [];

        while ($service = $stmt->fetch()) {
            $services[] = new Service($service['id'], $service['pricing'], $service['delivery_time'], $service['description'], $service['rating'], $service['Nrating'], $service['user'], $service['taskname'], $service['category'], $service['media']);
        }
        return $services;
    }

    public static function set_category(int $id, string $category) {
        $db = Database::getInstance();
        $stmt = $db->prepare('UPDATE services SET category = ? WHERE id = ?');
        $stmt->execute([$category, $id]);
    }


    #Specific function for User.php
    public static function get_services_by_user(string $username): array {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT s.*, u.profile_photo as seller_photo, u.name as seller_name,
                               COALESCE(s.taskname, s.description) as task_title,
                               (SELECT COUNT(*) FROM transactions WHERE service_id = s.id) as order_count,
                               (SELECT GROUP_CONCAT(file_path) FROM media WHERE service_id = s.id ORDER BY id ASC) as all_images
                            FROM services s
                            JOIN users u ON s.user = u.username
                            WHERE s.user = ?
                            ORDER BY s.id DESC ");
        $stmt->execute([$username]);
        
        return $stmt->fetchAll();
    }

    #Specific function for gigs.php
    public static function search($search, $category, $delivery_time, $min_price, $max_price): array{
        $db = Database::getInstance();

        $where = [];
        $params = [];

        if (!empty($search)) {
            $where[] = "(s.taskname LIKE ? OR s.description LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        if (!empty($category)) {
            $where[] = "s.category = ?";
            $params[] = $category;
        }
        if (!empty($delivery_time)) {
            $where[] = "s.delivery_time = ?";
            $params[] = $delivery_time;
        }
        if ($min_price !== '') {
            $where[] = "s.pricing >= ?";
            $params[] = $min_price;
        }
        if ($max_price !== '') {
            $where[] = "s.pricing <= ?";
            $params[] = $max_price;
        }

        $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $db->prepare("
            SELECT s.*, u.profile_photo as seller_photo, u.name as seller_name,
                COALESCE(s.taskname, s.description) as task_title,
                (SELECT COUNT(*) FROM transactions WHERE service_id = s.id) as order_count,
                (SELECT GROUP_CONCAT(file_path) FROM media WHERE service_id = s.id ORDER BY id ASC) as all_images
            FROM services s
            JOIN users u ON s.user = u.username
            $whereSQL
            ORDER BY s.id DESC
        ");
        
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }

    #Specific function for task.php
    public static function get_service_with_media(int $id): array {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT s.*, 
                   COALESCE(s.taskname, s.description) as task_title,
                   (SELECT GROUP_CONCAT(file_path) FROM media WHERE service_id = s.id ORDER BY id ASC) as all_images
            FROM services s 
            WHERE s.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    #Specific function for favorites.php
    static function getFavoriteServices($username, $search = '', $category = '', $delivery_time = '', $min_price = '', $max_price = ''): array {
        $db = Database::getInstance();
        
        $where = ["fs.username = ?"];
        $params = [$username];

        if (!empty($search)) {
            $where[] = "(s.taskname LIKE ? OR s.description LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        if (!empty($category)) {
            $where[] = "s.category = ?";
            $params[] = $category;
        }
        
        if (!empty($delivery_time)) {
            $where[] = "s.delivery_time = ?";
            $params[] = $delivery_time;
        }
        
        if ($min_price !== '') {
            $where[] = "s.pricing >= ?";
            $params[] = $min_price;
        }
        
        if ($max_price !== '') {
            $where[] = "s.pricing <= ?";
            $params[] = $max_price;
        }
        
        $whereSQL = 'WHERE ' . implode(' AND ', $where);
        
        $stmt = $db->prepare("
            SELECT s.*, u.profile_photo as seller_photo, u.name as seller_name,
                COALESCE(s.taskname, s.description) as task_title,
                (SELECT COUNT(*) FROM transactions WHERE service_id = s.id) as order_count,
                (SELECT GROUP_CONCAT(file_path) FROM media WHERE service_id = s.id ORDER BY id ASC) as all_images
            FROM services s
            INNER JOIN favourite_services fs ON s.id = fs.id
            JOIN users u ON s.user = u.username
            $whereSQL
            ORDER BY s.id DESC
        ");
        
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
}
?>