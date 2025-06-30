<?php
/**
 * Script simple para probar endpoints de comentarios y favoritos con usuario existente
 */

// URLs base
$baseUrl = 'http://localhost:8080/Skeletons/PHP-API-NEXUS';

// Función para hacer peticiones HTTP
function makeRequest($url, $method = 'GET', $data = null, $headers = []) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    if ($data && ($method === 'POST' || $method === 'PUT')) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $headers[] = 'Content-Type: application/json';
    }
    
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    return [
        'http_code' => $httpCode,
        'response' => $response,
        'error' => $error
    ];
}

// Función para imprimir resultados
function printResult($test, $result) {
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "PRUEBA: $test\n";
    echo str_repeat("-", 60) . "\n";
    echo "HTTP Code: " . $result['http_code'] . "\n";
    
    if ($result['error']) {
        echo "Error: " . $result['error'] . "\n";
    } else {
        $decoded = json_decode($result['response'], true);
        if ($decoded) {
            echo "Response:\n" . json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        } else {
            echo "Raw Response:\n" . $result['response'] . "\n";
        }
    }
}

echo "===========================================\n";
echo "PRUEBAS RÁPIDAS DE ENDPOINTS DE USUARIO\n";
echo "===========================================\n";

// Crear usuario temporal para login
echo "\n1. Intentando login con usuario de prueba...\n";
$loginData = [
    'email' => 'test@example.com',
    'password' => 'TestPassword123!'
];

$loginResult = makeRequest("$baseUrl/index.php?request=api/Auth/Login", 'POST', $loginData);
printResult("Login", $loginResult);

// Si no funciona, probemos con el usuario admin por defecto
if ($loginResult['http_code'] !== 200) {
    echo "\n2. Intentando login con usuario admin...\n";
    $loginData = [
        'email' => 'admin@nexus.com',
        'password' => 'admin123'
    ];
    
    $loginResult = makeRequest("$baseUrl/index.php?request=api/Auth/Login", 'POST', $loginData);
    printResult("Login Admin", $loginResult);
}

$token = null;
if ($loginResult['http_code'] === 200) {
    $loginResponse = json_decode($loginResult['response'], true);
    if (isset($loginResponse['data']['token'])) {
        $token = $loginResponse['data']['token'];
        echo "\nToken obtenido: " . substr($token, 0, 50) . "...\n";
    }
}

if (!$token) {
    echo "\nNo se pudo obtener token. Probando endpoints sin autenticación:\n";
    
    // Probar endpoints públicos
    echo "\n3. Probando GET /api/Constellations...\n";
    $constellationsResult = makeRequest("$baseUrl/index.php?request=api/Constellations", 'GET');
    printResult("GET /api/Constellations", $constellationsResult);
    
    echo "\n4. Probando GET /api/Constellations/1...\n";
    $constellationResult = makeRequest("$baseUrl/index.php?request=api/Constellations/1", 'GET');
    printResult("GET /api/Constellations/1", $constellationResult);
    
    echo "\n5. Probando GET /api/Stars...\n";
    $starsResult = makeRequest("$baseUrl/index.php?request=api/Stars", 'GET');
    printResult("GET /api/Stars", $starsResult);
    
    echo "\nTerminando porque no se pudo autenticar.\n";
    exit(0);
}

$authHeaders = ["Authorization: Bearer $token"];

// Probar endpoints de favoritos
echo "\n" . str_repeat("=", 40) . "\n";
echo "PRUEBAS DE FAVORITOS\n";
echo str_repeat("=", 40) . "\n";

echo "\n6. Obteniendo favoritos del usuario...\n";
$favoritesResult = makeRequest("$baseUrl/index.php?request=api/Account/Favorites", 'GET', null, $authHeaders);
printResult("GET /api/Account/Favorites", $favoritesResult);

echo "\n7. Verificando si constelación 1 es favorita...\n";
$checkFavoriteResult = makeRequest("$baseUrl/index.php?request=api/Account/Favorites/1", 'GET', null, $authHeaders);
printResult("GET /api/Account/Favorites/1", $checkFavoriteResult);

// Probar endpoints de comentarios
echo "\n" . str_repeat("=", 40) . "\n";
echo "PRUEBAS DE COMENTARIOS\n";
echo str_repeat("=", 40) . "\n";

echo "\n8. Obteniendo comentarios del usuario...\n";
$commentsResult = makeRequest("$baseUrl/index.php?request=api/Account/Comments", 'GET', null, $authHeaders);
printResult("GET /api/Account/Comments", $commentsResult);

echo "\n9. Obteniendo comentarios de constelación 1...\n";
$commentsConstellationResult = makeRequest("$baseUrl/index.php?request=api/Account/GetComments/1", 'GET', null, $authHeaders);
printResult("GET /api/Account/GetComments/1", $commentsConstellationResult);

echo "\n" . str_repeat("=", 60) . "\n";
echo "PRUEBAS COMPLETADAS\n";
echo str_repeat("=", 60) . "\n";

?>
