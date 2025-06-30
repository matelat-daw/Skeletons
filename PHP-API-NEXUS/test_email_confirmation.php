<?php
// Test completo del sistema de confirmación de email
echo "=== TEST SISTEMA DE CONFIRMACIÓN DE EMAIL ===\n\n";

// Test 1: Registro con envío de email
echo "1. Registrando usuario con email de confirmación:\n";
$testData = [
    'nick' => 'emailtest',
    'name' => 'Usuario',
    'surname1' => 'Email',
    'email' => 'test.email@example.com',
    'password' => 'password123',
    'password2' => 'password123'
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
$decoded = json_decode($response, true);
if ($decoded) {
    echo "Response:\n";
    echo json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    
    if (isset($decoded['data']['emailSent'])) {
        echo "Email enviado: " . ($decoded['data']['emailSent'] ? 'SÍ' : 'NO') . "\n";
    }
}

echo "\n" . str_repeat("-", 60) . "\n\n";

// Test 2: Intentar login sin confirmar email
echo "2. Intentando login sin confirmar email:\n";
$loginData = [
    'email' => 'test.email@example.com',
    'password' => 'password123',
    'rememberMe' => false
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8082/api/Auth/Login');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FAILONERROR, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
$decoded = json_decode($response, true);
if ($decoded) {
    echo "Response:\n";
    echo json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
}

echo "\n" . str_repeat("-", 60) . "\n\n";

// Test 3: Reenviar confirmación
echo "3. Reenviando email de confirmación:\n";
$resendData = [
    'email' => 'test.email@example.com'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8082/api/Auth/ResendConfirmation');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($resendData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FAILONERROR, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
$decoded = json_decode($response, true);
if ($decoded) {
    echo "Response:\n";
    echo json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
}

echo "\n" . str_repeat("-", 60) . "\n\n";

// Test 4: Confirmar con token inválido
echo "4. Confirmando con token inválido:\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8082/api/Auth/ConfirmEmail?token=invalid_token_12345');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FAILONERROR, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
$decoded = json_decode($response, true);
if ($decoded) {
    echo "Response:\n";
    echo json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "NOTAS IMPORTANTES:\n";
echo "1. Para que funcione completamente, necesitas:\n";
echo "   - Ejecutar el SQL para crear la tabla EmailConfirmationTokens\n";
echo "   - Configurar sendmail en tu servidor Apache\n";
echo "   - Actualizar el .env con tu configuración de email\n";
echo "2. El token real se obtendría del email enviado\n";
echo "3. El login fallará hasta que el email sea confirmado\n";
echo str_repeat("=", 60) . "\n";
?>
