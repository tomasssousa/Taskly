<?php
declare(strict_types=1);
require_once(__DIR__ . '/../database/connection.php');

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

    public static function update_service($id, $taskname, $description, $pricing, $delivery_time, $category, $images, $user) {
        $db = Database::getInstance();
        
        try {
            // Update the main service data (only update the image column, no media table)
            $stmt = $db->prepare('UPDATE services SET taskname = ?, description = ?, pricing = ?, delivery_time = ?, category = ?, image = ? WHERE id = ? AND user = ?');
            $success = $stmt->execute([$taskname, $description, $pricing, $delivery_time, $category, $images, $id, $user]);
            
            return $success;
        } catch (PDOException $e) {
            error_log("Update service error: " . $e->getMessage());
            return false;
        }
    }

    public static function add_as_favourite(string $username, int $id){
        $db = Database::getInstance();
        // Check if already favorited
        $stmt = $db->prepare('SELECT COUNT(*) FROM favourite_services WHERE username = ? AND id = ?');
        $stmt->execute([$username, $id]);
        if ($stmt->fetchColumn() == 0) {
            $stmt = $db->prepare('INSERT INTO favourite_services (username, id) VALUES (?, ?)');
            return $stmt->execute([$username, $id]);
        }
    }

    public static function remove_from_favourite(string $username, int $id){
        $db = Database::getInstance();
        $stmt = $db->prepare('DELETE FROM favourite_services WHERE username = ? AND id = ?');
        return $stmt->execute([$username, $id]);
    }
    
    #obtain all of a users favourited services
    public static function get_favourites(string $username, int $count): array {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT s.*, 
                   s.taskname as task_title,
                   s.image as all_images,
                   u.name as seller_name,
                   u.profile_photo as seller_photo
            FROM services s 
            JOIN favourite_services fs ON fs.id = s.id 
            LEFT JOIN users u ON s.user = u.username
            WHERE fs.username = ? 
            LIMIT ?
        ");
        $stmt->execute([$username, $count]);
        return $stmt->fetchAll();
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
                               s.image as all_images
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
                s.image as all_images
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
                   s.taskname as task_title,
                   GROUP_CONCAT(m.file_path) as all_images,
                   u.name as seller_name,
                   u.profile_photo as seller_photo
            FROM services s 
            LEFT JOIN users u ON s.user = u.username
            LEFT JOIN media m ON m.service_id = s.id
            WHERE s.id = ?
            GROUP BY s.id
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    #get all services
    public static function get_services(): array {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT s.*, 
                   s.taskname as task_title,
                   s.image as all_images,
                   u.name as seller_name,
                   u.profile_photo as seller_photo
            FROM services s 
            LEFT JOIN users u ON s.user = u.username
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    #Specific function for admin.php
    public static function get_all_services(): array {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT s.*, u.name as seller_name
            FROM services s 
            LEFT JOIN users u ON s.user = u.username
            ORDER BY s.id DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    #get services by category
    public static function get_services_by_category(string $category): array {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT s.*, 
                   s.taskname as task_title,
                   s.image as all_images,
                   u.name as seller_name,
                   u.profile_photo as seller_photo
            FROM services s 
            LEFT JOIN users u ON s.user = u.username
            WHERE s.category = ?
            ORDER BY s.id DESC
        ");
        $stmt->execute([$category]);
        return $stmt->fetchAll();
    }

    #get all categories from database
    public static function get_categories(): array {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare('SELECT name FROM categories ORDER BY name ASC');
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log("Get categories error: " . $e->getMessage());
            return []; // Return empty array if error
        }
    }

    #add new category to database
    public static function add_category(string $categoryName): bool {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare('INSERT INTO categories (name) VALUES (?)');
            return $stmt->execute([$categoryName]);
        } catch (PDOException $e) {
            error_log("Add category error: " . $e->getMessage());
            return false;
        }
    }

    #delete category from database
    public static function delete_category(string $categoryName): bool {
        try {
            $db = Database::getInstance();
            
            // First check if any services use this category
            $stmt = $db->prepare('SELECT COUNT(*) FROM services WHERE category = ?');
            $stmt->execute([$categoryName]);
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                return false; // Don't delete if services are using it
            }
            
            $stmt = $db->prepare('DELETE FROM categories WHERE name = ?');
            return $stmt->execute([$categoryName]);
        } catch (PDOException $e) {
            error_log("Delete category error: " . $e->getMessage());
            return false;
        }
    }

    #check if category exists
    public static function category_exists(string $categoryName): bool {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare('SELECT COUNT(*) FROM categories WHERE name = ?');
            $stmt->execute([$categoryName]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Category exists check error: " . $e->getMessage());
            return false;
        }
    }

    #get average rating for a service
    public static function get_service_rating(int $serviceId): array {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare('
                SELECT 
                    AVG(CAST(t.rating AS FLOAT)) as avg_rating,
                    COUNT(t.rating) as review_count
                FROM transactions t 
                WHERE t.service_id = ? AND t.rating IS NOT NULL AND t.rating > 0
            ');
            $stmt->execute([$serviceId]);
            $result = $stmt->fetch();
            
            return [
                'average' => $result['avg_rating'] ? round($result['avg_rating'], 2) : 0,
                'count' => $result['review_count'] ? intval($result['review_count']) : 0
            ];
        } catch (PDOException $e) {
            error_log("Get service rating error: " . $e->getMessage());
            return ['average' => 0, 'count' => 0];
        }
    }

    public static function get_service_reviews($serviceId) {
        $db = Database::getInstance();
        $stmt = $db->prepare('
            SELECT c.*, u.name as user_name, u.profile_photo,
                   t.rating as transaction_rating
            FROM comments c 
            JOIN users u ON c.user = u.username 
            LEFT JOIN transactions t ON c.service_id = t.service_id AND c.user = t.user
            WHERE c.service_id = ? AND c.reply_to IS NULL 
            ORDER BY c.id DESC
        ');
        $stmt->execute([$serviceId]);
        return $stmt->fetchAll();
    }

    #Specific function for task.php AND action_toggle_favorite.php
    public static function get_favourite_count($username, $serviceId) {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT COUNT(*) FROM favourite_services WHERE username = ? AND id = ?');
        $stmt->execute([$username, $serviceId]);
        return $stmt->fetchColumn() > 0;
    }

    #Specific function for action_delete_task.php
    public static function get_service_No_class(int $id): ?array {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT id, user, taskname FROM services WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
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
                s.image as all_images
            FROM services s
            INNER JOIN favourite_services fs ON s.id = fs.id
            JOIN users u ON s.user = u.username
            $whereSQL
            ORDER BY s.id DESC
        ");
        
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }

    #Specific function for edit_task.php
    public static function get_service_by_id_and_user(int $id, string $user){
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM services WHERE id = ? AND user = ?');
        $stmt->execute([$id, $user]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>