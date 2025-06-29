<?php
class Comments {
    private $conn;
    private $table_name = "Comments";

    // Propiedades del comentario (coincidentes con el modelo ASP.NET)
    public $id;
    public $user_id;
    public $user_nick;
    public $comment;
    public $constellation_id;
    public $constellation_name;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener todos los comentarios de un usuario
    public function getUserComments($userId) {
        $query = "SELECT c.Id, c.UserId, c.UserNick, c.Comment, c.ConstellationId, c.ConstellationName
                  FROM " . $this->table_name . " c
                  WHERE c.UserId = :userId
                  ORDER BY c.Id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":userId", $userId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener comentarios de una constelación específica
    public function getConstellationComments($constellationId, $limit = 50) {
        $query = "SELECT c.Id, c.UserId, c.UserNick, c.Comment, c.ConstellationId, c.ConstellationName
                  FROM " . $this->table_name . " c
                  WHERE c.ConstellationId = :constellationId
                  ORDER BY c.Id DESC
                  OFFSET 0 ROWS FETCH NEXT :limit ROWS ONLY";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":constellationId", $constellationId);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Agregar un nuevo comentario
    public function addComment($userId, $userNick, $comment, $constellationId, $constellationName) {
        $query = "INSERT INTO " . $this->table_name . " (UserId, UserNick, Comment, ConstellationId, ConstellationName) 
                  VALUES (:userId, :userNick, :comment, :constellationId, :constellationName)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":userId", $userId);
        $stmt->bindParam(":userNick", $userNick);
        $stmt->bindParam(":comment", $comment);
        $stmt->bindParam(":constellationId", $constellationId);
        $stmt->bindParam(":constellationName", $constellationName);
        
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            $this->user_id = $userId;
            $this->user_nick = $userNick;
            $this->comment = $comment;
            $this->constellation_id = $constellationId;
            $this->constellation_name = $constellationName;
            return true;
        }
        
        return false;
    }

    // Actualizar un comentario (solo si el usuario es el propietario)
    public function updateComment($commentId, $userId, $newComment) {
        $query = "UPDATE " . $this->table_name . " 
                  SET Comment = :newComment 
                  WHERE Id = :commentId AND UserId = :userId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":newComment", $newComment);
        $stmt->bindParam(":commentId", $commentId);
        $stmt->bindParam(":userId", $userId);
        return $stmt->execute();
    }

    // Eliminar un comentario (solo si el usuario es el propietario)
    public function deleteComment($commentId, $userId) {
        $query = "DELETE FROM " . $this->table_name . " 
                  WHERE Id = :commentId AND UserId = :userId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":commentId", $commentId);
        $stmt->bindParam(":userId", $userId);
        return $stmt->execute();
    }

    // Obtener comentario por ID
    public function getById($commentId) {
        $query = "SELECT c.Id, c.UserId, c.UserNick, c.Comment, c.ConstellationId, c.ConstellationName
                  FROM " . $this->table_name . " c
                  WHERE c.Id = :commentId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":commentId", $commentId);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->id = $row['Id'];
            $this->user_id = $row['UserId'];
            $this->user_nick = $row['UserNick'];
            $this->comment = $row['Comment'];
            $this->constellation_id = $row['ConstellationId'];
            $this->constellation_name = $row['ConstellationName'];
            return true;
        }
        
        return false;
    }

    // Verificar si un comentario pertenece a un usuario
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

    // Obtener estadísticas de comentarios de un usuario
    public function getUserCommentsStats($userId) {
        $query = "SELECT 
                    COUNT(*) as total_comments,
                    COUNT(DISTINCT c.ConstellationId) as constellations_commented,
                    AVG(LEN(c.Comment)) as average_comment_length
                  FROM " . $this->table_name . " c
                  WHERE c.UserId = :userId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":userId", $userId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Eliminar todos los comentarios de un usuario
    public function clearUserComments($userId) {
        $query = "DELETE FROM " . $this->table_name . " WHERE UserId = :userId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":userId", $userId);
        return $stmt->execute();
    }

    // Obtener los usuarios más activos en comentarios
    public function getTopCommenters($limit = 10) {
        $query = "SELECT c.UserId, c.UserNick, COUNT(*) as comment_count
                  FROM " . $this->table_name . " c
                  GROUP BY c.UserId, c.UserNick
                  ORDER BY COUNT(*) DESC
                  OFFSET 0 ROWS FETCH NEXT :limit ROWS ONLY";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener las constelaciones con más comentarios
    public function getMostCommentedConstellations($limit = 10) {
        $query = "SELECT c.ConstellationId, c.ConstellationName, COUNT(*) as comment_count
                  FROM " . $this->table_name . " c
                  GROUP BY c.ConstellationId, c.ConstellationName
                  ORDER BY COUNT(*) DESC
                  OFFSET 0 ROWS FETCH NEXT :limit ROWS ONLY";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Buscar comentarios por texto
    public function searchComments($searchTerm, $limit = 50) {
        $query = "SELECT c.Id, c.UserId, c.UserNick, c.Comment, c.ConstellationId, c.ConstellationName
                  FROM " . $this->table_name . " c
                  WHERE c.Comment LIKE :searchTerm
                  ORDER BY c.Id DESC
                  OFFSET 0 ROWS FETCH NEXT :limit ROWS ONLY";
        $stmt = $this->conn->prepare($query);
        $searchTerm = '%' . $searchTerm . '%';
        $stmt->bindParam(":searchTerm", $searchTerm);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener comentarios recientes (últimos comentarios del sistema)
    public function getRecentComments($limit = 20) {
        $query = "SELECT c.Id, c.UserId, c.UserNick, c.Comment, c.ConstellationId, c.ConstellationName
                  FROM " . $this->table_name . " c
                  ORDER BY c.Id DESC
                  OFFSET 0 ROWS FETCH NEXT :limit ROWS ONLY";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
