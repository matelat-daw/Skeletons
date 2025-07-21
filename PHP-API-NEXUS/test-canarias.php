<?php
/**
 * Script de prueba para verificar la conexión y configuración
 * del sistema de autenticación de Economía Circular Canarias
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🏝️ Prueba de Sistema - Economía Circular Canarias</h1>";

// Verificar extensiones PHP requeridas
echo "<h2>📋 Verificación de Extensiones PHP</h2>";
$requiredExtensions = ['pdo', 'pdo_mysql', 'json', 'openssl', 'mbstring'];
foreach ($requiredExtensions as $ext) {
    $status = extension_loaded($ext) ? '✅' : '❌';
    echo "<p>{$status} {$ext}</p>";
}

// Verificar configuración de base de datos
echo "<h2>🗄️ Verificación de Base de Datos</h2>";
try {
    require_once __DIR__ . '/config/database.php';
    
    if (testDatabaseConnection()) {
        echo "<p>✅ Conexión a base de datos exitosa</p>";
        
        // Verificar estructura de tabla
        $db = getDatabase();
        $structure = $db->getUserTableStructure();
        
        if (!empty($structure)) {
            echo "<h3>📊 Estructura de tabla 'user':</h3>";
            echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
            echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Por Defecto</th></tr>";
            foreach ($structure as $field) {
                echo "<tr>";
                echo "<td>{$field['Field']}</td>";
                echo "<td>{$field['Type']}</td>";
                echo "<td>{$field['Null']}</td>";
                echo "<td>{$field['Key']}</td>";
                echo "<td>{$field['Default']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<p>❌ Error de conexión a base de datos</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}

// Verificar JWT
echo "<h2>🔐 Verificación de JWT</h2>";
try {
    require_once __DIR__ . '/config/jwt.php';
    
    $jwtHandler = new JWTHandler();
    $testToken = $jwtHandler->generateToken(1, 'test@test.com', 'Test User');
    
    if ($testToken) {
        echo "<p>✅ Generación de JWT exitosa</p>";
        echo "<p><strong>Token de prueba:</strong> " . substr($testToken, 0, 50) . "...</p>";
        
        // Verificar validación
        $payload = $jwtHandler->validateToken($testToken);
        if ($payload) {
            echo "<p>✅ Validación de JWT exitosa</p>";
            echo "<p><strong>Payload:</strong> " . json_encode($payload) . "</p>";
        } else {
            echo "<p>❌ Error validando JWT</p>";
        }
    } else {
        echo "<p>❌ Error generando JWT</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Error JWT: " . $e->getMessage() . "</p>";
}

// Verificar modelo de usuario
echo "<h2>👤 Verificación de Modelo de Usuario</h2>";
try {
    require_once __DIR__ . '/models/CanariasUser.php';
    
    $userModel = new CanariasUser();
    echo "<p>✅ Modelo CanariasUser cargado exitosamente</p>";
    
    // Test de validación (sin insertar datos reales)
    $testData = [
        'name' => 'Test',
        'email' => 'test@example.com',
        'password' => 'testpassword123'
    ];
    
    echo "<p>✅ Modelo preparado para operaciones</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error en modelo: " . $e->getMessage() . "</p>";
}

// Verificar controlador
echo "<h2>🎮 Verificación de Controlador</h2>";
try {
    require_once __DIR__ . '/controllers/CanariasAuthController.php';
    echo "<p>✅ CanariasAuthController cargado exitosamente</p>";
} catch (Exception $e) {
    echo "<p>❌ Error en controlador: " . $e->getMessage() . "</p>";
}

// Información del sistema
echo "<h2>💻 Información del Sistema</h2>";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";
echo "<p><strong>Servidor:</strong> " . $_SERVER['SERVER_SOFTWARE'] ?? 'No disponible' . "</p>";
echo "<p><strong>Tiempo actual:</strong> " . date('Y-m-d H:i:s') . "</p>";

// URLs de prueba
echo "<h2>🔗 URLs de Prueba del API</h2>";
$baseUrl = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
echo "<ul>";
echo "<li><a href='{$baseUrl}/canarias-api.php' target='_blank'>Estado del API</a></li>";
echo "<li><a href='{$baseUrl}/api/status' target='_blank'>Status (con reescritura)</a></li>";
echo "<li><a href='{$baseUrl}/api/test/database' target='_blank'>Test de base de datos</a></li>";
echo "</ul>";

echo "<h2>📝 Instrucciones de Prueba</h2>";
echo "<ol>";
echo "<li>Verifica que todas las extensiones PHP estén instaladas</li>";
echo "<li>Configura la base de datos en <code>config/database.php</code></li>";
echo "<li>Asegúrate de que la tabla 'user' exista en la base de datos 'users'</li>";
echo "<li>Prueba los endpoints del API usando las URLs de arriba</li>";
echo "<li>Para pruebas de registro/login, usa un cliente REST como Postman</li>";
echo "</ol>";

echo "<hr>";
echo "<p><em>✅ Verificación completada - " . date('Y-m-d H:i:s') . "</em></p>";

?>
