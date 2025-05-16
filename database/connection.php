<?php
declare(strict_types = 1);

class Database {
    private static ?PDO $instance = null;

    public static function getInstance(): PDO {
        if (self::$instance === null) {             # Se Alguém mudar os ficheiros de sitio, que tenha atenção a atualizar o path em baixo
            self::$instance = new PDO('sqlite:' . __DIR__ . '/database.db');   # <--
            self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        return self::$instance;
    }
}

?>