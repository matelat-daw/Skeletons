<?php
/**
 * Nexus-API Router - Sistema de enrutamiento interno sin .htaccess
 * Maneja todas las rutas de la API de forma centralizada
 */

// Configuración de errores y charset
error_reporting(E_ALL);
ini_set('display_errors', 0); // Desactivado para producción
header('Content-Type: application/json; charset=UTF-8');

// Header para evitar la página de advertencia de Ngrok
header('ngrok-skip-browser-warning: true');

// Configuración CORS - ÚNICA FUENTE DE HEADERS
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

// Limpiar cualquier header CORS previo que pueda haber establecido Apache o algún middleware
header_remove('Access-Control-Allow-Origin');
header_remove('Access-Control-Allow-Credentials');
header_remove('Access-Control-Allow-Methods');
header_remove('Access-Control-Allow-Headers');

// Lista de orígenes permitidos
$allowed_origins = [
    'http://localhost:4200',
    'https://localhost:4200',
    'http://127.0.0.1:4200',
    'https://127.0.0.1:4200',
];

// Verificar si el origen es un subdominio de ngrok-free.app
$is_ngrok = preg_match('/^https:\/\/[a-zA-Z0-9\-]+\.ngrok-free\.app$/', $origin);

// DEBUG: Log para verificar qué está pasando
$request_method = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';
error_log("CORS DEBUG - Request Method: " . $request_method);
error_log("CORS DEBUG - Origin: " . $origin);
error_log("CORS DEBUG - Is Ngrok: " . ($is_ngrok ? 'YES' : 'NO'));
error_log("CORS DEBUG - In allowed: " . (in_array($origin, $allowed_origins) ? 'YES' : 'NO'));
error_log("CORS DEBUG - Host: " . ($_SERVER['HTTP_HOST'] ?? 'NO HOST'));
error_log("CORS DEBUG - Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'NO URI'));

// Permitir el origen si está en la lista o es de Ngrok
if (in_array($origin, $allowed_origins) || $is_ngrok) {
    header("Access-Control-Allow-Origin: $origin");
    error_log("CORS DEBUG - Header set for: " . $origin);
} else {
    // TEMPORAL: Permitir Ngrok incluso si no coincide el patrón
    if (!empty($origin) && strpos($origin, 'ngrok') !== false) {
        header("Access-Control-Allow-Origin: $origin");
        error_log("CORS DEBUG - Ngrok fallback for: " . $origin);
    } else {
        // NUNCA usar * cuando hay credenciales
        error_log("CORS DEBUG - Origin REJECTED: " . $origin);
        // NO establecer header para orígenes no permitidos
    }
}

// Headers CORS complementarios (solo una vez)
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin");
header("Access-Control-Max-Age: 86400");

// DEBUG: Verificar que no se establezcan headers CORS duplicados
error_log("CORS DEBUG - Headers set - Allow-Origin: " . (headers_list() ? json_encode(preg_grep('/Access-Control-Allow-Origin/', headers_list())) : 'NONE'));
error_log("CORS DEBUG - Headers set - Allow-Credentials: " . (headers_list() ? json_encode(preg_grep('/Access-Control-Allow-Credentials/', headers_list())) : 'NONE'));

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