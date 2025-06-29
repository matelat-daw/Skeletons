<?php
/**
 * Nexus-API Router - Sistema de enrutamiento interno sin .htaccess
 * Maneja todas las rutas de la API de forma centralizada
 */

// Configuración de errores y charset
error_reporting(E_ALL);
ini_set('display_errors', 0); // Desactivado en producción
header('Content-Type: application/json; charset=UTF-8');

// Configuración CORS
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowedOrigins = [
    'http://localhost:4200',
    'http://localhost:8080',
    'http://127.0.0.1:4200',
    'http://127.0.0.1:8080',
    'http://localhost:3000',
    'http://127.0.0.1:3000'
];

if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
} else if (strpos($origin, 'localhost') !== false || strpos($origin, '127.0.0.1') !== false) {
    header("Access-Control-Allow-Origin: $origin");
}

header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin");
header("Access-Control-Max-Age: 86400");

// Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Incluir el router
require_once 'config/Router.php';

try {
    $router = new Router();
    $router->handleRequest();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'message' => 'Error interno del servidor',
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
