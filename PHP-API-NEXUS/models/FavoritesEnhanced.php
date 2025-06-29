<?php
class FavoritesEnhanced {
    private $usersConn;
    private $starsConn;
    private $table_name = "Favorites";

    // Propiedades del favorito
    public $id;
    public $user_id;
    public $constellation_id;

    public function __construct($usersDb, $starsDb = null) {
        $this->usersConn = $usersDb;
        $this->starsConn = $starsDb;
    }

    // Obtener todos los favoritos de un usuario con información de constelaciones
    public function getUserFavoritesWithNames($userId) {
        if ($this->starsConn) {
            // Si tenemos conexión a stars, hacer JOIN entre bases de datos
            $query = "SELECT f.Id, f.UserId, f.ConstellationId, 
                            ISNULL(c.english_name, 'Constelación ' + CAST(f.ConstellationId AS varchar)) as ConstellationName
                      FROM " . $this->table_name . " f
                      LEFT JOIN [nexus_stars].[dbo].[constellations] c ON f.ConstellationId = c.id
                      WHERE f.UserId = :userId
                      ORDER BY c.english_name";
        } else {
            // Fallback sin nombres de constelaciones
            $query = "SELECT f.Id, f.UserId, f.ConstellationId, 
                            'Constelación ' + CAST(f.ConstellationId AS varchar) as ConstellationName
                      FROM " . $this->table_name . " f
                      WHERE f.UserId = :userId
                      ORDER BY f.ConstellationId";
        }
        
        $stmt = $this->usersConn->prepare($query);
        $stmt->bindParam(":userId", $userId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Método original sin JOIN para compatibilidad
    public function getUserFavorites($userId) {
        $query = "SELECT f.Id, f.UserId, f.ConstellationId, 
                        'Constelación ' + CAST(f.ConstellationId AS varchar) as ConstellationName
                  FROM " . $this->table_name . " f
                  WHERE f.UserId = :userId
                  ORDER BY f.ConstellationId";
        $stmt = $this->usersConn->prepare($query);
        $stmt->bindParam(":userId", $userId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Verificar si una constelación es favorita de un usuario
    public function isFavorite($userId, $constellationId) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                  WHERE UserId = :userId AND ConstellationId = :constellationId";
        $stmt = $this->usersConn->prepare($query);
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
        $stmt = $this->usersConn->prepare($query);
        $stmt->bindParam(":userId", $userId);
        $stmt->bindParam(":constellationId", $constellationId);
        
        if ($stmt->execute()) {
            $this->id = $this->usersConn->lastInsertId();
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
        $stmt = $this->usersConn->prepare($query);
        $stmt->bindParam(":userId", $userId);
        $stmt->bindParam(":constellationId", $constellationId);
        return $stmt->execute();
    }

    // Eliminar favorito por ID
    public function removeById($favoriteId, $userId) {
        $query = "DELETE FROM " . $this->table_name . " 
                  WHERE Id = :favoriteId AND UserId = :userId";
        $stmt = $this->usersConn->prepare($query);
        $stmt->bindParam(":favoriteId", $favoriteId);
        $stmt->bindParam(":userId", $userId);
        return $stmt->execute();
    }

    // Obtener favorito por ID
    public function getById($favoriteId) {
        $query = "SELECT f.Id, f.UserId, f.ConstellationId
                  FROM " . $this->table_name . " f
                  WHERE f.Id = :favoriteId";
        $stmt = $this->usersConn->prepare($query);
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
        $stmt = $this->usersConn->prepare($query);
        $stmt->bindParam(":userId", $userId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Eliminar todos los favoritos de un usuario
    public function clearUserFavorites($userId) {
        $query = "DELETE FROM " . $this->table_name . " WHERE UserId = :userId";
        $stmt = $this->usersConn->prepare($query);
        $stmt->bindParam(":userId", $userId);
        return $stmt->execute();
    }

    // Obtener las constelaciones más populares
    public function getPopularConstellations($limit = 10) {
        $query = "SELECT f.ConstellationId, COUNT(*) as favorite_count
                  FROM " . $this->table_name . " f
                  GROUP BY f.ConstellationId
                  ORDER BY COUNT(*) DESC
                  OFFSET 0 ROWS FETCH NEXT :limit ROWS ONLY";
        $stmt = $this->usersConn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Método para obtener nombres de constelaciones desde la base de datos de estrellas
    public function getConstellationNames($constellationIds) {
        if (!$this->starsConn || empty($constellationIds)) {
            return [];
        }
        
        $placeholders = str_repeat('?,', count($constellationIds) - 1) . '?';
        $query = "SELECT id, english_name FROM constellations WHERE id IN ($placeholders)";
        $stmt = $this->starsConn->prepare($query);
        $stmt->execute($constellationIds);
        
        $names = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $names[$row['id']] = $row['english_name'];
        }
        
        return $names;
    }
}
?>
