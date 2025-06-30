<?php
// Test del endpoint de registro
echo "=== TEST REGISTRO ===\n\n";

// Test 1: Datos válidos
echo "1. Test con datos válidos:\n";
$testData = [
    'nick' => 'testuser123',
    'name' => 'Usuario',
    'surname1' => 'Prueba',
    'surname2' => 'Segundo',
    'email' => 'test@example.com',
    'password' => 'password123',
    'password2' => 'password123',
    'phoneNumber' => '+34123456789',
    'bday' => '1990-01-01',
    'about' => 'Usuario de prueba',
    'userLocation' => 'Madrid, España',
    'publicProfile' => true
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8082/api/Auth/Register');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FAILONERROR, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n";

$decoded = json_decode($response, true);
if ($decoded) {
    echo "JSON Formateado:\n";
    echo json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
}

echo "\n" . str_repeat("-", 50) . "\n\n";

// Test 2: Datos inválidos
echo "2. Test con datos inválidos:\n";
$invalidData = [
    'nick' => 'ab', // Muy corto
    'name' => '', // Vacío
    'surname1' => '', // Vacío
    'email' => 'invalid-email', // Formato inválido
    'password' => '123', // Muy corta
    'password2' => '456', // No coincide
    'phoneNumber' => 'invalid-phone',
    'bday' => '2030-01-01' // Fecha futura
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8082/api/Auth/Register');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($invalidData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FAILONERROR, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n";

$decoded = json_decode($response, true);
if ($decoded) {
    echo "JSON Formateado:\n";
    echo json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
}

echo "\n" . str_repeat("-", 50) . "\n\n";

// Test 3: Solo campos obligatorios
echo "3. Test solo campos obligatorios:\n";
$minimalData = [
    'nick' => 'minimal123',
    'name' => 'Usuario',
    'surname1' => 'Minimal',
    'email' => 'minimal@example.com'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8082/api/Auth/Register');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($minimalData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FAILONERROR, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n";

$decoded = json_decode($response, true);
if ($decoded) {
    echo "JSON Formateado:\n";
    echo json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
}
?>
