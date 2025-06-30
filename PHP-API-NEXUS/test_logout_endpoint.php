<?php
/**
 * Test del endpoint de Logout
 */

// Configuración de la URL
$url = 'http://localhost:8080/Skeletons/PHP-API-NEXUS/api/Account/Logout';

// Crear contexto para la petición POST
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => [
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        'content' => json_encode([])
    ]
]);

echo "<h2>Test del endpoint de Logout</h2>";

// Hacer la petición
$response = file_get_contents($url, false, $context);

if ($response === false) {
    echo "<h3>❌ Error: No se pudo conectar al servidor</h3>";
    $error = error_get_last();
    echo "<p>Error: " . $error['message'] . "</p>";
} else {
    echo "<h3>✅ Respuesta recibida:</h3>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
    
    // Intentar decodificar JSON
    $data = json_decode($response, true);
    if ($data) {
        echo "<h4>JSON decodificado:</h4>";
        echo "<pre>" . print_r($data, true) . "</pre>";
    }
}

// Mostrar headers de respuesta si están disponibles
if (isset($http_response_header)) {
    echo "<h3>Headers de respuesta:</h3>";
    echo "<pre>" . implode("\n", $http_response_header) . "</pre>";
}

// Intentar acceder al log de errores PHP
echo "<h3>PHP Error Log (últimas líneas):</h3>";
$errorLog = ini_get('error_log');
if ($errorLog && file_exists($errorLog)) {
    $lines = file($errorLog);
    $lastLines = array_slice($lines, -10);
    echo "<pre>" . implode('', $lastLines) . "</pre>";
} else {
    echo "No se puede acceder al log de errores PHP.<br>";
}
?>
