<?php
/**
 * Modelo ExternalLogin - Compatible con ASP.NET (NexusAstralis.Models.User.ExternalLogin)
 * Representa el token de autenticación externa (Google, Facebook, etc.)
 */
class ExternalLogin {
    // Propiedades del modelo de login externo (coinciden exactamente con ASP.NET)
    public $token;                  // Token (string?) - Token de autenticación externa

    // Constructor para inicializar propiedades desde array
    public function __construct($data = []) {
        if (!empty($data)) {
            $this->fillFromArray($data);
        }
    }

    // Llenar propiedades desde array de datos (compatible con ASP.NET y PHP)
    public function fillFromArray($data) {
        $this->token = $data['Token'] ?? $data['token'] ?? null;
    }

    // Convertir a array para respuestas JSON (sin incluir token por seguridad)
    public function toArray($includeToken = false) {
        $loginData = [];

        // Solo incluir token si se solicita explícitamente (para operaciones internas)
        if ($includeToken) {
            $loginData['token'] = $this->token;
        }

        return $loginData;
    }

    // Convertir a array para base de datos (formato ASP.NET)
    public function toDatabaseArray() {
        return [
            'Token' => $this->token
        ];
    }

    // Validar que el token esté presente
    public function isValid() {
        $errors = $this->getValidationErrors();
        return empty($errors);
    }

    // Obtener errores de validación específicos
    public function getValidationErrors() {
        $errors = [];

        if (empty($this->token)) {
            $errors[] = "El token de autenticación externa es obligatorio";
        } elseif (!is_string($this->token)) {
            $errors[] = "El token debe ser una cadena válida";
        } elseif (strlen($this->token) < 10) {
            $errors[] = "El token no tiene un formato válido";
        }

        return $errors;
    }

    // Sanitizar el token
    public function sanitizeFields() {
        if (!empty($this->token)) {
            $this->token = trim($this->token);
        }
    }

    // Verificar si tiene el campo requerido
    public function hasRequiredFields() {
        return !empty($this->token);
    }

    // Verificar si el token parece ser un JWT de Google
    public function isGoogleJWT() {
        if (empty($this->token)) {
            return false;
        }

        // Un JWT válido tiene 3 partes separadas por puntos
        $parts = explode('.', $this->token);
        return count($parts) === 3;
    }

    // Obtener datos para logging (sin token completo por seguridad)
    public function getLogData() {
        $tokenPreview = '';
        if (!empty($this->token)) {
            $tokenPreview = substr($this->token, 0, 20) . '...';
        }

        return [
            'operation' => 'external_login_attempt',
            'tokenPreview' => $tokenPreview,
            'tokenLength' => strlen($this->token ?? ''),
            'isJWT' => $this->isGoogleJWT(),
            'timestamp' => date('Y-m-d H:i:s'),
            'userAgent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'ipAddress' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
        ];
    }

    // Crear instancia desde token simple
    public static function fromToken($token) {
        return new self(['token' => $token]);
    }

    // Obtener información del payload sin verificar (solo para debug - NO usar en producción)
    public function getUnverifiedPayload() {
        if (!$this->isGoogleJWT()) {
            return null;
        }

        try {
            $parts = explode('.', $this->token);
            $payload = base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1]));
            return json_decode($payload, true);
        } catch (Exception $e) {
            return null;
        }
    }
}
?>
