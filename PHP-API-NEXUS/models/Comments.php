<?php
/**
 * Comments Model - Entidad de comentario (solo propiedades y conversiones)
 * Compatible con ASP.NET Identity para NexusUsers
 * La lógica de acceso a datos está en CommentsRepository
 */
class Comments {
    // Propiedades del comentario (coincidentes exactamente con el modelo ASP.NET)
    public $id;                    // Id (int) - [Key]
    public $user_nick;             // UserNick (string?)
    public $constellation_name;    // ConstellationName (string?)
    public $comment;              // Comment (string?)
    public $user_id;              // UserId (string?)
    public $constellation_id;     // ConstellationId (int)

    public function __construct($data = []) {
        if (!empty($data)) {
            $this->fillFromArray($data);
        }
    }

    /**
     * Llenar propiedades desde array de datos
     * @param array $data Datos del comentario
     */
    public function fillFromArray($data) {
        $this->id = $data['Id'] ?? $data['id'] ?? null;
        $this->user_nick = $data['UserNick'] ?? $data['user_nick'] ?? null;
        $this->constellation_name = $data['ConstellationName'] ?? $data['constellation_name'] ?? null;
        $this->comment = $data['Comment'] ?? $data['comment'] ?? null;
        $this->user_id = $data['UserId'] ?? $data['user_id'] ?? null;
        $this->constellation_id = $data['ConstellationId'] ?? $data['constellation_id'] ?? null;
    }

    /**
     * Convertir a array para respuestas JSON
     * @return array Datos del comentario
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
     * Convertir a array para base de datos
     * @return array Datos para inserción/actualización
     */
    public function toDatabaseArray() {
        return [
            'Id' => $this->id,
            'UserNick' => $this->user_nick,
            'ConstellationName' => $this->constellation_name,
            'Comment' => $this->comment,
            'UserId' => $this->user_id,
            'ConstellationId' => $this->constellation_id
        ];
    }

    /**
     * Validar que el comentario tiene los datos mínimos requeridos
     * @return bool True si es válido
     */
    public function isValid() {
        return !empty($this->user_id) && 
               !empty($this->user_nick) && 
               !empty($this->comment) && 
               !empty($this->constellation_id);
    }

    /**
     * Obtener datos para crear comentario
     * @return array Datos preparados para inserción
     */
    public function getCreateData() {
        return [
            'userNick' => $this->user_nick,
            'constellationName' => $this->constellation_name,
            'comment' => $this->comment,
            'userId' => $this->user_id,
            'constellationId' => $this->constellation_id
        ];
    }
}
?>
