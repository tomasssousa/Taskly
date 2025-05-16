<?php
declare(strict_types=1);        # Se Alguém mudar os ficheiros de sitio, que tenha atenção a atualizar o path em baixo
require_once(__DIR__ . '/../database/connection.php');  # <--

class Category {
    public string $name;

    public function __construct(string $name) {
        $this->name = $name;
    }

    public static function get_category_by_name(string $name): Category {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM categories WHERE name = ?');
        $stmt->execute([$name]);
        $category = $stmt->fetch();
        return new Category($category['name']);
    }

    public static function get_all_categories($count): array {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM categories LIMIT ?');
        $stmt->execute([$count]);

        $categories = [];

        while ($category = $stmt->fetch()) {
            $categories[] = new Category($category['name']);
        }
        return $categories;
    }
    
    public static function add_category_to_service($category, $service_id) {
        $db = Database::getInstance();
        $stmt = $db->prepare('INSERT INTO cate_serv (service_id, category) VALUES (?, ?)');
        $stmt->execute([$service_id, $category]);
    }
}
?>