<?php
class User {
    private $conn;
    private $table_name = "AspNetUsers"; // Tabla de ASP.NET Identity

    // Propiedades del usuario
    public $id;
    public $nick;
    public $email;
    public $password;
    public $name;
    public $surname1;
    public $surname2;  // Agregar esta propiedad
    public $created_at;
    public $is_verified;

    // Propiedades adicionales para el perfil
    public $phone_number;
    public $profile_image;
    public $birthday;
    public $about;
    public $user_location;
    public $public_profile;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Buscar usuario por email para login
    public function findByEmail($email) {
        $query = "SELECT Id, UserName, Email, PasswordHash, EmailConfirmed 
                  FROM " . $this->table_name . " 
                  WHERE Email = :email";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id = $row['Id'];
            $this->nick = $row['Nick'];
            $this->email = $row['Email'];
            $this->password = $row['PasswordHash'];
            $this->name = $row['Name']; // ASP.NET usa UserName como nombre
            $this->surname1 = $row['Surname1'];
            $this->surname1 = $row['Surname2'];
            $this->created_at = ''; // Si necesitas este campo, agrégalo a la consulta
            $this->is_verified = $row['EmailConfirmed'];
            return true;
        }

        return false;
    }

    // Verificar contraseña
    public function verifyPassword($password) {
        // Si el password hash está vacío, no permitir login
        if (empty($this->password)) {
            return false;
        }
        
        // Verificar contraseña usando el formato de ASP.NET Identity
        return $this->verifyAspNetIdentityPassword($password, $this->password);
    }
    
    // Verificar contraseña con formato ASP.NET Identity (basado en código Java)
    private function verifyAspNetIdentityPassword($password, $hashedPassword) {
        try {
            // El hash de ASP.NET Identity está en Base64
            $hashBytes = base64_decode($hashedPassword);
            
            if ($hashBytes === false || strlen($hashBytes) < 61) {
                return false;
            }
            
            $format = ord($hashBytes[0]);
            if ($format !== 0x01) {
                return false; // Solo soportamos formato 0x01
            }
            
            $prf = unpack('N', substr($hashBytes, 1, 4))[1];
            $iterations = unpack('N', substr($hashBytes, 5, 4))[1];
            $saltLen = unpack('N', substr($hashBytes, 9, 4))[1];
            
            // Validar que sea el formato esperado
            if ($prf !== 2 || $saltLen !== 16) {
                return false;
            }
            
            // Extraer salt y subkey
            $salt = substr($hashBytes, 13, 16);
            $expectedSubkey = substr($hashBytes, 29, 32);
            
            // Generar hash de la contraseña proporcionada usando PBKDF2 con SHA512
            $actualSubkey = hash_pbkdf2('sha512', $password, $salt, $iterations, 32, true);
            
            // Comparar subkeys de forma segura
            return hash_equals($expectedSubkey, $actualSubkey);
            
        } catch (Exception $e) {
            error_log("Error verificando contraseña ASP.NET Identity: " . $e->getMessage());
            return false;
        }
    }

    // Verificar si el email ya existe
    public function emailExists($email) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE Email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    // Verificar si el username ya existe
    public function usernameExists($username) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE UserName = :username";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }
    
    // Generar hash de contraseña compatible con ASP.NET Identity
    public function hashPasswordAspNetIdentity($password) {
        $prf = 2; // 2 = HMACSHA512
        $iterCount = 10000;
        $saltLen = 16;
        $subKeyLen = 32;

        $salt = random_bytes($saltLen);
        $subKey = hash_pbkdf2('sha512', $password, $salt, $iterCount, $subKeyLen, true);

        $buffer = pack('C', 0x01) .
                  pack('N', $prf) .
                  pack('N', $iterCount) .
                  pack('N', $saltLen) .
                  $salt .
                  $subKey;

        return base64_encode($buffer);
    }

    // Buscar usuario por email para perfil completo
    public function findProfileByEmail($email) {
        // Consulta única que incluye todos los campos existentes en la tabla
        $query = "SELECT Id, UserName, Email, PasswordHash, EmailConfirmed, 
                         Nick, Name, Surname1, Surname2, PhoneNumber, 
                         ProfileImage, Bday, About, UserLocation, PublicProfile 
                  FROM " . $this->table_name . " 
                  WHERE Email = :email";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id = $row['Id'];
            $this->nick = $row['Nick'] ?? $row['UserName']; // Usar Nick si existe, sino UserName
            $this->email = $row['Email'];
            $this->password = $row['PasswordHash'];
            $this->is_verified = $row['EmailConfirmed'];
            
            // Mapear todos los campos del perfil con los nombres correctos de la tabla
            $this->name = $row['Name'] ?? '';
            $this->surname1 = $row['Surname1'] ?? '';
            $this->surname2 = $row['Surname2'] ?? '';
            $this->phone_number = $row['PhoneNumber'] ?? '';
            $this->profile_image = $row['ProfileImage'] ?? '';
            $this->birthday = $row['Bday'] ?? '';  // Campo correcto: Bday, no Birthday
            $this->about = $row['About'] ?? '';
            $this->user_location = $row['UserLocation'] ?? '';
            $this->public_profile = $row['PublicProfile'] ?? true;
            
            return true;
        }

        return false;
    }
}
?>