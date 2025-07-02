<?php
/**
 * FavoritesRepository - Maneja el acceso a datos de favoritos
 * Refactorizado desde el modelo Favorites para mejor separación de responsabilidades
 */
class FavoritesRepository {
    private $conn;
    private $table_name = "Favorites";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obtener todos los favoritos de un usuario
     * @param string $userId ID del usuario
     * @return array Lista de favoritos
     */
    public function getUserFavorites($userId) {
        $query = "SELECT f.Id, f.UserId, f.ConstellationId
                  FROM " . $this->table_name . " f
                  WHERE f.UserId = :userId
                  ORDER BY f.ConstellationId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":userId", $userId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Verificar si una constelación es favorita de un usuario
     * @param string $userId ID del usuario
     * @param int $constellationId ID de la constelación
     * @return bool True si es favorito, false en caso contrario
     */
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

    /**
     * Agregar una constelación a favoritos
     * @param string $userId ID del usuario
     * @param int $constellationId ID de la constelación
     * @return bool|int ID del favorito creado o false si falló
     */
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
            return $this->conn->lastInsertId();
        }
        
        return false;
    }

    /**
     * Eliminar una constelación de favoritos por usuario y constelación
     * @param string $userId ID del usuario
     * @param int $constellationId ID de la constelación
     * @return bool True si se eliminó correctamente
     */
    public function removeFavorite($userId, $constellationId) {
        $query = "DELETE FROM " . $this->table_name . " 
                  WHERE UserId = :userId AND ConstellationId = :constellationId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":userId", $userId);
        $stmt->bindParam(":constellationId", $constellationId);
        return $stmt->execute();
    }

    /**
     * Eliminar favorito por ID
     * @param int $favoriteId ID del favorito
     * @param string $userId ID del usuario (para seguridad)
     * @return bool True si se eliminó correctamente
     */
    public function removeById($favoriteId, $userId) {
        $query = "DELETE FROM " . $this->table_name . " 
                  WHERE Id = :favoriteId AND UserId = :userId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":favoriteId", $favoriteId);
        $stmt->bindParam(":userId", $userId);
        return $stmt->execute();
    }

    /**
     * Obtener favorito por ID
     * @param int $favoriteId ID del favorito
     * @return array|false Datos del favorito o false si no existe
     */
    public function getById($favoriteId) {
        $query = "SELECT f.Id, f.UserId, f.ConstellationId
                  FROM " . $this->table_name . " f
                  WHERE f.Id = :favoriteId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":favoriteId", $favoriteId);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener estadísticas de favoritos de un usuario
     * @param string $userId ID del usuario
     * @return array Estadísticas de favoritos
     */
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

    /**
     * Eliminar todos los favoritos de un usuario
     * @param string $userId ID del usuario
     * @return bool True si se eliminaron correctamente
     */
    public function clearUserFavorites($userId) {
        $query = "DELETE FROM " . $this->table_name . " WHERE UserId = :userId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":userId", $userId);
        return $stmt->execute();
    }

    /**
     * Obtener las constelaciones más populares (más agregadas a favoritos)
     * @param int $limit Número máximo de resultados
     * @return array Lista de constelaciones populares
     */
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

    /**
     * Contar total de favoritos por constelación
     * @param int $constellationId ID de la constelación
     * @return int Número de veces que ha sido marcada como favorita
     */
    public function countFavoritesByConstellation($constellationId) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                  WHERE ConstellationId = :constellationId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":constellationId", $constellationId);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return intval($row['count']);
    }

    /**
     * Obtener usuarios que tienen una constelación como favorita
     * @param int $constellationId ID de la constelación
     * @return array Lista de IDs de usuarios
     */
    public function getUsersByFavoriteConstellation($constellationId) {
        $query = "SELECT DISTINCT UserId FROM " . $this->table_name . " 
                  WHERE ConstellationId = :constellationId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":constellationId", $constellationId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
?>
