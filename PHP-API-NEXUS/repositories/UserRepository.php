<?php
require_once __DIR__ . '/../models/User.php';

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

    // Verificar si el email ya existe para otro usuario (excluye el usuario actual)
    public function emailExistsForOtherUser($email, $currentUserId) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE Email = :email AND Id != :current_user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":current_user_id", $currentUserId);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    // Verificar si el nick ya existe para otro usuario (excluye el usuario actual)
    public function nickExistsForOtherUser($nick, $currentUserId) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE Nick = :nick AND Id != :current_user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":nick", $nick);
        $stmt->bindParam(":current_user_id", $currentUserId);
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
        $birthday = $user->bday ?: '1900-01-01';

        $stmt->bindParam(":id", $user->id);
        $stmt->bindParam(":username", $user->userName);
        $stmt->bindParam(":email", $user->email);
        $stmt->bindParam(":password_hash", $user->passwordHash);
        
        // Convertir boolean a entero para campo bit en BD
        $emailConfirmedInt = $user->emailConfirmed ? 1 : 0;
        $stmt->bindParam(":email_confirmed", $emailConfirmedInt, PDO::PARAM_INT);
        
        $stmt->bindParam(":nick", $user->nick);
        $stmt->bindParam(":name", $user->name);
        $stmt->bindParam(":surname1", $user->surname1);
        $stmt->bindParam(":surname2", $user->surname2);
        $stmt->bindParam(":phone_number", $user->phoneNumber);
        $stmt->bindParam(":profile_image", $user->profileImage);
        $stmt->bindParam(":birthday", $birthday);
        $stmt->bindParam(":about", $user->about);
        $stmt->bindParam(":user_location", $user->userLocation);
        
        // Convertir boolean a entero para campo bit en BD
        $publicProfileInt = $user->publicProfile ? 1 : 0;
        $stmt->bindParam(":public_profile", $publicProfileInt, PDO::PARAM_INT);
        
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
        $stmt->bindParam(":phone_number", $user->phoneNumber);
        $stmt->bindParam(":profile_image", $user->profileImage);
        
        // Manejar fecha de nacimiento (puede ser null)
        $bdayValue = $user->bday ?: null;
        $stmt->bindParam(":birthday", $bdayValue);
        
        $stmt->bindParam(":about", $user->about);
        $stmt->bindParam(":user_location", $user->userLocation);
        
        // Convertir explícitamente boolean a entero para campo bit en BD
        $publicProfileInt = $user->publicProfile ? 1 : 0;
        $stmt->bindParam(":public_profile", $publicProfileInt, PDO::PARAM_INT);
        
        // Convertir boolean a entero para EmailConfirmed también
        $emailConfirmedInt = $user->emailConfirmed ? 1 : 0;
        $stmt->bindParam(":email_confirmed", $emailConfirmedInt, PDO::PARAM_INT);

        try {
            error_log("UserRepository UPDATE: About to execute query for user ID: " . $user->id);
            error_log("UserRepository UPDATE: Query values - Name: " . $user->name . ", Nick: " . $user->nick . ", Bday: " . ($bdayValue ?? 'null'));
            
            $result = $stmt->execute();
            
            error_log("UserRepository UPDATE: Query execution result: " . ($result ? 'TRUE' : 'FALSE'));
            
            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                error_log("UserRepository UPDATE ERROR: " . json_encode($errorInfo));
                error_log("UserRepository UPDATE ERROR Details - SQLSTATE: " . $errorInfo[0] . ", Code: " . $errorInfo[1] . ", Message: " . $errorInfo[2]);
                return false;
            } else {
                $rowCount = $stmt->rowCount();
                error_log("UserRepository UPDATE: Rows affected: " . $rowCount);
                
                // Considerar exitoso si la query se ejecutó correctamente, independientemente del rowCount
                // rowCount = 0 puede significar que no hubo cambios, lo cual no es un error
                if ($rowCount === 0) {
                    error_log("UserRepository UPDATE INFO: No rows were updated (no changes detected or user not found)");
                    
                    // Verificar si el usuario existe para distinguir entre "no cambios" y "no existe"
                    $checkQuery = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE Id = :id";
                    $checkStmt = $this->conn->prepare($checkQuery);
                    $checkStmt->bindParam(":id", $user->id);
                    $checkStmt->execute();
                    $userExists = $checkStmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;
                    
                    if (!$userExists) {
                        error_log("UserRepository UPDATE ERROR: User with ID " . $user->id . " does not exist");
                        return false;
                    } else {
                        error_log("UserRepository UPDATE INFO: User exists, but no changes were needed");
                        return true; // No hay cambios, pero es exitoso
                    }
                }
                
                return true;
            }
        } catch (PDOException $e) {
            error_log("UserRepository UPDATE EXCEPTION: " . $e->getMessage());
            error_log("UserRepository UPDATE EXCEPTION Code: " . $e->getCode());
            error_log("UserRepository UPDATE EXCEPTION File: " . $e->getFile() . " Line: " . $e->getLine());
            return false;
        }
    }

    // Actualizar email del usuario
    public function updateEmail($userId, $email) {
        $query = "UPDATE " . $this->table_name . " SET Email = :email, EmailConfirmed = 0 WHERE Id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":id", $userId);
        return $stmt->execute();
    }

    // Actualizar contraseña del usuario
    public function updatePassword($userId, $passwordHash) {
        $query = "UPDATE " . $this->table_name . " SET PasswordHash = :password_hash, SecurityStamp = :security_stamp WHERE Id = :id";
        $stmt = $this->conn->prepare($query);
        $newSecurityStamp = $this->generateGuid();
        $stmt->bindParam(":password_hash", $passwordHash);
        $stmt->bindParam(":security_stamp", $newSecurityStamp);
        $stmt->bindParam(":id", $userId);
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

    /**
     * Buscar login externo de un usuario
     * Compatible con ASP.NET Identity AspNetUserLogins
     */
    public function findExternalLogin($userId, $provider) {
        $query = "SELECT LoginProvider, ProviderKey, ProviderDisplayName, UserId
                  FROM AspNetUserLogins 
                  WHERE UserId = :userId AND LoginProvider = :provider";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":userId", $userId);
        $stmt->bindParam(":provider", $provider);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Agregar login externo para un usuario
     * Compatible con ASP.NET Identity AspNetUserLogins
     */
    public function addExternalLogin($externalLoginData) {
        $query = "INSERT INTO AspNetUserLogins 
                  (LoginProvider, ProviderKey, ProviderDisplayName, UserId) 
                  VALUES (:provider, :providerKey, :displayName, :userId)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":provider", $externalLoginData['login_provider']);
        $stmt->bindParam(":providerKey", $externalLoginData['provider_key']);
        $stmt->bindParam(":displayName", $externalLoginData['provider_display_name']);
        $stmt->bindParam(":userId", $externalLoginData['user_id']);
        
        return $stmt->execute();
    }

    /**
     * Buscar usuario por login externo
     * Compatible con ASP.NET Identity
     */
    public function findByExternalLogin($provider, $providerKey) {
        $query = "SELECT u.Id, u.UserName, u.Email, u.PasswordHash, u.EmailConfirmed, 
                         u.Nick, u.Name, u.Surname1, u.Surname2, u.PhoneNumber, 
                         u.ProfileImage, u.Bday, u.About, u.UserLocation, u.PublicProfile
                  FROM " . $this->table_name . " u
                  INNER JOIN AspNetUserLogins ul ON u.Id = ul.UserId
                  WHERE ul.LoginProvider = :provider AND ul.ProviderKey = :providerKey";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":provider", $provider);
        $stmt->bindParam(":providerKey", $providerKey);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            return new User($row);
        }
        
        return null;
    }

    /**
     * Eliminar login externo de un usuario
     * Compatible con ASP.NET Identity
     */
    public function removeExternalLogin($userId, $provider, $providerKey) {
        $query = "DELETE FROM AspNetUserLogins 
                  WHERE UserId = :userId AND LoginProvider = :provider AND ProviderKey = :providerKey";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":userId", $userId);
        $stmt->bindParam(":provider", $provider);
        $stmt->bindParam(":providerKey", $providerKey);
        
        return $stmt->execute();
    }

    /**
     * Obtener todos los logins externos de un usuario
     * Compatible con ASP.NET Identity
     */
    public function getExternalLogins($userId) {
        $query = "SELECT LoginProvider, ProviderKey, ProviderDisplayName
                  FROM AspNetUserLogins 
                  WHERE UserId = :userId
                  ORDER BY LoginProvider";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":userId", $userId);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
