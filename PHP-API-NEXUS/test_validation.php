<?php
// Test del endpoint con datos inválidos
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8082/api/Auth/Login');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'email' => 'invalid-email',
    'password' => '123'
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FAILONERROR, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "=== RESPUESTA DEL SERVIDOR ===\n";
echo "HTTP Code: $httpCode\n";
echo "Response: $response\n";

// Formatear JSON para mejor legibilidad
$decoded = json_decode($response, true);
if ($decoded) {
    echo "\n=== JSON FORMATEADO ===\n";
    echo json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
}
?>
