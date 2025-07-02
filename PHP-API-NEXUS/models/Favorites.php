<?php
/**
 * Modelo Favorites - Entidad de favorito (solo propiedades y conversiones)
 * La lógica de acceso a datos está en FavoritesRepository
 */
class Favorites {
    // Propiedades del favorito (coincidentes con el modelo ASP.NET)
    public $id;
    public $user_id;
    public $constellation_id;

    public function __construct($data = []) {
        if (!empty($data)) {
            $this->fillFromArray($data);
        }
    }

    /**
     * Llenar propiedades desde array de datos
     * @param array $data Datos del favorito
     */
    public function fillFromArray($data) {
        $this->id = $data['Id'] ?? $data['id'] ?? null;
        $this->user_id = $data['UserId'] ?? $data['user_id'] ?? null;
        $this->constellation_id = $data['ConstellationId'] ?? $data['constellation_id'] ?? null;
    }

    /**
     * Convertir a array para respuestas JSON
     * @return array Datos del favorito
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'constellation_id' => $this->constellation_id
        ];
    }

    /**
     * Convertir a array para base de datos
     * @return array Datos para inserción/actualización
     */
    public function toDatabaseArray() {
        return [
            'Id' => $this->id,
            'UserId' => $this->user_id,
            'ConstellationId' => $this->constellation_id
        ];
    }

    /**
     * Validar que el favorito tiene los datos mínimos requeridos
     * @return bool True si es válido
     */
    public function isValid() {
        return !empty($this->user_id) && !empty($this->constellation_id);
    }
}
?>
