<?php
class Database {
    private $server = "88.24.26.59,1433";
    private $database = "NexusUsers";
    private $username;
    private $password;
    private $conn;

    public function __construct() {
        // Obtener credenciales desde variables de entorno
        $this->username = getenv('SQLSERVER_USER') ?: $_ENV['SQLSERVER_USER'] ?: 'sa';
        $this->password = getenv('SQLSERVER_PASSWORD') ?: $_ENV['MySQL'] ?: '';
        
        if (empty($this->password)) {
            error_log("Advertencia: Variable de entorno 'SQLSERVER_PASSWORD' no encontrada.");
        }
    }

    public function getConnection() {
        $this->conn = null;
        
        try {
            // Configuración de PDO para SQL Server
            $dsn = "sqlsrv:Server={$this->server};Database={$this->database};TrustServerCertificate=true";
            
            $this->conn = new PDO(
                $dsn,
                $this->username,
                $this->password,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::SQLSRV_ATTR_ENCODING => PDO::SQLSRV_ENCODING_UTF8
                )
            );
            
        } catch(PDOException $exception) {
            error_log("Error de conexión a SQL Server: " . $exception->getMessage());
            
            // En desarrollo, mostrar el error. En producción, usar un mensaje genérico
            if (getenv('APP_ENV') === 'development') {
                throw new Exception("Error de conexión: " . $exception->getMessage());
            } else {
                throw new Exception("Error de conexión a la base de datos. Por favor, inténtelo más tarde.");
            }
        }

        return $this->conn;
    }
}
?>
