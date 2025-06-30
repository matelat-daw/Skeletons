<?php
/**
 * Modelo Login - Compatible con ASP.NET (NexusAstralis.Models.User.Login)
 * Representa los datos de autenticación de usuario
 */
class Login {
    // Propiedades del modelo de login (coinciden con ASP.NET)
    public $email;          // Email (string?) - DataType.EmailAddress
    public $password;       // Password (string?) - DataType.Password
    public $rememberMe;     // RememberMe (bool) - "Recuérdame!"

    // Constructor para inicializar propiedades desde array
    public function __construct($data = []) {
        if (!empty($data)) {
            $this->fillFromArray($data);
        }
    }

    // Llenar propiedades desde array de datos (compatible con ASP.NET y PHP)
    public function fillFromArray($data) {
        $this->email = $data['Email'] ?? $data['email'] ?? null;
        $this->password = $data['Password'] ?? $data['password'] ?? null;
        $this->rememberMe = $data['RememberMe'] ?? $data['rememberMe'] ?? $data['remember_me'] ?? false;
    }

    // Convertir a array para respuestas JSON (sin incluir password por seguridad)
    public function toArray($includePassword = false) {
        $loginData = [
            'email' => $this->email,
            'rememberMe' => (bool)$this->rememberMe
        ];

        // Solo incluir password si se solicita explícitamente (para operaciones internas)
        if ($includePassword) {
            $loginData['password'] = $this->password;
        }

        return $loginData;
    }

    // Convertir a array para operaciones con base de datos (formato ASP.NET)
    public function toDatabaseArray() {
        return [
            'Email' => $this->email,
            'Password' => $this->password,
            'RememberMe' => $this->rememberMe ? 1 : 0
        ];
    }

    // Validar datos requeridos para login (según validaciones de ASP.NET)
    public function isValid() {
        $errors = $this->getValidationErrors();
        return empty($errors);
    }

    // Obtener errores de validación específicos (mensajes similares a ASP.NET)
    public function getValidationErrors() {
        $errors = [];

        // Validación de Email (Required + EmailAddress)
        if (empty($this->email)) {
            $errors[] = "El Campo Email es Obligatorio";
        } elseif (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "El formato del Email no es válido";
        }

        // Validación de Password (Required)
        if (empty($this->password)) {
            $errors[] = "El Campo Contraseña es Obligatorio";
        } elseif (strlen($this->password) < 6) {
            // Validación adicional de longitud mínima para seguridad
            $errors[] = "La contraseña debe tener al menos 6 caracteres";
        }

        return $errors;
    }

    // Sanitizar datos de entrada
    public function sanitizeInput() {
        if (!empty($this->email)) {
            // Sanitizar email pero preservar formato
            $this->email = filter_var(trim(strtolower($this->email)), FILTER_SANITIZE_EMAIL);
        }
        
        // No sanitizar password para preservar caracteres especiales
        if (!empty($this->password)) {
            $this->password = trim($this->password);
        }
    }

    // Sanitizar específicamente el email (método esperado por AuthController)
    public function sanitizeEmail() {
        if (!empty($this->email)) {
            $this->email = filter_var(trim(strtolower($this->email)), FILTER_SANITIZE_EMAIL);
        }
    }

    // Verificar si las credenciales están completas
    public function hasCredentials() {
        return !empty($this->email) && !empty($this->password);
    }

    // Obtener datos para logging (sin password)
    public function getLogData() {
        return [
            'email' => $this->email,
            'rememberMe' => $this->rememberMe,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
}
?>
