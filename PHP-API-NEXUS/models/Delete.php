<?php
/**
 * Modelo Delete - Compatible con ASP.NET (NexusAstralis.Models.User.Delete)
 * Representa la confirmación de eliminación de perfil de usuario
 */
class Delete {
    // Propiedades del modelo de eliminación (coinciden exactamente con ASP.NET)
    public $id;                     // Id (string?) - ID del usuario a eliminar

    // Constructor para inicializar propiedades desde array
    public function __construct($data = []) {
        if (!empty($data)) {
            $this->fillFromArray($data);
        }
    }

    // Llenar propiedades desde array de datos (compatible con ASP.NET y PHP)
    public function fillFromArray($data) {
        $this->id = $data['Id'] ?? $data['id'] ?? null;
    }

    // Convertir a array para respuestas JSON
    public function toArray() {
        return [
            'id' => $this->id
        ];
    }

    // Convertir a array para base de datos (formato ASP.NET)
    public function toDatabaseArray() {
        return [
            'Id' => $this->id
        ];
    }

    // Validar que el ID esté presente
    public function isValid() {
        return !empty($this->id);
    }

    // Obtener errores de validación específicos
    public function getValidationErrors() {
        $errors = [];

        if (empty($this->id)) {
            $errors[] = "El ID del usuario es obligatorio para la eliminación";
        } elseif (!is_string($this->id)) {
            $errors[] = "El ID del usuario debe ser una cadena válida";
        } elseif (strlen($this->id) < 10) {
            $errors[] = "El ID del usuario no tiene un formato válido";
        }

        return $errors;
    }

    // Sanitizar el ID
    public function sanitizeFields() {
        if (!empty($this->id)) {
            $this->id = trim($this->id);
        }
    }

    // Verificar si tiene el campo requerido
    public function hasRequiredFields() {
        return !empty($this->id);
    }

    // Obtener datos para logging (operación sensible)
    public function getLogData() {
        return [
            'operation' => 'user_deletion_request',
            'userId' => $this->id,
            'timestamp' => date('Y-m-d H:i:s'),
            'userAgent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'ipAddress' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
        ];
    }

    // Verificar si el ID tiene formato de GUID (ASP.NET Identity)
    public function isValidGuid() {
        if (empty($this->id)) {
            return false;
        }

        // Patrón para GUID con o sin guiones
        $guidPattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';
        $guidPatternNoHyphens = '/^[0-9a-f]{32}$/i';

        return preg_match($guidPattern, $this->id) || preg_match($guidPatternNoHyphens, $this->id);
    }

    // Crear instancia desde ID simple
    public static function fromId($id) {
        return new self(['id' => $id]);
    }

    // Obtener confirmación de eliminación estructurada
    public function getConfirmationData() {
        return [
            'userId' => $this->id,
            'action' => 'delete_user_profile',
            'warning' => 'Esta acción eliminará permanentemente el perfil del usuario y todos sus datos asociados',
            'irreversible' => true,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
}
?>
