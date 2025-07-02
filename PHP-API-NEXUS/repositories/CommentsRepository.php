<?php
/**
 * CommentsRepository - Maneja el acceso a datos de comentarios
 * Refactorizado desde el modelo Comments para mejor separación de responsabilidades
 */
class CommentsRepository {
    private $conn;
    private $table_name = "Comments";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obtener todos los comentarios de un usuario
     * @param string $userId ID del usuario
     * @return array Lista de comentarios
     */
    public function findByUserId($userId) {
        $query = "SELECT c.Id, c.UserNick, c.ConstellationName, c.Comment, 
                        c.UserId, c.ConstellationId
                  FROM " . $this->table_name . " c
                  WHERE c.UserId = :userId
                  ORDER BY c.Id DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":userId", $userId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener comentario por ID
     * @param int $commentId ID del comentario
     * @return array|false Datos del comentario o false si no existe
     */
    public function findById($commentId) {
        $query = "SELECT c.Id, c.UserNick, c.ConstellationName, c.Comment, 
                        c.UserId, c.ConstellationId
                  FROM " . $this->table_name . " c
                  WHERE c.Id = :commentId";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":commentId", $commentId);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener comentarios por constelación
     * @param int $constellationId ID de la constelación
     * @return array Lista de comentarios
     */
    public function findByConstellationId($constellationId) {
        $query = "SELECT c.Id, c.UserNick, c.ConstellationName, c.Comment, 
                        c.UserId, c.ConstellationId
                  FROM " . $this->table_name . " c
                  WHERE c.ConstellationId = :constellationId
                  ORDER BY c.Id DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":constellationId", $constellationId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crear un nuevo comentario
     * @param array $data Datos del comentario
     * @return int|false ID del comentario creado o false si falló
     */
    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (UserNick, ConstellationName, Comment, UserId, ConstellationId) 
                  VALUES (:userNick, :constellationName, :comment, :userId, :constellationId)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":userNick", $data['userNick']);
        $stmt->bindParam(":constellationName", $data['constellationName']);
        $stmt->bindParam(":comment", $data['comment']);
        $stmt->bindParam(":userId", $data['userId']);
        $stmt->bindParam(":constellationId", $data['constellationId']);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }

    /**
     * Actualizar un comentario
     * @param int $commentId ID del comentario
     * @param string $userId ID del usuario
     * @param string $newComment Nuevo texto del comentario
     * @return bool True si se actualizó correctamente
     */
    public function update($commentId, $userId, $newComment) {
        $query = "UPDATE " . $this->table_name . " 
                  SET Comment = :comment 
                  WHERE Id = :commentId AND UserId = :userId";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":comment", $newComment);
        $stmt->bindParam(":commentId", $commentId);
        $stmt->bindParam(":userId", $userId);
        
        return $stmt->execute() && $stmt->rowCount() > 0;
    }

    /**
     * Eliminar un comentario
     * @param int $commentId ID del comentario
     * @param string $userId ID del usuario
     * @return bool True si se eliminó correctamente
     */
    public function delete($commentId, $userId) {
        $query = "DELETE FROM " . $this->table_name . " 
                  WHERE Id = :commentId AND UserId = :userId";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":commentId", $commentId);
        $stmt->bindParam(":userId", $userId);
        
        return $stmt->execute() && $stmt->rowCount() > 0;
    }

    /**
     * Verificar si el usuario es propietario del comentario
     * @param int $commentId ID del comentario
     * @param string $userId ID del usuario
     * @return bool True si es propietario
     */
    public function isOwner($commentId, $userId) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                  WHERE Id = :commentId AND UserId = :userId";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":commentId", $commentId);
        $stmt->bindParam(":userId", $userId);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'] > 0;
    }

    /**
     * Obtener estadísticas de comentarios de un usuario
     * @param string $userId ID del usuario
     * @return array Estadísticas de comentarios
     */
    public function getUserStats($userId) {
        $query = "SELECT 
                    COUNT(*) as total_comments,
                    COUNT(DISTINCT ConstellationId) as unique_constellations
                  FROM " . $this->table_name . " 
                  WHERE UserId = :userId";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":userId", $userId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Eliminar todos los comentarios de un usuario
     * @param string $userId ID del usuario
     * @return bool True si se eliminaron correctamente
     */
    public function deleteByUserId($userId) {
        $query = "DELETE FROM " . $this->table_name . " WHERE UserId = :userId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":userId", $userId);
        return $stmt->execute();
    }

    /**
     * Obtener comentarios recientes
     * @param int $limit Número máximo de comentarios a obtener
     * @return array Lista de comentarios recientes
     */
    public function findRecent($limit = 10) {
        $query = "SELECT c.Id, c.UserNick, c.ConstellationName, c.Comment, 
                        c.UserId, c.ConstellationId
                  FROM " . $this->table_name . " c
                  ORDER BY c.Id DESC
                  OFFSET 0 ROWS FETCH NEXT :limit ROWS ONLY";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener todos los comentarios
     * @return array Lista de todos los comentarios
     */
    public function findAll() {
        $query = "SELECT c.Id, c.UserNick, c.ConstellationName, c.Comment, 
                        c.UserId, c.ConstellationId
                  FROM " . $this->table_name . " c
                  ORDER BY c.Id DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Contar comentarios por constelación
     * @param int $constellationId ID de la constelación
     * @return int Número de comentarios
     */
    public function countByConstellation($constellationId) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                  WHERE ConstellationId = :constellationId";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":constellationId", $constellationId);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return intval($row['count']);
    }

    /**
     * Obtener constelaciones más comentadas
     * @param int $limit Número máximo de resultados
     * @return array Lista de constelaciones con más comentarios
     */
    public function getMostCommentedConstellations($limit = 10) {
        $query = "SELECT ConstellationId, ConstellationName, COUNT(*) as comment_count
                  FROM " . $this->table_name . " 
                  GROUP BY ConstellationId, ConstellationName
                  ORDER BY COUNT(*) DESC
                  OFFSET 0 ROWS FETCH NEXT :limit ROWS ONLY";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
