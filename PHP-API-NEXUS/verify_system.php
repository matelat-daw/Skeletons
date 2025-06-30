<?php
/**
 * Verificar dependencias del sistema
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Verificación de Dependencias del Sistema</h2>";

$files_to_check = [
    'config/Router.php',
    'config/database_manager.php',
    'config/jwt.php',
    'controllers/AuthController.php',
    'controllers/BaseController.php',
    'models/User.php',
    'models/UserRepository.php',
    'models/Login.php',
    'services/AuthService.php',
    '.env'
];

echo "<h3>1. Verificando archivos necesarios:</h3>";
$all_exist = true;
foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        echo "✅ $file<br>";
    } else {
        echo "❌ $file (FALTA)<br>";
        $all_exist = false;
    }
}

if ($all_exist) {
    echo "<h3>2. Todos los archivos existen ✅</h3>";
} else {
    echo "<h3>2. Faltan archivos importantes ❌</h3>";
}

echo "<h3>3. Verificando sintaxis de archivos PHP:</h3>";
foreach ($files_to_check as $file) {
    if (file_exists($file) && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
        $result = shell_exec("php -l \"$file\" 2>&1");
        if (strpos($result, 'No syntax errors') !== false) {
            echo "✅ $file - Sin errores de sintaxis<br>";
        } else {
            echo "❌ $file - Error de sintaxis:<br>";
            echo "<pre>$result</pre>";
        }
    }
}

echo "<h3>4. Verificando configuración de PHP:</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Memory Limit: " . ini_get('memory_limit') . "<br>";
echo "Max Execution Time: " . ini_get('max_execution_time') . "<br>";
echo "Display Errors: " . (ini_get('display_errors') ? 'On' : 'Off') . "<br>";
echo "Error Reporting: " . error_reporting() . "<br>";

echo "<h3>5. Verificando extensiones PHP necesarias:</h3>";
$required_extensions = ['pdo', 'pdo_sqlsrv', 'json', 'openssl', 'curl'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ $ext<br>";
    } else {
        echo "❌ $ext (NO CARGADA)<br>";
    }
}

echo "<h3>6. Probando inclusión del Router:</h3>";
try {
    require_once 'config/Router.php';
    echo "✅ Router incluido correctamente<br>";
    
    $router = new Router();
    echo "✅ Instancia de Router creada<br>";
    
} catch (Exception $e) {
    echo "❌ Error con Router: " . $e->getMessage() . "<br>";
}

echo "<h3>7. Probando conexión a base de datos:</h3>";
try {
    require_once 'config/database_manager.php';
    $dbManager = new DatabaseManager();
    echo "✅ DatabaseManager creado<br>";
    
    $conn = $dbManager->getConnection('NexusUsers');
    echo "✅ Conexión a NexusUsers establecida<br>";
    
} catch (Exception $e) {
    echo "❌ Error de base de datos: " . $e->getMessage() . "<br>";
}

echo "<h3>8. Probando AuthController básico:</h3>";
try {
    require_once 'controllers/AuthController.php';
    echo "✅ AuthController incluido<br>";
    
    $authController = new AuthController();
    echo "✅ Instancia de AuthController creada<br>";
    
} catch (Exception $e) {
    echo "❌ Error con AuthController: " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
