<?php
class User {
    // Propiedades del usuario
    public $id;
    public $nick;
    public $email;
    public $password_hash;
    public $name;
    public $surname1;
    public $surname2;
    public $phone_number;
    public $profile_image;
    public $birthday;
    public $about;
    public $user_location;
    public $public_profile;
    public $email_confirmed;
    public $created_at;

    // Constructor para inicializar propiedades desde array
    public function __construct($data = []) {
        if (!empty($data)) {
            $this->fillFromArray($data);
        }
    }

    // Llenar propiedades desde array de datos
    public function fillFromArray($data) {
        $this->id = $data['Id'] ?? $data['id'] ?? null;
        $this->nick = $data['Nick'] ?? $data['nick'] ?? $data['UserName'] ?? null;
        $this->email = $data['Email'] ?? $data['email'] ?? null;
        $this->password_hash = $data['PasswordHash'] ?? $data['password_hash'] ?? null;
        $this->name = $data['Name'] ?? $data['name'] ?? null;
        $this->surname1 = $data['Surname1'] ?? $data['surname1'] ?? null;
        $this->surname2 = $data['Surname2'] ?? $data['surname2'] ?? null;
        $this->phone_number = $data['PhoneNumber'] ?? $data['phone_number'] ?? null;
        $this->profile_image = $data['ProfileImage'] ?? $data['profile_image'] ?? null;
        $this->birthday = $data['Bday'] ?? $data['birthday'] ?? null;
        $this->about = $data['About'] ?? $data['about'] ?? null;
        $this->user_location = $data['UserLocation'] ?? $data['user_location'] ?? null;
        $this->public_profile = $data['PublicProfile'] ?? $data['public_profile'] ?? true;
        $this->email_confirmed = $data['EmailConfirmed'] ?? $data['email_confirmed'] ?? false;
        $this->created_at = $data['created_at'] ?? null;
    }

    // Convertir a array para respuestas JSON
    public function toArray() {
        return [
            'id' => $this->id,
            'nick' => $this->nick,
            'email' => $this->email,
            'name' => $this->name,
            'surname1' => $this->surname1,
            'surname2' => $this->surname2,
            'phoneNumber' => $this->phone_number,
            'profileImage' => $this->profile_image,
            'bday' => $this->birthday,
            'about' => $this->about,
            'userLocation' => $this->user_location,
            'publicProfile' => (bool)$this->public_profile,
            'emailConfirmed' => (bool)$this->email_confirmed
        ];
    }

    // Validar datos requeridos
    public function isValid() {
        return !empty($this->email) && !empty($this->nick);
    }
}
?>