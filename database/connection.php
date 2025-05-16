<?php
function getDatabaseConnection() {
    $dbfile = __DIR__ . '/database.db';
    
    try {
        return new PDO('sqlite:' . $dbfile);
    } catch (PDOException $e) {
        die('Connection failed: ' . $e->getMessage());
    }
}
?>
