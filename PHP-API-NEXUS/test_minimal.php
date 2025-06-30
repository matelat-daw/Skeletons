<?php
/**
 * Test mínimo del endpoint
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Test básico iniciado<br>";

// Simular exactamente lo que hace el router cuando recibe una petición POST a /api/Auth/Login
try {
    echo "1. Configurando variables de servidor...<br>";
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['REQUEST_URI'] = '/Skeletons/PHP-API-NEXUS/api/Auth/Login';
    $_SERVER['CONTENT_TYPE'] = 'application/json';
    
    echo "2. Incluyendo index.php...<br>";
    
    // Capturar la salida del index.php
    ob_start();
    include 'index.php';
    $output = ob_get_clean();
    
    echo "3. Salida de index.php:<br>";
    if (!empty($output)) {
        echo "<pre>" . htmlspecialchars($output) . "</pre>";
    } else {
        echo "Sin salida<br>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "Test completado<br>";
?>
