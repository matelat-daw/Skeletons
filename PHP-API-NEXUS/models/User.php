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
            $this->nick = $row['UserName'];
            $this->email = $row['Email'];
            $this->password = $row['PasswordHash'];
            $this->name = $row['UserName']; // ASP.NET usa UserName como nombre
            $this->surname1 = ''; // ASP.NET no tiene apellido por defecto
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
            
            // Formato basado en el código Java:
            // Byte 0: 0x01 (formato)
            // Bytes 1-4: PRF (2 = HMACSHA512)
            // Bytes 5-8: Iteraciones
            // Bytes 9-12: Salt length
            // Bytes 13-28: Salt (16 bytes)
            // Bytes 29-60: Subkey (32 bytes)
            
            $format = ord($hashBytes[0]);
            if ($format !== 0x01) {
                return false; // Solo soportamos formato 0x01
            }
            
            // Extraer valores (big-endian como en Java)
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
        // Consulta básica que siempre debe funcionar
        $baseQuery = "SELECT Id, UserName, Email, PasswordHash, EmailConfirmed FROM " . $this->table_name . " WHERE Email = :email";
        
        $stmt = $this->conn->prepare($baseQuery);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id = $row['Id'];
            $this->nick = $row['UserName'];
            $this->email = $row['Email'];
            $this->password = $row['PasswordHash'];
            $this->name = $row['UserName']; // ASP.NET usa UserName como nombre
            $this->surname1 = ''; // ASP.NET no tiene apellido por defecto
            $this->is_verified = $row['EmailConfirmed'];
            
            // Intentar obtener campos adicionales del perfil si existen
            try {
                $extendedQuery = "SELECT PhoneNumber, ProfileImage, Birthday, About, UserLocation, PublicProfile 
                                 FROM " . $this->table_name . " WHERE Id = :userId";
                $extendedStmt = $this->conn->prepare($extendedQuery);
                $extendedStmt->bindParam(":userId", $this->id);
                $extendedStmt->execute();
                
                $extendedRow = $extendedStmt->fetch(PDO::FETCH_ASSOC);
                if ($extendedRow) {
                    $this->phone_number = $extendedRow['PhoneNumber'] ?? '';
                    $this->profile_image = $extendedRow['ProfileImage'] ?? '';
                    $this->birthday = $extendedRow['Birthday'] ?? '';
                    $this->about = $extendedRow['About'] ?? '';
                    $this->user_location = $extendedRow['UserLocation'] ?? '';
                    $this->public_profile = $extendedRow['PublicProfile'] ?? true;
                }
            } catch (Exception $e) {
                // Campos adicionales no existen, usar valores por defecto
                $this->phone_number = '';
                $this->profile_image = '';
                $this->birthday = '';
                $this->about = '';
                $this->user_location = '';
                $this->public_profile = true;
            }
            
            return true;
        }

        return false;
    }
}
?>