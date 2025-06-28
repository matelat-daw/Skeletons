<?php
class User {
    private $conn;
    private $table_name = "Users"; // Ajustar según tu tabla

    // Propiedades del usuario
    public $id;
    public $nick;
    public $email;
    public $password;
    public $name;
    public $surname1;
    public $created_at;
    public $is_verified;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Buscar usuario por email para login
    public function findByEmail($email) {
        $query = "SELECT id, nick, email, password, name, surname1, created_at, is_verified 
                  FROM " . $this->table_name . " 
                  WHERE email = :email";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id = $row['id'];
            $this->nick = $row['nick'];
            $this->email = $row['email'];
            $this->password = $row['password'];
            $this->name = $row['name'];
            $this->surname1 = $row['surname1'];
            $this->created_at = $row['created_at'];
            $this->is_verified = $row['is_verified'];
            return true;
        }

        return false;
    }

    // Verificar contraseña
    public function verifyPassword($password) {
        return password_verify($password, $this->password);
    }

    // Crear nuevo usuario (para registro)
    public function create($nick, $email, $password, $name, $surname1) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (nick, email, password, name, surname1, created_at, is_verified) 
                  VALUES (:nick, :email, :password, :name, :surname1, GETDATE(), 0)";

        $stmt = $this->conn->prepare($query);

        // Hash de la contraseña
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Bind de valores
        $stmt->bindParam(":nick", $nick);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":password", $hashedPassword);
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":surname1", $surname1);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    // Verificar si el email ya existe
    public function emailExists($email) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    // Verificar si el nick ya existe
    public function nickExists($nick) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE nick = :nick";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":nick", $nick);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }
}
?>
