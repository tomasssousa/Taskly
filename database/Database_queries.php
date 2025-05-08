<?php

    # Nas outras coisas relacionadas com base de dados é que estão as implementações, de acordo com o exemplo do chinook dado pelo professor
    # Espero que a parte do PDO, das base de dados, possamos copiar e adaptar para o nosso site
    # NÃO MANDAR ESTES COMENTÁRIOS ^^^

    class Database {
        private $db;

        #Será que é preciso uma função para fazer a base de dados?
        # O professor já dá uma...
        public function __construct($dsn = 'sqlite:database.sqlite3') {
            try {
                $this->db = new PDO($dsn);
                $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                die("Database connection failed: " . $e->getMessage());
            }
        }
        // foi esta. Acho que é preciso mas, honestamente, não sei como funciona.

        public function getServices(): array{
            $stmt = $this->db->prepare('SELECT * FROM services');
            $stmt->execute();
            return $stmt->fetch();
        }

        #Passar o id do serviço obter os ids das imagensdo serviço.
        #As imagens vão ser guardadas com id.png (ou outro tipo de imagem) para podermos obter o seu nome atráves do id.
        #O professor disse para as guardar num folder em vez da DB porque senão perdemos performance na BD.
        public function getServiceImagesIds(int $id): array{
            $stmt = $this->db->prepare('SELECT id FROM service_image WHERE services = ?');
            $stmt->execute([$id]);
            return $stmt->fetch();
        }

        public function getServiceCategories(int $id): array{ #corrigir esta
            $stmt = $this->db->prepare('SELECT category FROM categories WHERE services = ?');
            $stmt->execute([$id]);
            return $stmt->fetch();
        }

        public function getServiceComments(int $id): array{
            $stmt = $this->db->prepare('SELECT comment, user FROM comments WHERE service_id = ?');
            $stmt->execute([$id]);
            return $stmt->fetch();
        }

        public function getUser(string $username):array{
            $stmt = $this->db->prepare('SELECT * FROM users WHERE username = ?');
            $stmt->execute([$username]);
            return $stmt->fetch();
        }

        public function CreateUser(string $username, string $name, string $password, string $email){
            $stmt = $this->db->prepare('SELECT * FROM users WHERE username = ?');

        }
    }
?>