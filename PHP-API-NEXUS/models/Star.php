<?php
class Star {
    private $conn;
    private $table_name = "stars";

    // Propiedades de la estrella
    public $id;
    public $name;
    public $common_name;
    public $constellation_id;
    public $magnitude;
    public $spectral_type;
    public $distance_ly;
    public $ra; // Right Ascension
    public $dec; // Declination
    public $temperature;
    public $mass;
    public $radius;
    public $luminosity;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener todas las estrellas
    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener estrella por ID
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->common_name = $row['common_name'] ?? '';
            $this->constellation_id = $row['constellation_id'] ?? null;
            $this->magnitude = $row['magnitude'] ?? null;
            $this->spectral_type = $row['spectral_type'] ?? '';
            $this->distance_ly = $row['distance_ly'] ?? null;
            $this->ra = $row['ra'] ?? null;
            $this->dec = $row['dec'] ?? null;
            $this->temperature = $row['temperature'] ?? null;
            $this->mass = $row['mass'] ?? null;
            $this->radius = $row['radius'] ?? null;
            $this->luminosity = $row['luminosity'] ?? null;
            return true;
        }
        
        return false;
    }

    // Obtener estrellas por constelación
    public function getByConstellation($constellationId) {
        $query = "SELECT s.* FROM " . $this->table_name . " s
                  INNER JOIN constellation_stars cs ON s.id = cs.star_id 
                  WHERE cs.constellation_id = :constellation_id 
                  ORDER BY s.magnitude ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":constellation_id", $constellationId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener estrellas más brillantes (menor magnitud)
    public function getBrightest($limit = 20) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE magnitude IS NOT NULL 
                  ORDER BY magnitude ASC 
                  LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Buscar estrellas por nombre
    public function searchByName($searchTerm) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE name LIKE :search OR common_name LIKE :search 
                  ORDER BY magnitude ASC";
        $stmt = $this->conn->prepare($query);
        $searchParam = '%' . $searchTerm . '%';
        $stmt->bindParam(":search", $searchParam);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener estrellas por rango de magnitud
    public function getByMagnitudeRange($minMag, $maxMag) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE magnitude BETWEEN :min_mag AND :max_mag 
                  ORDER BY magnitude ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":min_mag", $minMag);
        $stmt->bindParam(":max_mag", $maxMag);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
