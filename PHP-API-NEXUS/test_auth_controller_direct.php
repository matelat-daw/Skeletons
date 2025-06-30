<?php
/**
 * Test directo del AuthController sin router
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Test Directo del AuthController</h2>";

try {
    // Simular datos POST
    $_POST = [];
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['CONTENT_TYPE'] = 'application/json';
    
    // Datos de login
    $loginData = [
        'email' => 'cesarmatelat@gmail.com',
        'password' => 'Cesar@Peon',
        'rememberMe' => false
    ];
    
    // Simular el input JSON
    $GLOBALS['HTTP_RAW_POST_DATA'] = json_encode($loginData);
    file_put_contents('php://input', json_encode($loginData));
    
    echo "<h3>1. Datos de prueba preparados</h3>";
    echo "<pre>" . json_encode($loginData, JSON_PRETTY_PRINT) . "</pre>";
    
    // Incluir el AuthController directamente
    require_once 'controllers/AuthController.php';
    
    echo "<h3>2. AuthController incluido</h3>";
    
    // Crear instancia del controlador
    $authController = new AuthController();
    
    echo "<h3>3. Instancia de AuthController creada</h3>";
    
    // Capturar la salida
    ob_start();
    
    try {
        // Llamar al método login directamente
        $authController->login([]);
    } catch (Exception $e) {
        echo "❌ Excepción en login(): " . $e->getMessage() . "<br>";
        echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
    }
    
    $output = ob_get_clean();
    
    echo "<h3>4. Resultado del login:</h3>";
    if (!empty($output)) {
        echo "<pre>" . htmlspecialchars($output) . "</pre>";
        
        // Intentar decodificar como JSON
        $json = json_decode($output, true);
        if ($json) {
            echo "<h4>JSON decodificado:</h4>";
            echo "<pre>" . json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
        }
    } else {
        echo "No se produjo salida<br>";
    }
    
} catch (Exception $e) {
    echo "<h3>❌ Error:</h3>";
    echo $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
