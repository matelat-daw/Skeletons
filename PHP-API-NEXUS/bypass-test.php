<?php
/**
 * Wrapper CORS Ultra-Agresivo - Bypass completo de Apache
 * Este archivo maneja CORS de forma completamente manual
 */

// Iniciar capture de output
ob_start();

// Capturar todos los headers que Apache pueda haber establecido
$apache_headers = headers_list();
error_log("BYPASS - Apache headers detected: " . json_encode($apache_headers));

// Limpiar COMPLETAMENTE todos los headers
if (function_exists('header_remove')) {
    header_remove();
}

// Reiniciar el buffer de headers
if (function_exists('headers_sent') && !headers_sent()) {
    // Limpiar específicamente headers CORS problemáticos
    foreach ($apache_headers as $header) {
        if (strpos($header, 'Access-Control') !== false) {
            error_log("BYPASS - Removing problematic header: " . $header);
        }
    }
}

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

error_log("BYPASS - Origin: " . $origin . ", Method: " . $method);

// Lista de orígenes permitidos
$allowed_origins = [
    'http://localhost:4200',
    'https://localhost:4200',
    'http://127.0.0.1:4200',
    'https://127.0.0.1:4200',
];

$is_ngrok = preg_match('/^https:\/\/[a-zA-Z0-9\-]+\.ngrok-free\.app$/', $origin);

// Preparar headers CORS manualmente
$cors_headers = [];

if (in_array($origin, $allowed_origins) || $is_ngrok) {
    $cors_headers[] = "Access-Control-Allow-Origin: " . $origin;
    error_log("BYPASS - Origin allowed: " . $origin);
} else {
    if (!empty($origin) && strpos($origin, 'ngrok') !== false) {
        $cors_headers[] = "Access-Control-Allow-Origin: " . $origin;
        error_log("BYPASS - Ngrok fallback: " . $origin);
    } else {
        error_log("BYPASS - Origin rejected: " . $origin);
        // NO establecer Access-Control-Allow-Origin para orígenes no permitidos
    }
}

// Headers CORS básicos (NUNCA usar * con credentials)
$cors_headers[] = "Access-Control-Allow-Credentials: true";
$cors_headers[] = "Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH";
$cors_headers[] = "Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin";
$cors_headers[] = "Access-Control-Max-Age: 86400";

// Content-Type
$cors_headers[] = "Content-Type: application/json; charset=UTF-8";

// Headers de debug
$cors_headers[] = "X-Bypass-Test: true";
$cors_headers[] = "X-Bypass-Origin: " . $origin;

// Manejar OPTIONS
if ($method === 'OPTIONS') {
    // Establecer headers uno por uno
    foreach ($cors_headers as $header) {
        header($header);
    }
    
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'BYPASS - OPTIONS handled']);
    exit();
}

// Para requests normales, establecer headers y procesar
foreach ($cors_headers as $header) {
    header($header);
}

// Respuesta de test
$response = [
    'success' => true,
    'message' => 'BYPASS test successful',
    'data' => [
        'origin' => $origin,
        'method' => $method,
        'is_ngrok' => $is_ngrok,
        'apache_headers_detected' => $apache_headers,
        'cors_headers_set' => $cors_headers,
        'final_headers' => headers_list()
    ]
];

echo json_encode($response);

error_log("BYPASS - Final headers sent: " . json_encode(headers_list()));
?>
