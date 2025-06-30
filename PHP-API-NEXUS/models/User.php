<?php
/**
 * Modelo User - Compatible con ASP.NET Identity (NexusUser)
 * Representa un usuario del sistema con todos los campos de IdentityUser
 */
class User {
    // Propiedades de IdentityUser (ASP.NET Identity)
    public $id;                      // Id (nvarchar(450))
    public $userName;                // UserName (varchar(255))
    public $normalizedUserName;      // NormalizedUserName (nvarchar(256))
    public $email;                   // Email (varchar(255))
    public $normalizedEmail;         // NormalizedEmail (nvarchar(256))
    public $emailConfirmed;          // EmailConfirmed (bit)
    public $passwordHash;            // PasswordHash (varchar(255))
    public $securityStamp;           // SecurityStamp (varchar(255))
    public $concurrencyStamp;        // ConcurrencyStamp (varchar(255))
    public $phoneNumber;             // PhoneNumber (varchar(255))
    public $phoneNumberConfirmed;    // PhoneNumberConfirmed (bit)
    public $twoFactorEnabled;        // TwoFactorEnabled (bit)
    public $lockoutEnd;              // LockoutEnd (date)
    public $lockoutEnabled;          // LockoutEnabled (bit)
    public $accessFailedCount;       // AccessFailedCount (int)
    
    // Propiedades específicas de NexusUser
    public $nick;                    // Nick (nvarchar(20)) - Requerido
    public $name;                    // Name (varchar(255))
    public $surname1;                // Surname1 (varchar(255))
    public $surname2;                // Surname2 (varchar(255))
    public $bday;                    // Bday (date) - Requerido
    public $profileImage;            // ProfileImage (varchar(255))
    public $about;                   // About (varchar(255))
    public $userLocation;            // UserLocation (varchar(255))
    public $publicProfile;           // PublicProfile (bit) - Requerido

    // Constructor para inicializar propiedades desde array
    public function __construct($data = []) {
        if (!empty($data)) {
            $this->fillFromArray($data);
        }
    }

    // Llenar propiedades desde array de datos (compatible con ASP.NET y PHP)
    public function fillFromArray($data) {
        // Campos de IdentityUser
        $this->id = $data['Id'] ?? $data['id'] ?? null;
        $this->userName = $data['UserName'] ?? $data['userName'] ?? $data['user_name'] ?? null;
        $this->normalizedUserName = $data['NormalizedUserName'] ?? $data['normalizedUserName'] ?? null;
        $this->email = $data['Email'] ?? $data['email'] ?? null;
        $this->normalizedEmail = $data['NormalizedEmail'] ?? $data['normalizedEmail'] ?? null;
        $this->emailConfirmed = $data['EmailConfirmed'] ?? $data['emailConfirmed'] ?? false;
        $this->passwordHash = $data['PasswordHash'] ?? $data['passwordHash'] ?? $data['password_hash'] ?? null;
        $this->securityStamp = $data['SecurityStamp'] ?? $data['securityStamp'] ?? null;
        $this->concurrencyStamp = $data['ConcurrencyStamp'] ?? $data['concurrencyStamp'] ?? null;
        $this->phoneNumber = $data['PhoneNumber'] ?? $data['phoneNumber'] ?? $data['phone_number'] ?? null;
        $this->phoneNumberConfirmed = $data['PhoneNumberConfirmed'] ?? $data['phoneNumberConfirmed'] ?? false;
        $this->twoFactorEnabled = $data['TwoFactorEnabled'] ?? $data['twoFactorEnabled'] ?? false;
        $this->lockoutEnd = $data['LockoutEnd'] ?? $data['lockoutEnd'] ?? null;
        $this->lockoutEnabled = $data['LockoutEnabled'] ?? $data['lockoutEnabled'] ?? false;
        $this->accessFailedCount = $data['AccessFailedCount'] ?? $data['accessFailedCount'] ?? 0;
        
        // Campos específicos de NexusUser
        $this->nick = $data['Nick'] ?? $data['nick'] ?? null;
        $this->name = $data['Name'] ?? $data['name'] ?? null;
        $this->surname1 = $data['Surname1'] ?? $data['surname1'] ?? null;
        $this->surname2 = $data['Surname2'] ?? $data['surname2'] ?? null;
        $this->bday = $data['Bday'] ?? $data['bday'] ?? $data['birthday'] ?? null;
        $this->profileImage = $data['ProfileImage'] ?? $data['profileImage'] ?? $data['profile_image'] ?? null;
        $this->about = $data['About'] ?? $data['about'] ?? null;
        $this->userLocation = $data['UserLocation'] ?? $data['userLocation'] ?? $data['user_location'] ?? null;
        $this->publicProfile = $data['PublicProfile'] ?? $data['publicProfile'] ?? $data['public_profile'] ?? true;
    }

