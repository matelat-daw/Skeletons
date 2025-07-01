<?php
/**
 * Comments Model - Modelo para comentarios de constelaciones
 * Compatible con ASP.NET Identity para NexusUsers
 * Solo contiene propiedades y métodos básicos de acceso a datos
 */

class Comments {
    private $conn;
    private $table_name = "Comments";

    // Propiedades del comentario (coincidentes exactamente con el modelo ASP.NET)
    public $id;                    // Id (int) - [Key]
    public $user_nick;             // UserNick (string?)
    public $constellation_name;    // ConstellationName (string?)
    public $comment;              // Comment (string?)
    public $user_id;              // UserId (string?)
    public $constellation_id;     // ConstellationId (int)

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obtener todos los comentarios de un usuario
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
     */
    public function deleteByUserId($userId) {
        $query = "DELETE FROM " . $this->table_name . " WHERE UserId = :userId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":userId", $userId);
        return $stmt->execute();
    }

    /**
     * Obtener comentarios recientes
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
     * Convertir a array para JSON
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'userNick' => $this->user_nick,
            'constellationName' => $this->constellation_name,
            'comment' => $this->comment,
            'userId' => $this->user_id,
            'constellationId' => $this->constellation_id
        ];
    }
}
?>
