<?php
/**
 * Modelo UserInfoDto - Compatible con ASP.NET (NexusAstralis.Models.User.UserInfoDto)
 * Representa un DTO con información del usuario para respuestas del API
 */
class UserInfoDto {
    // Propiedades del DTO de información de usuario (coinciden exactamente con ASP.NET)
    public $nick;                   // Nick (string?)
    public $name;                   // Name (string?)
    public $surname1;               // Surname1 (string?)
    public $surname2;               // Surname2 (string?)
    public $email;                  // Email (string?)
    public $phoneNumber;            // PhoneNumber (string?)
    public $profileImage;           // ProfileImage (string?)
    public $bday;                   // Bday (DateOnly)
    public $about;                  // About (string?)
    public $userLocation;           // UserLocation (string?)
    public $publicProfile;          // PublicProfile (bool)
    public $comments;               // Comments (Object?)
    public $favorites;              // Favorites (ICollection<Constellations>?)

    // Constructor para inicializar propiedades desde array
    public function __construct($data = []) {
        if (!empty($data)) {
            $this->fillFromArray($data);
        }
    }

    // Llenar propiedades desde array de datos (compatible con ASP.NET y PHP)
    public function fillFromArray($data) {
        $this->nick = $data['Nick'] ?? $data['nick'] ?? null;
        $this->name = $data['Name'] ?? $data['name'] ?? null;
        $this->surname1 = $data['Surname1'] ?? $data['surname1'] ?? null;
        $this->surname2 = $data['Surname2'] ?? $data['surname2'] ?? null;
        $this->email = $data['Email'] ?? $data['email'] ?? null;
        $this->phoneNumber = $data['PhoneNumber'] ?? $data['phoneNumber'] ?? $data['phone_number'] ?? null;
        $this->profileImage = $data['ProfileImage'] ?? $data['profileImage'] ?? $data['profile_image'] ?? null;
        $this->bday = $data['Bday'] ?? $data['bday'] ?? $data['birthday'] ?? null;
        $this->about = $data['About'] ?? $data['about'] ?? null;
        $this->userLocation = $data['UserLocation'] ?? $data['userLocation'] ?? $data['user_location'] ?? null;
        $this->publicProfile = $data['PublicProfile'] ?? $data['publicProfile'] ?? $data['public_profile'] ?? false;
        $this->comments = $data['Comments'] ?? $data['comments'] ?? null;
        $this->favorites = $data['Favorites'] ?? $data['favorites'] ?? [];
    }

    // Convertir a array para respuestas JSON (formato compatible con Angular/Frontend)
    public function toArray() {
        return [
            'nick' => $this->nick,
            'name' => $this->name,
            'surname1' => $this->surname1,
            'surname2' => $this->surname2,
            'email' => $this->email,
            'phoneNumber' => $this->phoneNumber,
            'profileImage' => $this->profileImage,
            'bday' => $this->bday,
            'about' => $this->about,
            'userLocation' => $this->userLocation,
            'publicProfile' => (bool)$this->publicProfile,
            'comments' => $this->comments,
            'favorites' => $this->favorites
        ];
    }

    // Convertir a array para base de datos (formato ASP.NET)
    public function toDatabaseArray() {
        return [
            'Nick' => $this->nick,
            'Name' => $this->name,
            'Surname1' => $this->surname1,
            'Surname2' => $this->surname2,
            'Email' => $this->email,
            'PhoneNumber' => $this->phoneNumber,
            'ProfileImage' => $this->profileImage,
            'Bday' => $this->bday,
            'About' => $this->about,
            'UserLocation' => $this->userLocation,
            'PublicProfile' => $this->publicProfile ? 1 : 0,
            'Comments' => $this->comments,
            'Favorites' => $this->favorites
        ];
    }

    // Crear DTO desde modelo User
    public static function fromUser($user) {
        if (is_array($user)) {
            return new self($user);
        } elseif (is_object($user) && method_exists($user, 'toArray')) {
            return new self($user->toArray());
        }
        return new self();
    }

    // Crear DTO desde modelo User con comentarios y favoritos
    public static function fromUserWithRelations($user, $comments = null, $favorites = []) {
        $dto = self::fromUser($user);
        $dto->comments = $comments;
        $dto->favorites = $favorites;
        return $dto;
    }

    // Obtener nombre completo
    public function getFullName() {
        $parts = array_filter([$this->name, $this->surname1, $this->surname2]);
        return implode(' ', $parts);
    }

    // Verificar si el perfil es público
    public function isPublicProfile() {
        return (bool)$this->publicProfile;
    }

    // Obtener información básica (solo datos públicos)
    public function getPublicInfo() {
        if (!$this->isPublicProfile()) {
            return [
                'nick' => $this->nick,
                'profileImage' => $this->profileImage,
                'publicProfile' => false
            ];
        }

        return $this->toArray();
    }

    // Obtener información de contacto
    public function getContactInfo() {
        return [
            'email' => $this->email,
            'phoneNumber' => $this->phoneNumber,
            'userLocation' => $this->userLocation
        ];
    }

    // Obtener estadísticas del usuario
    public function getStats() {
        $stats = [
            'totalComments' => 0,
            'totalFavorites' => 0
        ];

        if (is_array($this->comments)) {
            $stats['totalComments'] = count($this->comments);
        } elseif (is_object($this->comments) && isset($this->comments->count)) {
            $stats['totalComments'] = $this->comments->count;
        }

        if (is_array($this->favorites)) {
            $stats['totalFavorites'] = count($this->favorites);
        }

        return $stats;
    }

    // Verificar si tiene imagen de perfil
    public function hasProfileImage() {
        return !empty($this->profileImage);
    }

    // Obtener URL de imagen de perfil o imagen por defecto
    public function getProfileImageUrl($defaultUrl = '/assets/images/default-avatar.png') {
        return $this->hasProfileImage() ? $this->profileImage : $defaultUrl;
    }

    // Validar que los datos básicos estén presentes
    public function isValid() {
        return !empty($this->nick) && !empty($this->email);
    }

    // Obtener datos para logging (sin información sensible)
    public function getLogData() {
        return [
            'nick' => $this->nick,
            'email' => $this->email,
            'publicProfile' => $this->publicProfile,
            'hasComments' => !empty($this->comments),
            'favoritesCount' => is_array($this->favorites) ? count($this->favorites) : 0,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
}
?>
