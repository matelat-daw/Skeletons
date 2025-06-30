<?php
class Register {
    // Propiedades del modelo de registro
    public $id;
    public $nick;
    public $name;
    public $surname1;
    public $surname2;
    public $email;
    public $password;
    public $password2;
    public $phone_number;
    public $bday;
    public $profile_image_file;
    public $about;
    public $user_location;
    public $public_profile;

    // Constructor para inicializar propiedades desde array
    public function __construct($data = []) {
        if (!empty($data)) {
            $this->fillFromArray($data);
        }
    }

    // Llenar propiedades desde array de datos
    public function fillFromArray($data) {
        $this->id = $data['id'] ?? $data['Id'] ?? null;
        $this->nick = $data['nick'] ?? $data['Nick'] ?? null;
        $this->name = $data['name'] ?? $data['Name'] ?? null;
        $this->surname1 = $data['surname1'] ?? $data['Surname1'] ?? null;
        $this->surname2 = $data['surname2'] ?? $data['Surname2'] ?? null;
        $this->email = $data['email'] ?? $data['Email'] ?? null;
        $this->password = $data['password'] ?? $data['Password'] ?? null;
        $this->password2 = $data['password2'] ?? $data['Password2'] ?? null;
        $this->phone_number = $data['phoneNumber'] ?? $data['phone_number'] ?? $data['PhoneNumber'] ?? null;
        $this->bday = $data['bday'] ?? $data['Bday'] ?? null;
        $this->profile_image_file = $data['profileImageFile'] ?? $data['profile_image_file'] ?? $data['ProfileImageFile'] ?? null;
        $this->about = $data['about'] ?? $data['About'] ?? null;
        $this->user_location = $data['userLocation'] ?? $data['user_location'] ?? $data['UserLocation'] ?? null;
        $this->public_profile = $data['publicProfile'] ?? $data['public_profile'] ?? $data['PublicProfile'] ?? true;
    }

    // Convertir a array para respuestas JSON (sin incluir passwords por seguridad)
    public function toArray() {
        return [
            'id' => $this->id,
            'nick' => $this->nick,
            'name' => $this->name,
            'surname1' => $this->surname1,
            'surname2' => $this->surname2,
            'email' => $this->email,
            'phoneNumber' => $this->phone_number,
            'bday' => $this->bday,
            'about' => $this->about,
            'userLocation' => $this->user_location,
            'publicProfile' => (bool)$this->public_profile
        ];
    }

    // Validar datos requeridos para registro
    public function isValid() {
        $errors = $this->getValidationErrors();
        return empty($errors);
    }

    // Obtener errores de validación específicos
    public function getValidationErrors() {
        $errors = [];

        // Campo Nick obligatorio
        if (empty($this->nick)) {
            $errors[] = "El campo Username es obligatorio";
        } elseif (strlen($this->nick) < 3) {
            $errors[] = "El Username debe tener al menos 3 caracteres";
        } elseif (strlen($this->nick) > 50) {
            $errors[] = "El Username no puede tener más de 50 caracteres";
        } elseif (!preg_match('/^[a-zA-Z0-9_-]+$/', $this->nick)) {
            $errors[] = "El Username solo puede contener letras, números, guiones y guiones bajos";
        }

        // Campo Name obligatorio
        if (empty($this->name)) {
            $errors[] = "El campo Nombre es obligatorio";
        } elseif (strlen($this->name) < 2) {
            $errors[] = "El Nombre debe tener al menos 2 caracteres";
        } elseif (strlen($this->name) > 100) {
            $errors[] = "El Nombre no puede tener más de 100 caracteres";
        }

        // Campo Surname1 obligatorio
        if (empty($this->surname1)) {
            $errors[] = "El campo Apellido 1 es obligatorio";
        } elseif (strlen($this->surname1) < 2) {
            $errors[] = "El Apellido 1 debe tener al menos 2 caracteres";
        } elseif (strlen($this->surname1) > 100) {
            $errors[] = "El Apellido 1 no puede tener más de 100 caracteres";
        }

        // Campo Surname2 opcional pero validar si está presente
        if (!empty($this->surname2) && strlen($this->surname2) > 100) {
            $errors[] = "El Apellido 2 no puede tener más de 100 caracteres";
        }

        // Campo Email obligatorio
        if (empty($this->email)) {
            $errors[] = "El campo E-mail es obligatorio";
        } elseif (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "El formato del E-mail no es válido";
        } elseif (strlen($this->email) > 256) {
            $errors[] = "El E-mail no puede tener más de 256 caracteres";
        }

        // Campo Password opcional pero validar si está presente
        if (!empty($this->password)) {
            if (strlen($this->password) < 6) {
                $errors[] = "La Contraseña debe tener al menos 6 caracteres";
            } elseif (strlen($this->password) > 100) {
                $errors[] = "La Contraseña no puede tener más de 100 caracteres";
            }

            // Validar confirmación de contraseña
            if ($this->password !== $this->password2) {
                $errors[] = "Las Contraseñas no Coinciden, Por Favor Escribelas de Nuevo";
            }
        }

        // Campo PhoneNumber opcional pero validar formato si está presente
        if (!empty($this->phone_number)) {
            if (!preg_match('/^\+?[0-9\s\-\(\)]{9,20}$/', $this->phone_number)) {
                $errors[] = "El formato del Teléfono no es válido";
            }
        }

        // Campo Bday opcional pero validar formato si está presente
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

        // Campo About opcional pero validar longitud si está presente
        if (!empty($this->about) && strlen($this->about) > 500) {
            $errors[] = "El campo Sobre Mí no puede tener más de 500 caracteres";
        }

        // Campo UserLocation opcional pero validar longitud si está presente
        if (!empty($this->user_location) && strlen($this->user_location) > 200) {
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
            $this->email = filter_var(trim($this->email), FILTER_SANITIZE_EMAIL);
        }
        
        if (!empty($this->phone_number)) {
            $this->phone_number = trim($this->phone_number);
        }
        
        if (!empty($this->about)) {
            $this->about = trim($this->about);
        }
        
        if (!empty($this->user_location)) {
            $this->user_location = trim($this->user_location);
        }
    }

    // Generar ID único si no existe
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

    // Convertir a modelo User para inserción en BD
    public function toUser() {
        require_once __DIR__ . '/User.php';
        
        $userData = [
            'Id' => $this->id,
            'Nick' => $this->nick,
            'UserName' => $this->nick, // ASP.NET usa UserName
            'Email' => $this->email,
            'Name' => $this->name,
            'Surname1' => $this->surname1,
            'Surname2' => $this->surname2,
            'PhoneNumber' => $this->phone_number,
            'Bday' => $this->bday,
            'About' => $this->about,
            'UserLocation' => $this->user_location,
            'PublicProfile' => $this->public_profile,
            'EmailConfirmed' => false // Por defecto no confirmado
        ];
        
        return new User($userData);
    }
}
?>
