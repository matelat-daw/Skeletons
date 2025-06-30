<?php
class Constellation {
    private $conn;
    private $table_name = "constellations";

    // Propiedades de la constelación (coincidentes con el modelo ASP.NET)
    public $id;
    public $code;
    public $latin_name;
    public $english_name;
    public $spanish_name;
    public $mythology;
    public $area_degrees;
    public $declination;
    public $celestial_zone;
    public $ecliptic_zone;
    public $brightest_star;
    public $discovery;
    public $image_name;
    public $image_url;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener todas las constelaciones
    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY english_name";
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
            return $row; // Devolver directamente el array
        }
        
        return false;
    }

    // Obtener constelación por código
    public function getByCode($code) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE code = :code";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":code", $code);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->id = $row['id'];
            $this->code = $row['code'];
            $this->latin_name = $row['latin_name'];
            $this->english_name = $row['english_name'];
            $this->spanish_name = $row['spanish_name'];
            $this->mythology = $row['mythology'];
            $this->area_degrees = $row['area_degrees'];
            $this->declination = $row['declination'];
            $this->celestial_zone = $row['celestial_zone'];
            $this->ecliptic_zone = $row['ecliptic_zone'];
            $this->brightest_star = $row['brightest_star'];
            $this->discovery = $row['discovery'];
            $this->image_name = $row['image_name'];
            $this->image_url = $row['image_url'];
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
        $query = "SELECT * FROM " . $this->table_name . " WHERE id IN ({$placeholders}) ORDER BY english_name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($ids);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener estrellas de una constelación
    public function getStars($constellationId) {
        $query = "SELECT s.* FROM stars s 
                  INNER JOIN constellation_stars cs ON s.id = cs.star_id 
                  WHERE cs.constellation_id = :constellation_id 
                  ORDER BY CAST(s.mag AS FLOAT) ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":constellation_id", $constellationId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener estrellas de una constelación por ID (alias para compatibilidad)
    public function getStarsByConstellationId($constellationId) {
        // Primero verificar si la constelación existe
        $checkQuery = "SELECT id FROM " . $this->table_name . " WHERE id = :id";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bindParam(":id", $constellationId);
        $checkStmt->execute();
        
        if (!$checkStmt->fetch()) {
            return false; // La constelación no existe
        }
        
        // Si existe, obtener las estrellas
        return $this->getStars($constellationId);
    }

    // Buscar constelaciones por nombre (en cualquier idioma)
    public function searchByName($searchTerm) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE (latin_name LIKE ? 
                     OR english_name LIKE ? 
                     OR spanish_name LIKE ? 
                     OR code LIKE ?)
                  ORDER BY english_name";
        $stmt = $this->conn->prepare($query);
        $searchParam = '%' . $searchTerm . '%';
        $stmt->execute([$searchParam, $searchParam, $searchParam, $searchParam]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener constelaciones por zona celestial
    public function getByCelestialZone($zone) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE celestial_zone = :zone 
                  ORDER BY english_name";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":zone", $zone);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener constelaciones por zona eclíptica
    public function getByEclipticZone($zone) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE ecliptic_zone = :zone 
                  ORDER BY english_name";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":zone", $zone);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener constelaciones ordenadas por área (más grandes primero)
    public function getByArea($limit = null) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE area_degrees IS NOT NULL 
                  ORDER BY area_degrees DESC";
        
        if ($limit) {
            $query .= " OFFSET 0 ROWS FETCH NEXT :limit ROWS ONLY";
        }
        
        $stmt = $this->conn->prepare($query);
        if ($limit) {
            $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener constelaciones con mitología
    public function getWithMythology() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE mythology IS NOT NULL AND mythology != '' 
                  ORDER BY english_name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener constelaciones con imágenes
    public function getWithImages() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE (image_name IS NOT NULL AND image_name != '') 
                     OR (image_url IS NOT NULL AND image_url != '') 
                  ORDER BY english_name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener constelaciones modernas vs antiguas
    public function getByDiscoveryPeriod($period) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE discovery LIKE :period 
                  ORDER BY english_name";
        $stmt = $this->conn->prepare($query);
        $periodParam = '%' . $period . '%';
        $stmt->bindParam(":period", $periodParam);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener constelaciones del zodíaco
    public function getZodiacConstellations() {
        $zodiacCodes = ['Ari', 'Tau', 'Gem', 'Cnc', 'Leo', 'Vir', 'Lib', 'Sco', 'Sgr', 'Cap', 'Aqr', 'Psc'];
        $placeholders = str_repeat('?,', count($zodiacCodes) - 1) . '?';
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE code IN ({$placeholders}) 
                  ORDER BY english_name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($zodiacCodes);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener estadísticas básicas
    public function getStats() {
        $query = "SELECT 
                    COUNT(*) as total_constellations,
                    COUNT(CASE WHEN mythology IS NOT NULL AND mythology != '' THEN 1 END) as with_mythology,
                    COUNT(CASE WHEN image_name IS NOT NULL AND image_name != '' THEN 1 END) as with_images,
                    AVG(area_degrees) as avg_area,
                    MAX(area_degrees) as max_area,
                    MIN(area_degrees) as min_area
                  FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>