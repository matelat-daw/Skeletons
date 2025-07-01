<?php
/**
 * Modelo Register - Compatible con ASP.NET (NexusAstralis.Models.User.Register)
 * Representa los datos de registro de usuario con validaciones
 */
class Register {
    // Propiedades del modelo de registro (coinciden exactamente con ASP.NET)
    public $id;                     // Id (String?)
    public $nick;                   // Nick (string?) - Required, Display("Username")
    public $name;                   // Name (string?) - Required, Display("Nombre")
    public $surname1;               // Surname1 (string?) - Required, Display("Apellido 1")
    public $surname2;               // Surname2 (string?) - Optional, Display("Apellido 2")
    public $email;                  // Email (string?) - Required, DataType.EmailAddress, Display("E-mail")
    public $password;               // Password (string?) - DataType.Password, Display("Contraseña")
    public $password2;              // Password2 (string?) - DataType.Password, Compare("Password"), Display("Repite contraseña")
    public $phoneNumber;            // PhoneNumber (string?) - Display("Teléfono")
    public $bday;                   // Bday (DateOnly) - DataType.Date, Display("Fecha de Nacimiento")
    public $profileImageFile;       // ProfileImageFile (IFormFile?) - Display("Foto de Perfil")
    public $about;                  // About (string?) - Display("Sobre Mí")
    public $userLocation;           // UserLocation (string?) - Display("Localización")
    public $publicProfile;          // PublicProfile (string?) - Display("Perfil Público?")

    // Constructor para inicializar propiedades desde array
    public function __construct($data = []) {
        if (!empty($data)) {
            $this->fillFromArray($data);
        }
    }

    // Llenar propiedades desde array de datos (compatible con ASP.NET y PHP)
    public function fillFromArray($data) {
        $this->id = $data['Id'] ?? $data['id'] ?? null;
        $this->nick = $data['Nick'] ?? $data['nick'] ?? null;
        $this->name = $data['Name'] ?? $data['name'] ?? null;
        $this->surname1 = $data['Surname1'] ?? $data['surname1'] ?? null;
        $this->surname2 = $data['Surname2'] ?? $data['surname2'] ?? null;
        $this->email = $data['Email'] ?? $data['email'] ?? null;
        $this->password = $data['Password'] ?? $data['password'] ?? null;
        $this->password2 = $data['Password2'] ?? $data['password2'] ?? $data['confirmPassword'] ?? $data['ConfirmPassword'] ?? null;
        $this->phoneNumber = $data['PhoneNumber'] ?? $data['phoneNumber'] ?? $data['phone_number'] ?? null;
        $this->bday = $data['Bday'] ?? $data['bday'] ?? $data['birthdate'] ?? null;
        $this->profileImageFile = $data['ProfileImageFile'] ?? $data['profileImageFile'] ?? $data['profile_image_file'] ?? null;
        $this->about = $data['About'] ?? $data['about'] ?? null;
        $this->userLocation = $data['UserLocation'] ?? $data['userLocation'] ?? $data['user_location'] ?? null;
        $this->publicProfile = $data['PublicProfile'] ?? $data['publicProfile'] ?? $data['public_profile'] ?? null;
    }

    // Convertir a array para respuestas JSON (sin incluir passwords por seguridad)
    public function toArray($includePasswords = false) {
        $registerData = [
            'id' => $this->id,
            'nick' => $this->nick,
            'name' => $this->name,
            'surname1' => $this->surname1,
            'surname2' => $this->surname2,
            'email' => $this->email,
            'phoneNumber' => $this->phoneNumber,
            'bday' => $this->bday,
            'about' => $this->about,
            'userLocation' => $this->userLocation,
            'publicProfile' => $this->publicProfile
        ];

        // Solo incluir passwords si se solicita explícitamente (para operaciones internas)
        if ($includePasswords) {
            $registerData['password'] = $this->password;
            $registerData['password2'] = $this->password2;
        }

        return $registerData;
    }

