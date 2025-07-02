<?php
// Test específico para Account/Profile
ob_start(); // Iniciar output buffering

error_log("PROFILE TEST - START");

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$method = $_SERVER['REQUEST_METHOD'] ?? '';

error_log("PROFILE TEST - Method: $method, Origin: $origin");
error_log("PROFILE TEST - Headers before any action: " . json_encode(headers_list()));

// Limpiar headers CORS previos
if (function_exists('header_remove')) {
    header_remove('Access-Control-Allow-Origin');
    header_remove('Access-Control-Allow-Credentials');
    header_remove('Access-Control-Allow-Methods');
    header_remove('Access-Control-Allow-Headers');
}

// Simular exactamente la misma lógica CORS de index.php
$allowed_origins = [
    'http://localhost:4200',
    'https://localhost:4200',
    'http://127.0.0.1:4200',
    'https://127.0.0.1:4200',
];

$is_ngrok = preg_match('/^https:\/\/[a-zA-Z0-9\-]+\.ngrok-free\.app$/', $origin);

if (in_array($origin, $allowed_origins) || $is_ngrok) {
    header("Access-Control-Allow-Origin: $origin", true);
    error_log("PROFILE TEST - CORS header set for: " . $origin);
} else {
    if (!empty($origin) && strpos($origin, 'ngrok') !== false) {
        header("Access-Control-Allow-Origin: $origin", true);
        error_log("PROFILE TEST - Ngrok fallback for: " . $origin);
    } else {
        error_log("PROFILE TEST - Origin REJECTED: " . $origin);
    }
}

header("Access-Control-Allow-Credentials: true", true);
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH", true);
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin", true);

// Content-Type después de CORS
header('Content-Type: application/json; charset=UTF-8');

error_log("PROFILE TEST - Headers after CORS setup: " . json_encode(headers_list()));

// Si es OPTIONS, terminar aquí
if ($method === 'OPTIONS') {
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Profile test - OPTIONS handled']);
    exit();
}

// Simular respuesta de profile
echo json_encode([
    'success' => true,
    'message' => 'Profile test successful',
    'data' => [
        'origin' => $origin,
        'method' => $method,
        'is_ngrok' => $is_ngrok,
        'headers_sent' => headers_list(),
        'cookies_received' => $_COOKIE
    ]
]);

error_log("PROFILE TEST - END");
?>
