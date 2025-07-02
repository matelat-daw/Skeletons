<?php
/**
 * CORS Proxy - Solución final para el problema de Apache
 * Este archivo actúa como proxy entre Angular y la API
 */

// Limpiar headers
if (function_exists('header_remove')) {
    header_remove();
}

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

error_log("PROXY - Origin: $origin, Method: $method");

// Lista de orígenes permitidos
$allowed_origins = [
    'http://localhost:4200',
    'https://localhost:4200',
    'http://127.0.0.1:4200',
    'https://127.0.0.1:4200',
];

$is_ngrok = preg_match('/^https:\/\/[a-zA-Z0-9\-]+\.ngrok-free\.app$/', $origin);

// CORS headers - SIN Access-Control-Allow-Credentials para evitar el problema
if (in_array($origin, $allowed_origins) || $is_ngrok) {
    header("Access-Control-Allow-Origin: $origin");
} else if (!empty($origin) && strpos($origin, 'ngrok') !== false) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    header("Access-Control-Allow-Origin: http://localhost:4200");
}

// NO establecer Access-Control-Allow-Credentials para evitar el problema de Apache
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin, X-Proxy-Target, X-Proxy-Method");
header("Access-Control-Max-Age: 86400");
header("Content-Type: application/json; charset=UTF-8");

// Manejar OPTIONS
if ($method === 'OPTIONS') {
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Proxy CORS - OPTIONS handled']);
    exit();
}

// Obtener datos de la petición proxy
$target_url = $_SERVER['HTTP_X_PROXY_TARGET'] ?? null;
$target_method = $_SERVER['HTTP_X_PROXY_METHOD'] ?? $method;
$input_data = file_get_contents('php://input');

if (!$target_url) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing X-Proxy-Target header']);
    exit();
}

error_log("PROXY - Target: $target_url, Method: $target_method");

// Preparar contexto para petición interna
$context_options = [
    'http' => [
        'method' => $target_method,
        'header' => [
            'Content-Type: application/json',
            'User-Agent: ProxyCORS/1.0'
        ],
        'content' => $input_data,
        'ignore_errors' => true
    ]
];

// Agregar cookies si existen
if (!empty($_COOKIE)) {
    $cookie_string = '';
    foreach ($_COOKIE as $name => $value) {
        $cookie_string .= "$name=$value; ";
    }
    $context_options['http']['header'][] = 'Cookie: ' . rtrim($cookie_string, '; ');
}

$context = stream_context_create($context_options);

// Hacer petición interna
$internal_url = 'http://localhost:8080' . parse_url($target_url, PHP_URL_PATH);
error_log("PROXY - Internal URL: $internal_url");

$response = file_get_contents($internal_url, false, $context);
$response_headers = $http_response_header ?? [];

// Extraer código de respuesta
$status_code = 200;
if (!empty($response_headers[0])) {
    preg_match('/HTTP\/\d\.\d\s+(\d+)/', $response_headers[0], $matches);
    $status_code = $matches[1] ?? 200;
}

// Extraer cookies de la respuesta para reenviarlas
foreach ($response_headers as $header) {
    if (stripos($header, 'Set-Cookie:') === 0) {
        header($header);
        error_log("PROXY - Forwarding cookie: $header");
    }
}

http_response_code($status_code);

if ($response === false) {
    echo json_encode(['error' => 'Internal request failed']);
} else {
    echo $response;
}

error_log("PROXY - Response sent, status: $status_code");
?>
