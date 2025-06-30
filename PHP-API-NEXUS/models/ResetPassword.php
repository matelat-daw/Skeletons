<?php
/**
 * Modelo ResetPassword - Compatible con ASP.NET (NexusAstralis.Models.User.ResetPassword)
 * Representa los datos para resetear la contraseña después del enlace de recuperación
 */
class ResetPassword {
    // Propiedades del modelo de reseteo de contraseña (coinciden exactamente con ASP.NET)
    public $email;                  // Email (string?) - Required, DataType.EmailAddress, Display("E-mail")
    public $password;               // Password (string?) - Required, DataType.Password, Display("Nueva contraseña")
    public $password2;              // Password2 (string?) - DataType.Password, Compare("Password"), Display("Confirmar contraseña")
    public $token;                  // Token (string?) - Token de reseteo

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
        $this->password2 = $data['Password2'] ?? $data['password2'] ?? $data['confirmPassword'] ?? $data['ConfirmPassword'] ?? null;
        $this->token = $data['Token'] ?? $data['token'] ?? $data['code'] ?? null;
    }

    // Convertir a array para respuestas JSON (sin incluir passwords por seguridad)
    public function toArray($includePasswords = false) {
        $resetData = [
            'email' => $this->email,
            'token' => $this->token
        ];

        // Solo incluir passwords si se solicita explícitamente (para operaciones internas)
        if ($includePasswords) {
            $resetData['password'] = $this->password;
            $resetData['password2'] = $this->password2;
        }

        return $resetData;
    }

    // Convertir a array para base de datos (formato ASP.NET)
    public function toDatabaseArray() {
        return [
            'Email' => $this->email,
            'Password' => $this->password,
            'Password2' => $this->password2,
            'Token' => $this->token
        ];
    }

    // Validar datos requeridos según las restricciones de ASP.NET
    public function isValid() {
        $errors = $this->getValidationErrors();
        return empty($errors);
    }

    // Obtener errores de validación específicos (mensajes idénticos a ASP.NET)
    public function getValidationErrors() {
        $errors = [];

        // Campo Email (Required + EmailAddress)
        if (empty($this->email)) {
            $errors[] = "El campo E-mail es obligatorio";
        } elseif (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "El formato del E-mail no es válido";
        } elseif (strlen($this->email) > 256) {
            $errors[] = "El E-mail no puede tener más de 256 caracteres";
        }

        // Campo Password (Required)
        if (empty($this->password)) {
            $errors[] = "El campo Nueva contraseña es obligatorio";
        } elseif (strlen($this->password) < 6) {
            $errors[] = "La Nueva contraseña debe tener al menos 6 caracteres";
        } elseif (strlen($this->password) > 100) {
            $errors[] = "La Nueva contraseña no puede tener más de 100 caracteres";
        }

        // Campo Password2 (Compare con Password)
        if ($this->password !== $this->password2) {
            $errors[] = "Las contraseñas no coinciden";
        }

        // Campo Token (no required según tu modelo, pero importante para seguridad)
        if (empty($this->token)) {
            $errors[] = "El token de reseteo es requerido";
        }

        return $errors;
    }

    // Sanitizar campos
    public function sanitizeFields() {
        if (!empty($this->email)) {
            $this->email = filter_var(trim(strtolower($this->email)), FILTER_SANITIZE_EMAIL);
        }
        
        if (!empty($this->token)) {
            $this->token = trim($this->token);
        }

        // No sanitizar passwords para preservar caracteres especiales
        if (!empty($this->password)) {
            $this->password = trim($this->password);
        }
        
        if (!empty($this->password2)) {
            $this->password2 = trim($this->password2);
        }
    }

    // Verificar si tiene los campos requeridos
    public function hasRequiredFields() {
        return !empty($this->email) && 
               !empty($this->password) && 
               !empty($this->token);
    }

    // Obtener datos para logging (sin passwords)
    public function getLogData() {
        return [
            'email' => $this->email,
            'token' => substr($this->token, 0, 10) . '...', // Solo primeros caracteres por seguridad
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
}
?>
