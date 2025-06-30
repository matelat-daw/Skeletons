<?php
/**
 * Test del endpoint de Logout simulando Angular
 */

// Configuración de la URL exacta que usa Angular
$url = 'http://localhost:8080/Skeletons/PHP-API-NEXUS/api/Account/Logout';

// Headers exactos que envía Angular
$headers = [
    'Content-Type: application/json',
    'Accept: application/json',
    'Origin: http://localhost:4200',
    'Referer: http://localhost:4200/',
    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
];

// Usar cURL para simular mejor la petición de Angular
$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_POSTFIELDS => json_encode([]),
    CURLOPT_VERBOSE => true,
    CURLOPT_HEADER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_TIMEOUT => 30
]);

echo "<h2>Test Logout - Simulando Angular</h2>";

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

if ($error) {
    echo "<h3>❌ Error cURL: $error</h3>";
} else {
    echo "<h3>Status Code: $httpCode</h3>";
    
    // Separar headers y body
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    
    echo "<h3>Headers de respuesta:</h3>";
    echo "<pre>" . htmlspecialchars($headers) . "</pre>";
    
    echo "<h3>Body de respuesta:</h3>";
    echo "<pre>" . htmlspecialchars($body) . "</pre>";
    
    // Decodificar JSON si es posible
    $data = json_decode($body, true);
    if ($data) {
        echo "<h4>JSON decodificado:</h4>";
        echo "<pre>" . print_r($data, true) . "</pre>";
    }
}

curl_close($ch);

echo "<h3>Información adicional:</h3>";
echo "URL probada: $url<br>";
echo "Método: POST<br>";
echo "Content-Type: application/json<br>";
?>
