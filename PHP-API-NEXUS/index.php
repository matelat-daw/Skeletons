<?php
/**
 * Nexus-API Router - Sistema de enrutamiento interno sin .htaccess
 * Maneja todas las rutas de la API de forma centralizada
 */

// Configuración de errores y charset
error_reporting(E_ALL);
ini_set('display_errors', 0); // Desactivado para producción
header('Content-Type: application/json; charset=UTF-8');

// Configuración CORS - ÚNICA FUENTE DE HEADERS
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

// Solo configurar Access-Control-Allow-Origin UNA VEZ
if (!empty($origin)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    header("Access-Control-Allow-Origin: *");
}

// Headers CORS complementarios (solo una vez)
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin");
header("Access-Control-Max-Age: 86400");

// Manejar preflight OPTIONS aquí (primera y única línea de defensa)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'CORS preflight handled']);
    exit();
}

// Incluir el router
require_once 'config/Router.php';

try {
    $router = new Router();
    $router->handleRequest();
} catch (Exception $e) {
    // Log del error
    error_log("Error en index.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    http_response_code(500);
    echo json_encode([
        'message' => 'Error interno del servidor',
        'success' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
} catch (Error $e) {
    // Capturar errores fatales también
    error_log("Error fatal en index.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    http_response_code(500);
    echo json_encode([
        'message' => 'Error fatal del servidor',
        'success' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
?>