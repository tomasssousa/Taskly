<?php
declare(strict_types=1);        # Se Alguém mudar os ficheiros de sitio, que tenha atenção a atualizar o path em baixo
require_once(__DIR__ . '/../database/connection.php');  # <--

class Media {
    public string $id;
    public string $service_id;
    public string $file_path;
    public string $media_type;

    public function __construct(string $id, string $service_id, string $file_path, string $media_type) {
        $this->id = $id;
        $this->service_id = $service_id;
        $this->file_path = $file_path;
        $this->media_type = $media_type;
    }

    public static function create($service_id, $file_path, $media_type) {
        $db = Database::getInstance();
        $stmt = $db->prepare('INSERT INTO media (service_id, file_path, media_type) VALUES (?, ?, ?)');
        $stmt->execute([$service_id, $file_path, $media_type]);
    }

    
}
?>