<?php
/**
 * Modelo ForgotPassword - Compatible con ASP.NET (NexusAstralis.Models.User.ForgotPassword)
 * Representa la solicitud de recuperación de contraseña
 */
class ForgotPassword {
    // Propiedades del modelo de recuperación de contraseña (coinciden exactamente con ASP.NET)
    public $email;                  // Email (string?) - Required, DataType.EmailAddress, Display("E-mail")

    // Constructor para inicializar propiedades desde array
    public function __construct($data = []) {
        if (!empty($data)) {
            $this->fillFromArray($data);
        }
    }

    // Llenar propiedades desde array de datos (compatible con ASP.NET y PHP)
    public function fillFromArray($data) {
        $this->email = $data['Email'] ?? $data['email'] ?? null;
    }

    // Convertir a array para respuestas JSON
    public function toArray() {
        return [
            'email' => $this->email
        ];
    }

    // Convertir a array para base de datos (formato ASP.NET)
    public function toDatabaseArray() {
        return [
            'Email' => $this->email
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
            $errors[] = "El Campo E-mail es Obligatorio";
        } elseif (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "El formato del E-mail no es válido";
        } elseif (strlen($this->email) > 256) {
            $errors[] = "El E-mail no puede tener más de 256 caracteres";
        }

        return $errors;
    }

    // Sanitizar el campo de email
    public function sanitizeFields() {
        if (!empty($this->email)) {
            $this->email = filter_var(trim(strtolower($this->email)), FILTER_SANITIZE_EMAIL);
        }
    }

    // Verificar si tiene el campo requerido
    public function hasRequiredFields() {
        return !empty($this->email);
    }

    // Obtener datos para logging
    public function getLogData() {
        return [
            'email' => $this->email,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
}
?>