    // Convertir a array para base de datos (formato ASP.NET)
    public function toDatabaseArray() {
        return [
            'Id' => $this->id,
            'Nick' => $this->nick,
            'Name' => $this->name,
            'Surname1' => $this->surname1,
            'Surname2' => $this->surname2,
            'Email' => $this->email,
            'Password' => $this->password,
            'Password2' => $this->password2,
            'PhoneNumber' => $this->phoneNumber,
            'Bday' => $this->bday,
            'ProfileImageFile' => $this->profileImageFile,
            'About' => $this->about,
            'UserLocation' => $this->userLocation,
            'PublicProfile' => $this->publicProfile
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

        // Campo Nick/Username (Required)
        if (empty($this->nick)) {
            $errors[] = "El campo Username es obligatorio.";
        } elseif (strlen($this->nick) < 3) {
            $errors[] = "El Username debe tener al menos 3 caracteres";
        } elseif (strlen($this->nick) > 50) {
            $errors[] = "El Username no puede tener más de 50 caracteres";
        }
        // Nota: En ASP.NET Identity, Username puede ser cualquier string válido, 
        // no solo letras/números/guiones. Removida restricción de regex.

        // Campo Name (Required)
        if (empty($this->name)) {
            $errors[] = "El campo Nombre es obligatorio.";
        } elseif (strlen($this->name) < 2) {
            $errors[] = "El Nombre debe tener al menos 2 caracteres";
        } elseif (strlen($this->name) > 100) {
            $errors[] = "El Nombre no puede tener más de 100 caracteres";
        }

        // Campo Surname1 (Required)
        if (empty($this->surname1)) {
            $errors[] = "El campo Apellido 1 es obligatorio.";
        } elseif (strlen($this->surname1) < 2) {
            $errors[] = "El Apellido 1 debe tener al menos 2 caracteres";
        } elseif (strlen($this->surname1) > 100) {
            $errors[] = "El Apellido 1 no puede tener más de 100 caracteres";
        }

        // Campo Surname2 (Optional)
        if (!empty($this->surname2) && strlen($this->surname2) > 100) {
            $errors[] = "El Apellido 2 no puede tener más de 100 caracteres";
        }

        // Campo Email (Required + EmailAddress)
        if (empty($this->email)) {
            $errors[] = "El campo E-mail es obligatorio.";
        } elseif (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "El formato del E-mail no es válido";
        } elseif (strlen($this->email) > 256) {
            $errors[] = "El E-mail no puede tener más de 256 caracteres";
        }

        // Campo Password (Optional pero validar si está presente)
        if (!empty($this->password)) {
            if (strlen($this->password) < 6) {
                $errors[] = "La Contraseña debe tener al menos 6 caracteres";
            } elseif (strlen($this->password) > 100) {
                $errors[] = "La Contraseña no puede tener más de 100 caracteres";
            }

            // Validar Compare("Password")
            if ($this->password !== $this->password2) {
                $errors[] = "Las Contraseñas no Coinciden, Por Favor Escribelas de Nuevo.";
            }
        }

        // Campo PhoneNumber (Optional)
        if (!empty($this->phoneNumber)) {
            if (!preg_match('/^\+?[0-9\s\-\(\)]{9,20}$/', $this->phoneNumber)) {
                $errors[] = "El formato del Teléfono no es válido";
            }
        }

        // Campo Bday (DataType.Date)
        if (!empty($this->bday)) {
            $date = DateTime::createFromFormat('Y-m-d', $this->bday);
            if (!$date || $date->format('Y-m-d') !== $this->bday) {
                $errors[] = "El formato de la Fecha de Nacimiento debe ser YYYY-MM-DD";
            } else {
                // Validar que la fecha no sea futura
                $today = new DateTime();
                if ($date > $today) {
                    $errors[] = "La Fecha de Nacimiento no puede ser futura";
                }
                
                // Validar edad mínima (13 años)
                $minAge = new DateTime();
                $minAge->sub(new DateInterval('P13Y'));
                if ($date > $minAge) {
                    $errors[] = "Debes tener al menos 13 años para registrarte";
                }
            }
        }

        // Campo About (Optional)
        if (!empty($this->about) && strlen($this->about) > 500) {
            $errors[] = "El campo Sobre Mí no puede tener más de 500 caracteres";
        }

        // Campo UserLocation (Optional)
        if (!empty($this->userLocation) && strlen($this->userLocation) > 200) {
            $errors[] = "El campo Localización no puede tener más de 200 caracteres";
        }

        return $errors;
    }

    // Sanitizar todos los campos de texto
    public function sanitizeFields() {
        if (!empty($this->nick)) {
            $this->nick = trim($this->nick);
        }
        
        if (!empty($this->name)) {
            $this->name = trim($this->name);
        }
        
        if (!empty($this->surname1)) {
            $this->surname1 = trim($this->surname1);
        }
        
        if (!empty($this->surname2)) {
            $this->surname2 = trim($this->surname2);
        }
        
        if (!empty($this->email)) {
            $this->email = filter_var(trim(strtolower($this->email)), FILTER_SANITIZE_EMAIL);
        }
        
        if (!empty($this->phoneNumber)) {
            $this->phoneNumber = trim($this->phoneNumber);
        }
        
        if (!empty($this->about)) {
            $this->about = trim($this->about);
        }
        
        if (!empty($this->userLocation)) {
            $this->userLocation = trim($this->userLocation);
        }

        // No sanitizar passwords para preservar caracteres especiales
        if (!empty($this->password)) {
            $this->password = trim($this->password);
        }
        
        if (!empty($this->password2)) {
            $this->password2 = trim($this->password2);
        }
    }

    // Generar ID único si no existe (compatible con ASP.NET Identity)
    public function generateId() {
        if (empty($this->id)) {
            $this->id = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            );
        }
    }

    // Convertir a modelo User para inserción en BD (compatible con ASP.NET Identity)
    public function toUser() {
        require_once __DIR__ . '/User.php';
        
        $userData = [
            'Id' => $this->id,
            'Nick' => $this->nick,
            'UserName' => $this->email, // ASP.NET Identity usa Email como UserName
            'Email' => $this->email,
            'NormalizedEmail' => strtoupper($this->email),
            'NormalizedUserName' => strtoupper($this->email),
            'Name' => $this->name,
            'Surname1' => $this->surname1,
            'Surname2' => $this->surname2,
            'PhoneNumber' => $this->phoneNumber,
            'Bday' => $this->bday,
            'ProfileImage' => !empty($this->profileImageFile) ? $this->profileImageFile : 'https://88.24.26.59/imgs/default-profile.jpg',
            'About' => $this->about,
            'UserLocation' => $this->userLocation,
            'PublicProfile' => $this->publicProfile === 'true' || $this->publicProfile === true,
            'EmailConfirmed' => false, // Por defecto no confirmado
            'PhoneNumberConfirmed' => false,
            'TwoFactorEnabled' => false,
            'LockoutEnabled' => true,
            'AccessFailedCount' => 0
        ];
        
        return new User($userData);
    }

    // Verificar si tiene los campos mínimos requeridos
    public function hasRequiredFields() {
        return !empty($this->nick) && 
               !empty($this->name) && 
               !empty($this->surname1) && 
               !empty($this->email);
    }

    // Obtener datos para logging (sin passwords)
    public function getLogData() {
        return [
            'nick' => $this->nick,
            'email' => $this->email,
            'name' => $this->name,
            'surname1' => $this->surname1,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
}
?>
