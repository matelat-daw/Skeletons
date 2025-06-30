<?php
class Login {
    // Propiedades del modelo de login
    public $email;
    public $password;
    public $remember_me;

    // Constructor para inicializar propiedades desde array
    public function __construct($data = []) {
        if (!empty($data)) {
            $this->fillFromArray($data);
        }
    }

    // Llenar propiedades desde array de datos
    public function fillFromArray($data) {
        $this->email = $data['email'] ?? $data['Email'] ?? null;
        $this->password = $data['password'] ?? $data['Password'] ?? null;
        $this->remember_me = $data['rememberMe'] ?? $data['remember_me'] ?? $data['RememberMe'] ?? false;
    }

    // Convertir a array para respuestas JSON (sin incluir password por seguridad)
    public function toArray() {
        return [
            'email' => $this->email,
            'rememberMe' => (bool)$this->remember_me
        ];
    }

    // Validar datos requeridos para login
    public function isValid() {
        if (empty($this->email) || empty($this->password)) {
            return false;
        }

        // Validar formato de email
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        // Validar longitud mínima de contraseña
        if (strlen($this->password) < 6) {
            return false;
        }

        return true;
    }

    // Obtener errores de validación específicos
    public function getValidationErrors() {
        $errors = [];

        if (empty($this->email)) {
            $errors[] = "El campo Email es obligatorio";
        } elseif (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "El formato del Email no es válido";
        }

        if (empty($this->password)) {
            $errors[] = "El campo Contraseña es obligatorio";
        } elseif (strlen($this->password) < 6) {
            $errors[] = "La contraseña debe tener al menos 6 caracteres";
        }

        return $errors;
    }

    // Sanitizar email (el password no se sanitiza para preservar caracteres especiales)
    public function sanitizeEmail() {
        if (!empty($this->email)) {
            $this->email = filter_var(trim($this->email), FILTER_SANITIZE_EMAIL);
        }
    }
}
?>
