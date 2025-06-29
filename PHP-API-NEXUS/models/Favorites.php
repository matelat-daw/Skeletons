<?php
class Favorites {
    private $conn;
    private $table_name = "Favorites";

    // Propiedades del favorito (coincidentes con el modelo ASP.NET)
    public $id;
    public $user_id;
    public $constellation_id;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener todos los favoritos de un usuario
    public function getUserFavorites($userId) {
        $query = "SELECT f.Id, f.UserId, f.ConstellationId, 
                        CAST(f.ConstellationId AS varchar(50)) as ConstellationName
                  FROM " . $this->table_name . " f
                  WHERE f.UserId = :userId
                  ORDER BY f.ConstellationId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":userId", $userId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Verificar si una constelación es favorita de un usuario
    public function isFavorite($userId, $constellationId) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                  WHERE UserId = :userId AND ConstellationId = :constellationId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":userId", $userId);
        $stmt->bindParam(":constellationId", $constellationId);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'] > 0;
    }

    // Agregar una constelación a favoritos
    public function addFavorite($userId, $constellationId) {
        // Verificar si ya es favorito
        if ($this->isFavorite($userId, $constellationId)) {
            return false; // Ya es favorito
        }

        $query = "INSERT INTO " . $this->table_name . " (UserId, ConstellationId) VALUES (:userId, :constellationId)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":userId", $userId);
        $stmt->bindParam(":constellationId", $constellationId);
        
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            $this->user_id = $userId;
            $this->constellation_id = $constellationId;
            return true;
        }
        
        return false;
    }

    // Eliminar una constelación de favoritos
    public function removeFavorite($userId, $constellationId) {
        $query = "DELETE FROM " . $this->table_name . " 
                  WHERE UserId = :userId AND ConstellationId = :constellationId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":userId", $userId);
        $stmt->bindParam(":constellationId", $constellationId);
        return $stmt->execute();
    }

    // Eliminar favorito por ID
    public function removeById($favoriteId, $userId) {
        $query = "DELETE FROM " . $this->table_name . " 
                  WHERE Id = :favoriteId AND UserId = :userId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":favoriteId", $favoriteId);
        $stmt->bindParam(":userId", $userId);
        return $stmt->execute();
    }

    // Obtener favorito por ID
    public function getById($favoriteId) {
        $query = "SELECT f.Id, f.UserId, f.ConstellationId,
                        CAST(f.ConstellationId AS varchar(50)) as ConstellationName
                  FROM " . $this->table_name . " f
                  WHERE f.Id = :favoriteId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":favoriteId", $favoriteId);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->id = $row['Id'];
            $this->user_id = $row['UserId'];
            $this->constellation_id = $row['ConstellationId'];
            return true;
        }
        
        return false;
    }

    // Obtener estadísticas de favoritos de un usuario
    public function getUserFavoritesStats($userId) {
        $query = "SELECT 
                    COUNT(*) as total_favorites,
                    COUNT(DISTINCT f.ConstellationId) as unique_constellations
                  FROM " . $this->table_name . " f
                  WHERE f.UserId = :userId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":userId", $userId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Eliminar todos los favoritos de un usuario
    public function clearUserFavorites($userId) {
        $query = "DELETE FROM " . $this->table_name . " WHERE UserId = :userId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":userId", $userId);
        return $stmt->execute();
    }

    // Obtener las constelaciones más populares (más agregadas a favoritos)
    public function getPopularConstellations($limit = 10) {
        $query = "SELECT f.ConstellationId, COUNT(*) as favorite_count
                  FROM " . $this->table_name . " f
                  GROUP BY f.ConstellationId
                  ORDER BY COUNT(*) DESC
                  OFFSET 0 ROWS FETCH NEXT :limit ROWS ONLY";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
