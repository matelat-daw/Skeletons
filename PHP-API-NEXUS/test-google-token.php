<?php
// Script de prueba para GoogleAuthService
require_once 'services/GoogleAuthService.php';

// Cargar variables de entorno
function loadEnvFile($filePath) {
    if (file_exists($filePath)) {
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value, '"\'');
                putenv("$name=$value");
                $_ENV[$name] = $value;
            }
        }
    }
}

loadEnvFile(__DIR__ . '/.env');

echo "=== PRUEBA GOOGLE AUTH SERVICE ===\n";

// Token de ejemplo del log (aunque puede estar expirado)
$token = "eyJhbGciOiJSUzI1NiIsImtpZCI6IjhlOGZjOGU1NTZmN2E3NmQwOGQzNTgyOWQ2ZjkwYWUyZTEyY2ZkMGQiLCJ0eXAiOiJKV1QifQ.eyJpc3MiOiJodHRwczovL2FjY291bnRzLmdvb2dsZS5jb20iLCJhenAiOiIxMDcxOTE3NjM3NjIzLTAyMGw1cWJjaWhwajR1N3RkdjQxMWNvdjRjZmg1MzBjLmFwcHMuZ29vZ2xldXNlcmNvbnRlbnQuY29tIiwiYXVkIjoiMTA3MTkxNzYzNzYyMy0wMjBsNXFiY2locGo0dTd0ZHY0MTFjb3Y0Y2ZoNTMwYy5hcHBzLmdvb2dsZXVzZXJjb250ZW50LmNvbSIsInN1YiI6IjExNzQ0ODQ3ODQyNDkzNDY1NTUxNiIsImVtYWlsIjoibWF0ZWxhdEBnbWFpbC5jb20iLCJlbWFpbF92ZXJpZmllZCI6dHJ1ZSwibmJmIjoxNzUxNjE4NTg1LCJuYW1lIjoiQ2VzYXIgTWF0ZWxhdCIsInBpY3R1cmUiOiJodHRwczovL2xoMy5nb29nbGV1c2VyY29udGVudC5jb20vYS9BQ2c4b2NKWktPMy1wZE9Ca1VxdTF1eV94aVhrMFRNRGI0OHlpS3NkSENNcEJOTHV1MlkzSWt3PXM5Ni1jIiwiZ2l2ZW5fbmFtZSI6IkNlc2FyIiwiZmFtaWx5X25hbWUiOiJNYXRlbGF0IiwiaWF0IjoxNzUxNjE4ODg1LCJleHAiOjE3NTE2MjI0ODUsImp0aSI6IjZlODE3MDM2MzE5MmE2MWI3MGJhYjJlOTdkOTJmMjNmMWIwZjAwMTYifQ.j0eeLKaCbxDxgLimmXoDcGvkbCtTgfr3UpqT69tZZMe4GQ6rut8W6liZfbXdYoFHbdCPiiXPl-2VwEr2vvTj3TlU4vjlWRlaJMRmqo_JXESqx7Pd01F7vyOfYB-Y20Dv57Uxc7Wyym1XO15etFVGY3TSPmzWX9xA79QKkYAxaAYcEpzJSBlqqYC7yVJ1y3drGh8UMud2iXp6Iu8DXmNbSzLl_4shs7GPRh9rvXMLU8L8oq7Z3Re1uvMcE-5srwqULIcBZzVJyKx71rD_kQIo-XmvOhg3xPWtaYEwBq4S1MSlT3dF6KpxhvGb9rccTRe5Lip29faDcpa8lPfxWflC9cvw";

echo "Token length: " . strlen($token) . "\n";
echo "Token parts: " . count(explode('.', $token)) . "\n";
echo "Token start: " . substr($token, 0, 100) . "...\n\n";

try {
    $googleService = new GoogleAuthService();
    echo "GoogleAuthService initialized successfully\n";
    echo "Client ID: " . $googleService->getClientId() . "\n\n";
    
    // Obtener información del token sin validar (para debug)
    echo "=== TOKEN INFO (no validated) ===\n";
    $tokenInfo = $googleService->getTokenInfo($token);
    if ($tokenInfo) {
        echo "Issuer: " . ($tokenInfo['iss'] ?? 'N/A') . "\n";
        echo "Audience: " . ($tokenInfo['aud'] ?? 'N/A') . "\n";
        echo "Subject: " . ($tokenInfo['sub'] ?? 'N/A') . "\n";
        echo "Email: " . ($tokenInfo['email'] ?? 'N/A') . "\n";
        echo "Name: " . ($tokenInfo['name'] ?? 'N/A') . "\n";
        echo "Issued at: " . ($tokenInfo['iat'] ?? 'N/A') . "\n";
        echo "Expires at: " . ($tokenInfo['exp'] ?? 'N/A') . "\n";
        echo "Current time: " . time() . "\n";
        
        if (isset($tokenInfo['exp'])) {
            $timeLeft = $tokenInfo['exp'] - time();
            echo "Time until expiration: $timeLeft seconds\n";
            echo "Token is " . ($timeLeft > 0 ? "VALID" : "EXPIRED") . "\n";
        }
    } else {
        echo "Could not decode token\n";
    }
    
    echo "\n=== ATTEMPTING VALIDATION ===\n";
    $payload = $googleService->validateGoogleToken($token);
    echo "✅ Token validated successfully!\n";
    echo "Email: " . $payload['email'] . "\n";
    echo "Name: " . $payload['name'] . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
