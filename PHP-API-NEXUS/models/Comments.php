<?php
/**
 * Comments Model - Modelo para comentarios de constelaciones
 * Compatible con ASP.NET Identity para NexusUsers
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
    // User navigation property se maneja mediante joins en consultas ([JsonIgnore])

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obtener todos los comentarios de un usuario
     * Compatible con ASP.NET: getUserComments()
     */
    public function getUserComments($userId) {
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
     * Compatible con ASP.NET: getById()
     */
    public function getById($commentId) {
        $query = "SELECT c.Id, c.UserNick, c.ConstellationName, c.Comment, 
                        c.UserId, c.ConstellationId
                  FROM " . $this->table_name . " c
                  WHERE c.Id = :commentId";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":commentId", $commentId);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->id = $row['Id'];
            $this->user_nick = $row['UserNick'];
            $this->constellation_name = $row['ConstellationName'];
            $this->comment = $row['Comment'];
            $this->user_id = $row['UserId'];
            $this->constellation_id = $row['ConstellationId'];
            return true;
        }
        
        return false;
    }

    /**
     * Obtener comentarios por constelación
     * Compatible con ASP.NET: getByConstellationId()
     */
    public function getByConstellationId($constellationId) {
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
     * Agregar un nuevo comentario
     * Compatible con ASP.NET: addComment()
     */
    public function addComment($userId, $userNick, $commentText, $constellationId, $constellationName) {
        // Validaciones equivalentes a DataAnnotations de ASP.NET
        if (!$this->validateComment($commentText)) {
            return false;
        }

        if (!$this->validateUserId($userId)) {
            return false;
        }

        if (!$this->validateConstellationId($constellationId)) {
            return false;
        }

        $query = "INSERT INTO " . $this->table_name . " 
                  (UserNick, ConstellationName, Comment, UserId, ConstellationId) 
                  VALUES (:userNick, :constellationName, :comment, :userId, :constellationId)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":userNick", $userNick);
        $stmt->bindParam(":constellationName", $constellationName);
        $stmt->bindParam(":comment", $commentText);
        $stmt->bindParam(":userId", $userId);
        $stmt->bindParam(":constellationId", $constellationId);
        
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            $this->user_nick = $userNick;
            $this->constellation_name = $constellationName;
            $this->comment = $commentText;
            $this->user_id = $userId;
            $this->constellation_id = $constellationId;
            return true;
        }
        
        return false;
    }

    /**
     * Actualizar un comentario existente
     * Compatible con ASP.NET: updateComment()
     */
    public function updateComment($commentId, $userId, $newComment) {
        // Validaciones equivalentes a DataAnnotations de ASP.NET
        if (!$this->validateComment($newComment)) {
            return false;
        }

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
     * Compatible con ASP.NET: deleteComment()
     */
    public function deleteComment($commentId, $userId) {
        $query = "DELETE FROM " . $this->table_name . " 
                  WHERE Id = :commentId AND UserId = :userId";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":commentId", $commentId);
        $stmt->bindParam(":userId", $userId);
        
        return $stmt->execute() && $stmt->rowCount() > 0;
    }

    /**
     * Verificar si el usuario es propietario del comentario
     * Compatible con ASP.NET: isOwner()
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
     * Compatible con ASP.NET: getUserCommentsStats()
     */
    public function getUserCommentsStats($userId) {
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
     * Compatible con ASP.NET: clearUserComments()
     */
    public function clearUserComments($userId) {
        $query = "DELETE FROM " . $this->table_name . " WHERE UserId = :userId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":userId", $userId);
        return $stmt->execute();
    }

    /**
     * Obtener comentarios recientes
     * Compatible con ASP.NET: getRecentComments()
     */
    public function getRecentComments($limit = 10) {
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

    // ===============================
    // MÉTODOS DE VALIDACIÓN
    // Equivalentes a DataAnnotations de ASP.NET
    // ===============================

    /**
     * Validar comentario - Equivalente a [Required] y [StringLength] de ASP.NET
     */
    private function validateComment($comment) {
        if (empty($comment) || trim($comment) === '') {
            return false;
        }
        
        // Longitud máxima (equivalente a StringLength de ASP.NET)
        if (strlen($comment) > 1000) {
            return false;
        }
        
        return true;
    }

    /**
     * Validar ID de usuario - Equivalente a [Required] de ASP.NET
     */
    private function validateUserId($userId) {
        return !empty($userId) && is_string($userId);
    }

    /**
     * Validar ID de constelación - Equivalente a [Required] de ASP.NET
     */
    private function validateConstellationId($constellationId) {
        return !empty($constellationId) && (is_numeric($constellationId) || is_int($constellationId));
    }

    /**
     * Sanitizar texto del comentario
     * Equivalente a la validación de entrada de ASP.NET
     */
    public function sanitizeComment($comment) {
        // Limpiar HTML y caracteres especiales
        $comment = htmlspecialchars($comment, ENT_QUOTES, 'UTF-8');
        $comment = trim($comment);
        return $comment;
    }

    /**
     * Convertir a array para JSON
     * Equivalente a los DTOs de ASP.NET
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

    /**
     * Logging seguro sin información sensible
     * Equivalente al logging de ASP.NET
     */
    public function toLogString() {
        return "Comment[Id: {$this->id}, UserId: {$this->user_id}, ConstellationId: {$this->constellation_id}]";
    }
}
?>
