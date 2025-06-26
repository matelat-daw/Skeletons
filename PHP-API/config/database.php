<?php
class Database {
    private $host = "localhost";
    private $db_name = "clients";
    private $username = "root";
    private $password;
    private $conn;

    public function __construct() {
        // Obtener la contraseña desde la variable de entorno
        $this->password = getenv('MySQL') ?: $_ENV['MySQL'] ?: '';
        
        // Si no se encuentra la variable de entorno, mostrar advertencia
        if (empty($this->password)) {
            error_log("Advertencia: Variable de entorno 'MySQL' no encontrada. Usando contraseña vacía.");
        }
    }

    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            error_log("Error de conexión a la base de datos: " . $exception->getMessage());
            
            // En desarrollo, mostrar el error. En producción, usar un mensaje genérico
            if (getenv('APP_ENV') === 'development') {
                echo "Error de conexión: " . $exception->getMessage();
            } else {
                echo "Error de conexión a la base de datos. Por favor, inténtelo más tarde.";
            }
        }

        return $this->conn;
    }
}
?>