    // Convertir a array para respuestas JSON (formato Angular/Frontend)
    public function toArray($includeSecure = false) {
        $userData = [
            'id' => $this->id,
            'nick' => $this->nick,
            'userName' => $this->userName,
            'email' => $this->email,
            'emailConfirmed' => (bool)$this->emailConfirmed,
            'name' => $this->name,
            'surname1' => $this->surname1,
            'surname2' => $this->surname2,
            'phoneNumber' => $this->phoneNumber,
            'phoneNumberConfirmed' => (bool)$this->phoneNumberConfirmed,
            'profileImage' => $this->profileImage,
            'bday' => $this->bday,
            'about' => $this->about,
            'userLocation' => $this->userLocation,
            'publicProfile' => (bool)$this->publicProfile,
            'twoFactorEnabled' => (bool)$this->twoFactorEnabled,
            'lockoutEnabled' => (bool)$this->lockoutEnabled,
            'accessFailedCount' => (int)$this->accessFailedCount
        ];

        // Incluir campos seguros solo si se solicita (para operaciones internas)
        if ($includeSecure) {
            $userData['passwordHash'] = $this->passwordHash;
            $userData['securityStamp'] = $this->securityStamp;
            $userData['concurrencyStamp'] = $this->concurrencyStamp;
            $userData['normalizedUserName'] = $this->normalizedUserName;
            $userData['normalizedEmail'] = $this->normalizedEmail;
            $userData['lockoutEnd'] = $this->lockoutEnd;
        }

        return $userData;
    }

    // Convertir a array para base de datos (formato ASP.NET Identity)
    public function toDatabaseArray() {
        return [
            'Id' => $this->id,
            'Nick' => $this->nick,
            'UserName' => $this->userName,
            'NormalizedUserName' => $this->normalizedUserName,
            'Email' => $this->email,
            'NormalizedEmail' => $this->normalizedEmail,
            'EmailConfirmed' => $this->emailConfirmed ? 1 : 0,
            'PasswordHash' => $this->passwordHash,
            'SecurityStamp' => $this->securityStamp,
            'ConcurrencyStamp' => $this->concurrencyStamp,
            'PhoneNumber' => $this->phoneNumber,
            'PhoneNumberConfirmed' => $this->phoneNumberConfirmed ? 1 : 0,
            'TwoFactorEnabled' => $this->twoFactorEnabled ? 1 : 0,
            'LockoutEnd' => $this->lockoutEnd,
            'LockoutEnabled' => $this->lockoutEnabled ? 1 : 0,
            'AccessFailedCount' => (int)$this->accessFailedCount,
            'Name' => $this->name,
            'Surname1' => $this->surname1,
            'Surname2' => $this->surname2,
            'Bday' => $this->bday,
            'ProfileImage' => $this->profileImage,
            'About' => $this->about,
            'UserLocation' => $this->userLocation,
            'PublicProfile' => $this->publicProfile ? 1 : 0
        ];
    }

    // Validar datos requeridos según las restricciones de ASP.NET
    public function isValid($checkPassword = false) {
        $errors = [];

        // Campos requeridos
        if (empty($this->nick)) {
            $errors[] = "Nick es obligatorio";
        } elseif (strlen($this->nick) < 3 || strlen($this->nick) > 20) {
            $errors[] = "Nick debe tener entre 3 y 20 caracteres";
        }

        if (empty($this->email)) {
            $errors[] = "Email es obligatorio";
        } elseif (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Email no es válido";
        }

        if (empty($this->bday)) {
            $errors[] = "Fecha de nacimiento es obligatoria";
        }

        // Validar nombre si se proporciona
        if (!empty($this->name) && (strlen($this->name) < 3 || strlen($this->name) > 24)) {
            $errors[] = "Nombre debe tener entre 3 y 24 caracteres";
        }

        // Validar apellidos si se proporcionan
        if (!empty($this->surname1) && (strlen($this->surname1) < 3 || strlen($this->surname1) > 24)) {
            $errors[] = "Primer apellido debe tener entre 3 y 24 caracteres";
        }

        if (!empty($this->surname2) && (strlen($this->surname2) < 3 || strlen($this->surname2) > 24)) {
            $errors[] = "Segundo apellido debe tener entre 3 y 24 caracteres";
        }

        // Validar password si se requiere
        if ($checkPassword && empty($this->passwordHash)) {
            $errors[] = "Password es obligatorio";
        }

        return empty($errors) ? true : $errors;
    }

    // Obtener nombre completo
    public function getFullName() {
        $parts = array_filter([$this->name, $this->surname1, $this->surname2]);
        return implode(' ', $parts);
    }

    // Verificar si el usuario está bloqueado
    public function isLockedOut() {
        if (!$this->lockoutEnabled) {
            return false;
        }
        
        if ($this->lockoutEnd === null) {
            return false;
        }
        
        return strtotime($this->lockoutEnd) > time();
    }
}
?>