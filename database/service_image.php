<?php
declare(strict_types=1);        # Se Alguém mudar os ficheiros de sitio, que tenha atenção a atualizar o path em baixo
require_once(__DIR__ . '/../database/database.php');  # <--

class Service_image{
    public string $id;
    public int $service;

    public function __construct(int $id, string $service){
        $this->id = $id;
        $this->service = $service;
    }

    public static function add_image_to_service($id, $service){
        $db = Database::getInstance();
        $stmt = $db->prepare('INSERT INTO service_image (id, services) VALUES (?, ?)');
        $stmt->execute([$id, $service]);
    }

    #get the id of the images that belong to a certain service, with the $count limiter
    public static function get_service_images($service, $count): array {
        $db = Database::getInstance();
        $stmt = $db->prepare('SELECT * FROM service_image WHERE services = ? LIMIT ?');
        $stmt->execute([$service, $count]);

        $images = [];

        while ($image = $stmt->fetch()) {
            $images[] = new Service_image($image['id'], $image['services']);
        }
        return $images;
    }
}
?>