<?php
/**
 * Test exacto del login como lo hace Angular
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "<h2>Test Exacto del Login (simulando Angular)</h2>";

// Datos exactos que envía Angular
$postData = json_encode([
    'email' => 'cesarmatelat@gmail.com',
    'password' => 'Cesar@Peon',
    'rememberMe' => false
]);

echo "<h3>1. Datos que envía Angular:</h3>";
echo "<pre>" . htmlspecialchars($postData) . "</pre>";

// Configurar cURL exactamente como Angular
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8080/Skeletons/PHP-API-NEXUS/api/Auth/Login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'X-Requested-With: XMLHttpRequest'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_VERBOSE, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

echo "<h3>2. Enviando petición POST...</h3>";

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

if ($error) {
    echo "<h3>❌ Error de cURL:</h3>";
    echo $error . "<br>";
} else {
    echo "<h3>3. Código de respuesta HTTP: " . $httpCode . "</h3>";
    
    // Separar headers y body
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    
    echo "<h3>4. Headers de respuesta:</h3>";
    echo "<pre>" . htmlspecialchars($headers) . "</pre>";
    
    echo "<h3>5. Body de respuesta:</h3>";
    if (!empty($body)) {
        echo "<strong>Longitud del body:</strong> " . strlen($body) . " bytes<br>";
        echo "<strong>Contenido:</strong><br>";
        echo "<pre>" . htmlspecialchars($body) . "</pre>";
        
        // Intentar decodificar JSON
        $jsonData = json_decode($body, true);
        if ($jsonData !== null) {
            echo "<h4>JSON decodificado:</h4>";
            echo "<pre>" . json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
        } else {
            echo "<strong>No es JSON válido</strong><br>";
        }
    } else {
        echo "<strong>❌ Body vacío - esto explica por qué Angular recibe una respuesta vacía</strong><br>";
    }
}

curl_close($ch);

echo "<h3>6. Información adicional de cURL:</h3>";
$curlInfo = curl_getinfo($ch);
echo "<pre>";
foreach ($curlInfo as $key => $value) {
    if (is_string($value) || is_numeric($value)) {
        echo "$key: $value\n";
    }
}
echo "</pre>";

// Verificar si el endpoint existe accediendo directamente
echo "<h3>7. Verificando si el endpoint existe:</h3>";
$ch2 = curl_init();
curl_setopt($ch2, CURLOPT_URL, 'http://localhost:8080/Skeletons/PHP-API-NEXUS/api/Auth/Login');
curl_setopt($ch2, CURLOPT_CUSTOMREQUEST, 'GET'); // GET en lugar de POST para ver si responde
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_HEADER, true);

$testResponse = curl_exec($ch2);
$testHttpCode = curl_getinfo($ch2, CURLINFO_HTTP_CODE);

echo "Respuesta a GET: HTTP " . $testHttpCode . "<br>";
if ($testHttpCode == 405) {
    echo "✅ El endpoint existe (405 = Method Not Allowed para GET)<br>";
} elseif ($testHttpCode == 404) {
    echo "❌ El endpoint no existe (404 = Not Found)<br>";
} else {
    echo "Código inesperado: " . $testHttpCode . "<br>";
}

curl_close($ch2);

echo "<h3>8. Verificando el archivo de log de errores de PHP:</h3>";
$errorLog = ini_get('error_log');
if ($errorLog && file_exists($errorLog)) {
    $lines = file($errorLog);
    $recentLines = array_slice($lines, -20); // Últimas 20 líneas
    echo "<pre>";
    foreach ($recentLines as $line) {
        if (strpos($line, date('Y-m-d')) !== false) {
            echo htmlspecialchars($line);
        }
    }
    echo "</pre>";
} else {
    echo "No se pudo acceder al log de errores: " . ($errorLog ?: 'no configurado') . "<br>";
}
?>
