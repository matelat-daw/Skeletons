<?php
/**
 * Test directo del controlador AuthController
 */

// Simular environment para testing
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/json';

// Datos de prueba
$userData = [
    'email' => 'test' . time() . '@example.com',
    'password' => 'TestPassword123!',
    'confirmPassword' => 'TestPassword123!',
    'nick' => 'testuser' . time(),
    'name' => 'Test',
    'surname1' => 'User',
    'surname2' => 'Example',
    'birthdate' => '1990-01-01',
    'gender' => 'M',
    'country' => 'ES',
    'city' => 'Madrid',
    'postal_code' => '28001',
    'phone' => '+34600000000'
];

// Simular entrada JSON
file_put_contents('php://input', json_encode($userData));

echo "=== TEST DIRECTO DEL CONTROLADOR ===\n";
echo "Email: " . $userData['email'] . "\n";
echo "Nick: " . $userData['nick'] . "\n\n";

try {
    // Cargar dependencias necesarias
    require_once 'config/env.php';
    require_once 'config/database_manager.php';
    require_once 'config/jwt.php';
    require_once 'controllers/AuthController.php';
    
    echo "✅ Dependencias cargadas\n";
    
    // Crear controlador
    $authController = new AuthController();
    echo "✅ Controlador creado\n";
    
    // Ejecutar registro
    echo "\n=== EJECUTANDO REGISTRO ===\n";
    ob_start();
    $authController->register();
    $response = ob_get_clean();
    
    echo "Respuesta:\n";
    echo $response . "\n";
    
    // Decodificar respuesta
    $decoded = json_decode($response, true);
    if ($decoded) {
        echo "\n=== JSON FORMATEADO ===\n";
        echo json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "❌ Error Fatal: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== FIN DEL TEST ===\n";
?>
