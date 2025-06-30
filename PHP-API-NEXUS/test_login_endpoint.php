<?php
/**
 * Test directo del endpoint de login
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Test del endpoint de Login</h2>";

// Datos de prueba (usa los datos reales de tu usuario)
$loginData = [
    'email' => 'cesarmatelat@gmail.com',
    'password' => 'Cesar@Peon',
    'rememberMe' => false
];

// Hacer POST request al endpoint
$url = 'http://localhost:8080/Skeletons/PHP-API-NEXUS/api/Auth/Login';
$options = [
    'http' => [
        'header' => "Content-type: application/json\r\n",
        'method' => 'POST',
        'content' => json_encode($loginData)
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);

if ($result === FALSE) {
    echo "<h3>❌ Error al hacer la petición</h3>";
    
    // Mostrar información del error
    if (isset($http_response_header)) {
        echo "<h4>Headers de respuesta:</h4>";
        foreach ($http_response_header as $header) {
            echo $header . "<br>";
        }
    }
} else {
    echo "<h3>✅ Respuesta recibida:</h3>";
    echo "<pre>" . htmlspecialchars($result) . "</pre>";
    
    // Intentar decodificar JSON
    $response = json_decode($result, true);
    if ($response) {
        echo "<h4>JSON decodificado:</h4>";
        echo "<pre>" . print_r($response, true) . "</pre>";
    }
}

// Mostrar también logs PHP si están habilitados
echo "<h3>PHP Error Log (últimas líneas):</h3>";
$logFile = ini_get('error_log');
if ($logFile && file_exists($logFile)) {
    $logs = file($logFile);
    $lastLogs = array_slice($logs, -10);
    echo "<pre>" . htmlspecialchars(implode('', $lastLogs)) . "</pre>";
} else {
    echo "No se puede acceder al log de errores PHP.<br>";
}
?>
