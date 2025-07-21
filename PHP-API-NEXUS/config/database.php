<?php
/**
 * Configuración de Base de Datos para Economía Circular Canarias
 * Conexión a MySQL con manejo de errores y configuración segura
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'users';  // Base de datos específica para usuarios
    private $username = 'root';  // Cambiar por tu usuario MySQL
    private $password = '';      // Cambiar por tu contraseña MySQL
    private $port = 3306;
    private $charset = 'utf8mb4';
    private $conn;

    /**
     * Constructor - Establece conexión automáticamente
     */
    public function __construct() {
        $this->getConnection();
    }

    /**
     * Establece conexión PDO con MySQL
     * @return PDO|null
     */
    public function getConnection() {
        if ($this->conn === null) {
            try {
                $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset={$this->charset}";
                
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ];

                $this->conn = new PDO($dsn, $this->username, $this->password, $options);
                
                // Log successful connection
                error_log("✅ Conexión exitosa a la base de datos: {$this->db_name}");
                
            } catch (PDOException $e) {
                error_log("❌ Error de conexión a la base de datos: " . $e->getMessage());
                
                // En desarrollo, mostrar error detallado
                if ($_ENV['ENVIRONMENT'] === 'development') {
                    throw new Exception("Error de conexión: " . $e->getMessage());
                } else {
                    throw new Exception("Error de conexión a la base de datos");
                }
            }
        }

        return $this->conn;
    }

    /**
     * Verifica si la conexión está activa
     * @return bool
     */
    public function isConnected() {
        try {
            if ($this->conn === null) {
                return false;
            }
            
            $this->conn->query('SELECT 1');
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Cierra la conexión
     */
    public function closeConnection() {
        $this->conn = null;
    }

    /**
     * Obtiene información de la conexión actual
     * @return array
     */
    public function getConnectionInfo() {
        if (!$this->isConnected()) {
            return ['status' => 'disconnected'];
        }

        try {
            $version = $this->conn->query('SELECT VERSION() as version')->fetch();
            return [
                'status' => 'connected',
                'host' => $this->host,
                'database' => $this->db_name,
                'charset' => $this->charset,
                'mysql_version' => $version['version']
            ];
        } catch (PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Verifica si existe la tabla de usuarios
     * @return bool
     */
    public function checkUserTable() {
        try {
            $stmt = $this->conn->prepare("SHOW TABLES LIKE 'user'");
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error verificando tabla user: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene la estructura de la tabla user
     * @return array
     */
    public function getUserTableStructure() {
        try {
            $stmt = $this->conn->prepare("DESCRIBE user");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error obteniendo estructura de tabla user: " . $e->getMessage());
            return [];
        }
    }
}

// Función helper para obtener instancia de base de datos
function getDatabase() {
    static $database = null;
    if ($database === null) {
        $database = new Database();
    }
    return $database;
}

// Función para verificar conexión de base de datos
function testDatabaseConnection() {
    try {
        $db = getDatabase();
        $info = $db->getConnectionInfo();
        
        if ($info['status'] === 'connected') {
            echo "✅ Conexión exitosa a MySQL\n";
            echo "📊 Base de datos: {$info['database']}\n";
            echo "🖥️ Servidor: {$info['host']}\n";
            echo "📄 Versión MySQL: {$info['mysql_version']}\n";
            
            // Verificar tabla user
            if ($db->checkUserTable()) {
                echo "✅ Tabla 'user' encontrada\n";
                $structure = $db->getUserTableStructure();
                echo "📋 Campos de la tabla:\n";
                foreach ($structure as $field) {
                    echo "   - {$field['Field']} ({$field['Type']})\n";
                }
            } else {
                echo "⚠️ Tabla 'user' no encontrada\n";
            }
            
            return true;
        } else {
            echo "❌ Error de conexión: {$info['message']}\n";
            return false;
        }
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
        return false;
    }
}

?>
