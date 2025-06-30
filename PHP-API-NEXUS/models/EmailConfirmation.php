<?php
class EmailConfirmation {
    private $conn;
    private $table_name = "EmailConfirmationTokens"; // Tabla para tokens de confirmación

    // Propiedades
    public $user_id;
    public $email;
    public $token;
    public $expires_at;
    public $created_at;
    public $confirmed_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Generar token único de confirmación
    public function generateToken($userId, $email) {
        $this->user_id = $userId;
        $this->email = $email;
        $this->token = bin2hex(random_bytes(32)); // Token seguro de 64 caracteres
        $this->expires_at = date('Y-m-d H:i:s', strtotime('+24 hours')); // Válido 24 horas
        $this->created_at = date('Y-m-d H:i:s');
        
        return $this->token;
    }

    // Guardar token en base de datos
    public function saveToken() {
        // Primero eliminar tokens anteriores del mismo usuario
        $this->deleteUserTokens();
        
        $query = "INSERT INTO " . $this->table_name . " 
                  (UserId, Email, Token, ExpiresAt, CreatedAt) 
                  VALUES (:user_id, :email, :token, :expires_at, :created_at)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":token", $this->token);
        $stmt->bindParam(":expires_at", $this->expires_at);
        $stmt->bindParam(":created_at", $this->created_at);

        return $stmt->execute();
    }

    // Buscar token válido
    public function findValidToken($token) {
        $query = "SELECT UserId, Email, Token, ExpiresAt, CreatedAt, ConfirmedAt 
                  FROM " . $this->table_name . " 
                  WHERE Token = :token AND ExpiresAt > GETDATE() AND ConfirmedAt IS NULL";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":token", $token);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->user_id = $row['UserId'];
            $this->email = $row['Email'];
            $this->token = $row['Token'];
            $this->expires_at = $row['ExpiresAt'];
            $this->created_at = $row['CreatedAt'];
            $this->confirmed_at = $row['ConfirmedAt'];
            return true;
        }

        return false;
    }

    // Marcar token como confirmado
    public function confirmToken($token) {
        $query = "UPDATE " . $this->table_name . " 
                  SET ConfirmedAt = GETDATE() 
                  WHERE Token = :token AND ConfirmedAt IS NULL";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":token", $token);
        
        return $stmt->execute();
    }

    // Eliminar tokens anteriores del usuario
    private function deleteUserTokens() {
        $query = "DELETE FROM " . $this->table_name . " WHERE UserId = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id);
        return $stmt->execute();
    }

    // Limpiar tokens expirados (mantenimiento)
    public function cleanupExpiredTokens() {
        $query = "DELETE FROM " . $this->table_name . " WHERE ExpiresAt < GETDATE()";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute();
    }
}
?>
