<?php
require_once __DIR__ . '/User.php';

class UserRepository {
    private $conn;
    private $table_name = "AspNetUsers";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Buscar usuario por email
    public function findByEmail($email, $fullProfile = false) {
        if ($fullProfile) {
            // Consulta completa para perfil
            $query = "SELECT Id, UserName, Email, PasswordHash, EmailConfirmed, 
                             Nick, Name, Surname1, Surname2, PhoneNumber, 
                             ProfileImage, Bday, About, UserLocation, PublicProfile 
                      FROM " . $this->table_name . " 
                      WHERE Email = :email";
        } else {
            // Consulta básica para login
            $query = "SELECT Id, UserName, Email, PasswordHash, EmailConfirmed, Nick
                      FROM " . $this->table_name . " 
                      WHERE Email = :email";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return new User($row);
        }

        return null;
    }

    // Buscar usuario por ID
    public function findById($id) {
        $query = "SELECT Id, UserName, Email, PasswordHash, EmailConfirmed, 
                         Nick, Name, Surname1, Surname2, PhoneNumber, 
                         ProfileImage, Bday, About, UserLocation, PublicProfile 
                  FROM " . $this->table_name . " 
                  WHERE Id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return new User($row);
        }

        return null;
    }

    // Buscar usuario por nick
    public function findByNick($nick) {
        $query = "SELECT Id, UserName, Email, PasswordHash, EmailConfirmed, 
                         Nick, Name, Surname1, Surname2, PhoneNumber, 
                         ProfileImage, Bday, About, UserLocation, PublicProfile 
                  FROM " . $this->table_name . " 
                  WHERE Nick = :nick";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":nick", $nick);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return new User($row);
        }

        return null;
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

    // Verificar si el nick ya existe
    public function nickExists($nick) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE Nick = :nick";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":nick", $nick);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    // Crear nuevo usuario
    public function create(User $user) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (Id, UserName, Email, PasswordHash, EmailConfirmed, Nick, Name, Surname1, Surname2, 
                   PhoneNumber, ProfileImage, Bday, About, UserLocation, PublicProfile, 
                   AccessFailedCount, LockoutEnabled, TwoFactorEnabled, PhoneNumberConfirmed, SecurityStamp) 
                  VALUES (:id, :username, :email, :password_hash, :email_confirmed, :nick, :name, 
                          :surname1, :surname2, :phone_number, :profile_image, :birthday, :about, 
                          :user_location, :public_profile, :access_failed_count, :lockout_enabled, 
                          :two_factor_enabled, :phone_number_confirmed, :security_stamp)";

        $stmt = $this->conn->prepare($query);
        
        // Generar ID único si no existe
        if (empty($user->id)) {
            $user->id = $this->generateGuid();
        }

        // Valores por defecto para campos obligatorios de ASP.NET Identity
        $access_failed_count = 0;
        $lockout_enabled = true;
        $two_factor_enabled = false;
        $phone_number_confirmed = false;
        $security_stamp = $this->generateGuid(); // Security stamp único
        
        // Si Bday está vacío, usar una fecha por defecto
        $birthday = $user->birthday ?: '1900-01-01';

        $stmt->bindParam(":id", $user->id);
        $stmt->bindParam(":username", $user->nick);
        $stmt->bindParam(":email", $user->email);
        $stmt->bindParam(":password_hash", $user->password_hash);
        $stmt->bindParam(":email_confirmed", $user->email_confirmed);
        $stmt->bindParam(":nick", $user->nick);
        $stmt->bindParam(":name", $user->name);
        $stmt->bindParam(":surname1", $user->surname1);
        $stmt->bindParam(":surname2", $user->surname2);
        $stmt->bindParam(":phone_number", $user->phone_number);
        $stmt->bindParam(":profile_image", $user->profile_image);
        $stmt->bindParam(":birthday", $birthday);
        $stmt->bindParam(":about", $user->about);
        $stmt->bindParam(":user_location", $user->user_location);
        $stmt->bindParam(":public_profile", $user->public_profile);
        $stmt->bindParam(":access_failed_count", $access_failed_count);
        $stmt->bindParam(":lockout_enabled", $lockout_enabled);
        $stmt->bindParam(":two_factor_enabled", $two_factor_enabled);
        $stmt->bindParam(":phone_number_confirmed", $phone_number_confirmed);
        $stmt->bindParam(":security_stamp", $security_stamp);

        return $stmt->execute();
    }

    // Actualizar usuario
    public function update(User $user) {
        $query = "UPDATE " . $this->table_name . " 
                  SET Nick = :nick, Name = :name, Surname1 = :surname1, Surname2 = :surname2,
                      PhoneNumber = :phone_number, ProfileImage = :profile_image, Bday = :birthday,
                      About = :about, UserLocation = :user_location, PublicProfile = :public_profile,
                      EmailConfirmed = :email_confirmed
                  WHERE Id = :id";

        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":id", $user->id);
        $stmt->bindParam(":nick", $user->nick);
        $stmt->bindParam(":name", $user->name);
        $stmt->bindParam(":surname1", $user->surname1);
        $stmt->bindParam(":surname2", $user->surname2);
        $stmt->bindParam(":phone_number", $user->phone_number);
        $stmt->bindParam(":profile_image", $user->profile_image);
        $stmt->bindParam(":birthday", $user->birthday);
        $stmt->bindParam(":about", $user->about);
        $stmt->bindParam(":user_location", $user->user_location);
        $stmt->bindParam(":public_profile", $user->public_profile);
        $stmt->bindParam(":email_confirmed", $user->email_confirmed);

        return $stmt->execute();
    }

    // Eliminar usuario
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE Id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    // Obtener todos los usuarios (con paginación)
    public function getAll($limit = 50, $offset = 0) {
        $query = "SELECT Id, UserName, Email, EmailConfirmed, Nick, Name, Surname1, Surname2,
                         PhoneNumber, ProfileImage, Bday, About, UserLocation, PublicProfile 
                  FROM " . $this->table_name . " 
                  ORDER BY Nick
                  OFFSET :offset ROWS FETCH NEXT :limit ROWS ONLY";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();

        $users = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = new User($row);
        }

        return $users;
    }

    // Generar GUID compatible con ASP.NET
    private function generateGuid() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
?>
