<?php
/**
 * Test script para probar todos los endpoints de la API
 */

echo "=== TEST DE ENDPOINTS DE LA API ===\n\n";

// Función para hacer requests
function makeRequest($url, $method = 'GET', $data = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, false);
    
    if ($data && $method !== 'GET') {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen(json_encode($data))
        ]);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Separar headers y body
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    
    return ['code' => $httpCode, 'body' => $body, 'headers' => $headers];
}

$baseUrl = 'http://localhost:8080/Skeletons/PHP-API-NEXUS/index.php';

// Test 1: GET /api/Constellations
echo "1. Probando GET /api/Constellations\n";
$response = makeRequest($baseUrl . '?request=api/Constellations');
echo "   Código HTTP: " . $response['code'] . "\n";
if ($response['code'] === 200) {
    $data = json_decode($response['body'], true);
    if (is_array($data)) {
        echo "   ✅ Respuesta válida - Constelaciones: " . count($data) . "\n";
    } else {
        echo "   ❌ Respuesta no es un array válido\n";
        echo "   Respuesta: " . substr($response['body'], 0, 200) . "...\n";
    }
} else {
    echo "   ❌ Error en la respuesta\n";
    echo "   Respuesta: " . substr($response['body'], 0, 200) . "...\n";
}

echo "\n";

// Test 2: GET /api/Constellations/6 (Ara)
echo "2. Probando GET /api/Constellations/6\n";
$response = makeRequest($baseUrl . '?request=api/Constellations/6');
echo "   Código HTTP: " . $response['code'] . "\n";
if ($response['code'] === 200) {
    $data = json_decode($response['body'], true);
    if (isset($data['english_name'])) {
        echo "   ✅ Constelación obtenida: " . $data['english_name'] . "\n";
    } else {
        echo "   ❌ Estructura de respuesta incorrecta\n";
        echo "   Respuesta: " . substr($response['body'], 0, 200) . "...\n";
    }
} else {
    echo "   ❌ Error en la respuesta\n";
    echo "   Respuesta: " . substr($response['body'], 0, 200) . "...\n";
}

echo "\n";

// Test 3: GET /api/Constellations/GetStars/6
echo "3. Probando GET /api/Constellations/GetStars/6\n";
$response = makeRequest($baseUrl . '?request=api/Constellations/GetStars/6');
echo "   Código HTTP: " . $response['code'] . "\n";
if ($response['code'] === 200) {
    $data = json_decode($response['body'], true);
    if (is_array($data)) {
        echo "   ✅ Estrellas obtenidas: " . count($data) . "\n";
        if (count($data) > 0) {
            echo "   Primera estrella ID: " . ($data[0]['id'] ?? 'N/A') . "\n";
        }
    } else {
        echo "   ❌ Respuesta no es un array válido\n";
        echo "   Respuesta: " . substr($response['body'], 0, 200) . "...\n";
    }
} else {
    echo "   ❌ Error en la respuesta\n";
    echo "   Respuesta: " . substr($response['body'], 0, 200) . "...\n";
}

echo "\n";

// Test 4: GET /api/Stars
echo "4. Probando GET /api/Stars\n";
$response = makeRequest($baseUrl . '?request=api/Stars');
echo "   Código HTTP: " . $response['code'] . "\n";
if ($response['code'] === 200) {
    $data = json_decode($response['body'], true);
    if (is_array($data)) {
        echo "   ✅ Respuesta válida - Estrellas: " . count($data) . "\n";
    } else {
        echo "   ❌ Respuesta no es un array válido\n";
        echo "   Respuesta: " . substr($response['body'], 0, 200) . "...\n";
    }
} else {
    echo "   ❌ Error en la respuesta\n";
    echo "   Respuesta: " . substr($response['body'], 0, 200) . "...\n";
}

echo "\n";

// Test 5: GET /api/Account/GetComments/6
echo "5. Probando GET /api/Account/GetComments/6\n";
$response = makeRequest($baseUrl . '?request=api/Account/GetComments/6');
echo "   Código HTTP: " . $response['code'] . "\n";
if ($response['code'] === 200) {
    $data = json_decode($response['body'], true);
    if (isset($data['data']) && is_array($data['data'])) {
        echo "   ✅ Comentarios obtenidos: " . count($data['data']) . "\n";
    } else {
        echo "   ⚠️  Sin comentarios o estructura diferente\n";
        echo "   Respuesta: " . substr($response['body'], 0, 200) . "...\n";
    }
} else {
    echo "   ❌ Error en la respuesta\n";
    echo "   Respuesta: " . substr($response['body'], 0, 200) . "...\n";
}

echo "\n=== TESTS COMPLETADOS ===\n";
?>
