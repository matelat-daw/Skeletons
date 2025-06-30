<?php
/**
 * Script de prueba para endpoints de usuario (comentarios y favoritos)
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

echo "=================================\n";
echo "PRUEBAS DE ENDPOINTS DE USUARIO\n";
echo "=================================\n";

// Primero necesitamos un token de autenticación
echo "\n1. Registrando usuario de prueba...\n";
$registerData = [
    'email' => 'test.user@example.com',
    'password' => 'TestPassword123!',
    'confirmPassword' => 'TestPassword123!',
    'nick' => 'TestUser',
    'name' => 'Test',
    'surname1' => 'User'
];

$registerResult = makeRequest("$baseUrl/index.php?request=api/Auth/Register", 'POST', $registerData);
printResult("Registro de usuario", $registerResult);

// Confirmar email automáticamente (para pruebas)
echo "\n2. Confirmando email...\n";
$confirmResult = makeRequest("$baseUrl/index.php?request=api/Auth/ConfirmEmail&email=test.user@example.com&token=test-token-123", 'GET');
printResult("Confirmación de email", $confirmResult);

// Login para obtener token
echo "\n3. Iniciando sesión...\n";
$loginData = [
    'email' => 'test.user@example.com',
    'password' => 'TestPassword123!'
];

$loginResult = makeRequest("$baseUrl/index.php?request=api/Auth/Login", 'POST', $loginData);
printResult("Login", $loginResult);

$token = null;
if ($loginResult['http_code'] === 200) {
    $loginResponse = json_decode($loginResult['response'], true);
    if (isset($loginResponse['data']['token'])) {
        $token = $loginResponse['data']['token'];
        echo "\nToken obtenido: " . substr($token, 0, 50) . "...\n";
    }
}

if (!$token) {
    echo "\nERROR: No se pudo obtener el token de autenticación. Terminando pruebas.\n";
    exit(1);
}

$authHeaders = ["Authorization: Bearer $token"];

// Probar endpoints de favoritos
echo "\n" . str_repeat("=", 40) . "\n";
echo "PRUEBAS DE FAVORITOS\n";
echo str_repeat("=", 40) . "\n";

// 1. Obtener favoritos del usuario (debería estar vacío)
echo "\n4. Obteniendo favoritos del usuario...\n";
$favoritesResult = makeRequest("$baseUrl/index.php?request=api/Account/Favorites", 'GET', null, $authHeaders);
printResult("GET /api/Account/Favorites", $favoritesResult);

// 2. Verificar si una constelación es favorita
echo "\n5. Verificando si constelación 1 es favorita...\n";
$checkFavoriteResult = makeRequest("$baseUrl/index.php?request=api/Account/Favorites/1", 'GET', null, $authHeaders);
printResult("GET /api/Account/Favorites/1", $checkFavoriteResult);

// 3. Agregar constelación a favoritos
echo "\n6. Agregando constelación 1 a favoritos...\n";
$addFavoriteResult = makeRequest("$baseUrl/index.php?request=api/Account/Favorites/1", 'POST', null, $authHeaders);
printResult("POST /api/Account/Favorites/1", $addFavoriteResult);

// 4. Verificar favoritos después de agregar
echo "\n7. Verificando favoritos después de agregar...\n";
$favoritesAfterResult = makeRequest("$baseUrl/index.php?request=api/Account/Favorites", 'GET', null, $authHeaders);
printResult("GET /api/Account/Favorites (después de agregar)", $favoritesAfterResult);

// 5. Remover de favoritos
echo "\n8. Removiendo constelación 1 de favoritos...\n";
$removeFavoriteResult = makeRequest("$baseUrl/index.php?request=api/Account/Favorites/1", 'DELETE', null, $authHeaders);
printResult("DELETE /api/Account/Favorites/1", $removeFavoriteResult);

// Probar endpoints de comentarios
echo "\n" . str_repeat("=", 40) . "\n";
echo "PRUEBAS DE COMENTARIOS\n";
echo str_repeat("=", 40) . "\n";

// 1. Obtener comentarios del usuario (debería estar vacío)
echo "\n9. Obteniendo comentarios del usuario...\n";
$commentsResult = makeRequest("$baseUrl/index.php?request=api/Account/Comments", 'GET', null, $authHeaders);
printResult("GET /api/Account/Comments", $commentsResult);

// 2. Agregar un comentario
echo "\n10. Agregando comentario a constelación 1...\n";
$commentData = [
    'constellationId' => 1,
    'comment' => 'Este es un comentario de prueba para la constelación.'
];
$addCommentResult = makeRequest("$baseUrl/index.php?request=api/Account/Comments", 'POST', $commentData, $authHeaders);
printResult("POST /api/Account/Comments", $addCommentResult);

// 3. Obtener comentarios por constelación
echo "\n11. Obteniendo comentarios de constelación 1...\n";
$commentsConstellationResult = makeRequest("$baseUrl/index.php?request=api/Account/GetComments/1", 'GET', null, $authHeaders);
printResult("GET /api/Account/GetComments/1", $commentsConstellationResult);

// 4. Obtener comentarios del usuario después de agregar
echo "\n12. Obteniendo comentarios del usuario después de agregar...\n";
$userCommentsAfterResult = makeRequest("$baseUrl/index.php?request=api/Account/Comments", 'GET', null, $authHeaders);
printResult("GET /api/Account/Comments (después de agregar)", $userCommentsAfterResult);

echo "\n" . str_repeat("=", 60) . "\n";
echo "PRUEBAS COMPLETADAS\n";
echo str_repeat("=", 60) . "\n";

?>
