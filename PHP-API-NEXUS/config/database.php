<?php
// Cargar variables de entorno
require_once __DIR__ . '/env.php';

class Database {
    private $server;
    private $username;
    private $password;
    private $connections = []; // Array para almacenar múltiples conexiones

    public function __construct() {
        // Obtener configuración desde variables de entorno
        $host = $_ENV['SQLSERVER_HOST'] ?? '88.24.26.59';
        $port = $_ENV['SQLSERVER_PORT'] ?? '1433';
        
        $this->server = $host . ',' . $port;
        $this->username = $_ENV['SQLSERVER_USER'] ?? 'sa';
        $this->password = $_ENV['SQLSERVER_PASSWORD'] ?? 'Anubis@68';
        
        if (empty($this->password)) {
            error_log("Advertencia: Variable de entorno 'SQLSERVER_PASSWORD' no encontrada.");
        }
    }

    public function getConnection($database = null) {
        // Usar base de datos por defecto si no se especifica
        if ($database === null) {
            $database = $_ENV['SQLSERVER_DATABASE'] ?? 'NexusUsers';
        }
        
        // Mapear nombres de bases de datos
        $databaseMap = [
            'NexusUsers' => 'NexusUsers',
            'nexus_stars' => 'nexus_stars',
            'Stars' => 'nexus_stars', // Alias para compatibilidad
            'Constellations' => 'nexus_stars' // Alias para compatibilidad
        ];
        
        // Usar el mapeo si existe, sino usar el nombre directamente
        $dbName = $databaseMap[$database] ?? $database;
        
        // Si ya existe una conexión para esta base de datos, devolverla
        if (isset($this->connections[$dbName])) {
            return $this->connections[$dbName];
        }
        
        try {
            // Configuración de PDO para SQL Server
            $dsn = "sqlsrv:Server={$this->server};Database={$dbName};TrustServerCertificate=true;ConnectRetryCount=3;ConnectRetryInterval=10";
            
            // Configurar opciones de PDO básicas para SQL Server
            $options = array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            );
            
            // Agregar opciones específicas de SQL Server si están disponibles
            if (defined('PDO::SQLSRV_ATTR_ENCODING')) {
                $options[PDO::SQLSRV_ATTR_ENCODING] = PDO::SQLSRV_ENCODING_UTF8;
            }
            
            $this->connections[$dbName] = new PDO(
                $dsn,
                $this->username,
                $this->password,
                $options
            );
            
        } catch(PDOException $exception) {
            error_log("Error de conexión a SQL Server (DB: {$dbName}): " . $exception->getMessage());
            
            // En desarrollo, mostrar el error. En producción, usar un mensaje genérico
            if ($_ENV['ENVIRONMENT'] === 'development' || getenv('APP_ENV') === 'development') {
                throw new Exception("Error de conexión a {$dbName}: " . $exception->getMessage());
            } else {
                throw new Exception("Error de conexión a la base de datos. Por favor, inténtelo más tarde.");
            }
        }

        return $this->connections[$dbName];
    }
}
?>
