<?php
// Test simple para verificar CORS y cookies
header('Content-Type: application/json; charset=UTF-8');

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$method = $_SERVER['REQUEST_METHOD'] ?? '';

// Log de debug
error_log("TEST CORS - Method: $method, Origin: $origin");

// Verificar si es Ngrok
$is_ngrok = preg_match('/^https:\/\/[a-zA-Z0-9\-]+\.ngrok-free\.app$/', $origin);

if ($is_ngrok || !empty($origin)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    header("Access-Control-Allow-Origin: http://localhost:4200");
}

header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin");

// Headers de debug
header("X-Test-Origin: $origin");
header("X-Test-Method: $method");
header("X-Test-Is-Ngrok: " . ($is_ngrok ? 'YES' : 'NO'));

if ($method === 'OPTIONS') {
    http_response_code(200);
    echo json_encode(['message' => 'CORS test - OPTIONS handled']);
    exit();
}

if ($method === 'POST') {
    // Simular login - establecer cookie de prueba
    $cookieString = "test_token=test123; expires=" . gmdate('D, d M Y H:i:s T', time() + 3600) . 
                   "; path=/; secure; httponly; samesite=None; partitioned";
    header("Set-Cookie: {$cookieString}", false);
    
    echo json_encode([
        'success' => true,
        'message' => 'Test cookie set',
        'origin' => $origin,
        'is_ngrok' => $is_ngrok,
        'headers_sent' => headers_list()
    ]);
} else {
    echo json_encode([
        'success' => true,
        'message' => 'CORS test endpoint',
        'origin' => $origin,
        'method' => $method,
        'is_ngrok' => $is_ngrok,
        'cookies' => $_COOKIE
    ]);
}
?>
