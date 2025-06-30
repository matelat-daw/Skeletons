<?php
class Star {
    private $conn;
    private $table_name = "stars";

    // Propiedades de la estrella (coincidentes con el modelo ASP.NET)
    public $id;
    public $x;
    public $y;
    public $z;
    public $ra;
    public $dec;
    public $mag;
    public $ci;
    public $bf;
    public $hr;
    public $proper;
    public $az;
    public $alt;
    public $hip;
    public $hd;
    public $gl;
    public $dist;
    public $pmra;
    public $pmdec;
    public $rv;
    public $absmag;
    public $spect;
    public $vx;
    public $vy;
    public $vz;
    public $rarad;
    public $decrad;
    public $pmrarad;
    public $pmdecrad;
    public $bayer;
    public $flam;
    public $con;
    public $comp;
    public $comp_primary;
    public $base; // en ASP.NET es _base pero en PHP usamos base
    public $lum;
    public $var;
    public $var_min;
    public $var_max;
    public $x_gal;
    public $y_gal;
    public $z_gal;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener todas las estrellas
    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY id";
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
            return $row; // Devolver directamente el array
        }
        
        return false;
    }

    // Obtener estrellas por constelación
    public function getByConstellation($constellationId) {
        $query = "SELECT s.* FROM " . $this->table_name . " s
                  INNER JOIN constellation_stars cs ON s.id = cs.star_id 
                  WHERE cs.constellation_id = :constellation_id 
                  ORDER BY CAST(s.mag AS FLOAT) ASC"; // Ordenar por magnitud (más brillantes primero)
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":constellation_id", $constellationId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener estrellas más brillantes (menor magnitud)
    public function getBrightest($limit = 20) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE mag IS NOT NULL AND mag != '' 
                  ORDER BY CAST(mag AS FLOAT) ASC 
                  OFFSET 0 ROWS FETCH NEXT :limit ROWS ONLY";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Buscar estrellas por nombre (proper, bayer, hip, hr)
    public function searchByName($searchTerm) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE (proper LIKE ? 
                     OR bayer LIKE ? 
                     OR hip LIKE ? 
                     OR hr LIKE ? 
                     OR hd LIKE ?)
                  ORDER BY CAST(mag AS FLOAT) ASC";
        $stmt = $this->conn->prepare($query);
        $searchParam = '%' . $searchTerm . '%';
        $stmt->execute([$searchParam, $searchParam, $searchParam, $searchParam, $searchParam]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener estrellas por rango de magnitud
    public function getByMagnitudeRange($minMag, $maxMag) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE mag IS NOT NULL AND mag != ''
                    AND CAST(mag AS FLOAT) BETWEEN :min_mag AND :max_mag 
                  ORDER BY CAST(mag AS FLOAT) ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":min_mag", $minMag);
        $stmt->bindParam(":max_mag", $maxMag);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener estrellas por constelación (usando campo 'con')
    public function getByConstellationCode($conCode) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE con = :con_code 
                  ORDER BY CAST(mag AS FLOAT) ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":con_code", $conCode);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener estrellas por designación Bayer
    public function getByBayer($bayerDesignation) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE bayer LIKE :bayer 
                  ORDER BY CAST(mag AS FLOAT) ASC";
        $stmt = $this->conn->prepare($query);
        $bayerParam = '%' . $bayerDesignation . '%';
        $stmt->bindParam(":bayer", $bayerParam);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener estrellas por tipo espectral
    public function getBySpectralType($spectralType) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE spect LIKE :spect 
                  ORDER BY CAST(mag AS FLOAT) ASC";
        $stmt = $this->conn->prepare($query);
        $spectParam = $spectralType . '%';
        $stmt->bindParam(":spect", $spectParam);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener estrellas variables
    public function getVariableStars() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE var IS NOT NULL AND var != '' 
                  ORDER BY CAST(mag AS FLOAT) ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener estrellas con nombres propios
    public function getNamedStars() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE proper IS NOT NULL AND proper != '' 
                  ORDER BY CAST(mag AS FLOAT) ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>