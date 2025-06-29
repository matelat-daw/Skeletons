<?php
class Constellation {
    private $conn;
    private $table_name = "constellations";

    // Propiedades de la constelación
    public $id;
    public $name;
    public $description;
    public $mythology;
    public $best_time_to_observe;
    public $hemisphere;
    public $size_rank;
    public $main_stars_count;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener todas las constelaciones
    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener constelación por ID
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->description = $row['description'] ?? '';
            $this->mythology = $row['mythology'] ?? '';
            $this->best_time_to_observe = $row['best_time_to_observe'] ?? '';
            $this->hemisphere = $row['hemisphere'] ?? '';
            $this->size_rank = $row['size_rank'] ?? 0;
            $this->main_stars_count = $row['main_stars_count'] ?? 0;
            return true;
        }
        
        return false;
    }

    // Obtener constelaciones por lista de IDs
    public function getByIds($ids) {
        if (empty($ids)) {
            return [];
        }
        
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $query = "SELECT * FROM " . $this->table_name . " WHERE id IN ({$placeholders}) ORDER BY name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($ids);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener estrellas de una constelación
    public function getStars($constellationId) {
        $query = "SELECT s.* FROM stars s 
                  INNER JOIN constellation_stars cs ON s.id = cs.star_id 
                  WHERE cs.constellation_id = :constellation_id 
                  ORDER BY s.name";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":constellation_id", $constellationId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Buscar constelaciones por nombre
    public function searchByName($searchTerm) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE name LIKE :search 
                  ORDER BY name";
        $stmt = $this->conn->prepare($query);
        $searchParam = '%' . $searchTerm . '%';
        $stmt->bindParam(":search", $searchParam);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
