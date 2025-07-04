<?php
/**
 * Nexus-API Server - Punto de entrada principal
 * Maneja todas las rutas de la API con configuración CORS centralizada
 */

// Configuración inicial
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'C:\Server\apache\logs\php_errors.log');

// Incluir manejador de CORS centralizado
require_once 'config/CorsHandler.php';

// Configurar CORS y manejar preflight
CorsHandler::initialize();

// Content-Type para JSON
header("Content-Type: application/json; charset=UTF-8");

// Log para debug (solo información esencial)
error_log("API REQUEST: " . $_SERVER['REQUEST_METHOD'] . " " . $_SERVER['REQUEST_URI']);

// Obtener URI limpia
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Router optimizado - rutas específicas primero
switch ($uri) {
    case '/':
    case '/test':
        echo json_encode([
            'success' => true,
            'message' => 'Nexus API Server working!',
            'timestamp' => date('Y-m-d H:i:s'),
            'server' => 'index.php'
        ]);
        exit();

    case '/api/Auth/Login':
        require_once 'controllers/AuthController.php';
        (new AuthController())->login();
        exit();

    case '/api/Account/Profile':
        require_once 'controllers/AccountController.php';
        (new AccountController())->getProfile();
        exit();

    case '/api/Account/Logout':
        require_once 'controllers/AccountController.php';
        (new AccountController())->logout();
        exit();

    case '/api/Constellations':
        require_once 'controllers/ConstellationsController.php';
        $controller = new ConstellationsController();
        $params = [];
        if (!empty($_SERVER['QUERY_STRING'])) {
            parse_str($_SERVER['QUERY_STRING'], $params);
        }
        $controller->getAll($params);
        exit();

    default:
        // Delegar al router principal para rutas complejas
        require_once 'config/Router.php';
        (new Router())->handleRequest();
        break;
}
?>

function handleLogin() {
    try {
        // Usar el controlador real de autenticación
        require_once 'controllers/AuthController.php';
        $controller = new AuthController();
        $controller->login();
    } catch (Exception $e) {
        error_log("Error en handleLogin: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error interno del servidor'
        ]);
    }
}

function handleProfile() {
    try {
        // Usar el controlador real de cuenta
        require_once 'controllers/AccountController.php';
        $controller = new AccountController();
        $controller->getProfile();
    } catch (Exception $e) {
        error_log("Error en handleProfile: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error interno del servidor'
        ]);
    }
}

function handleLogout() {
    try {
        // Usar el controlador real de cuenta
        require_once 'controllers/AccountController.php';
        $controller = new AccountController();
        $controller->logout();
    } catch (Exception $e) {
        error_log("Error en handleLogout: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error interno del servidor'
        ]);
    }
}

function handleConstellations() {
    try {
        // Los headers CORS ya están configurados en el BaseController
        require_once 'controllers/ConstellationsController.php';
        $controller = new ConstellationsController();
        
        // Obtener parámetros de la URL si existen
        $params = [];
        
        // Parse query string si existe
        if (!empty($_SERVER['QUERY_STRING'])) {
            parse_str($_SERVER['QUERY_STRING'], $params);
        }
        
        // Llamar al método getAll del controlador
        $controller->getAll($params);
        
    } catch (Exception $e) {
        error_log("Error en handleConstellations: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error interno del servidor'
        ]);
    }
}

// Lista de orígenes permitidos (código original para compatibilidad)
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
$request_uri = $_SERVER['REQUEST_URI'] ?? '/';
error_log("PROXY DEBUG - Request Method: " . $request_method);
error_log("PROXY DEBUG - Origin: " . $origin);
error_log("PROXY DEBUG - URI: " . $request_uri);
error_log("PROXY DEBUG - Is Ngrok: " . ($is_ngrok ? 'YES' : 'NO'));

// Función de callback CORS removida - manejado por .htaccess

// Callback CORS removido - manejado por .htaccess

// PROXY: Redirigir TODAS las peticiones al servidor standalone
function proxyToStandalone() {
    global $origin;
    
    $standalone_url = 'http://localhost:9000' . $_SERVER['REQUEST_URI'];
    
    // Preparar cabeceras para el proxy
    $headers = [
        'Origin: ' . $origin,
        'Content-Type: application/json'
    ];
    
    // Copiar cookies si existen
    if (!empty($_SERVER['HTTP_COOKIE'])) {
        $headers[] = 'Cookie: ' . $_SERVER['HTTP_COOKIE'];
    }
    
    // Preparar contexto para file_get_contents
    $context_options = [
        'http' => [
            'method' => $_SERVER['REQUEST_METHOD'],
            'header' => implode("\r\n", $headers),
            'ignore_errors' => true
        ]
    ];
    
    // Incluir contenido del cuerpo para POST/PUT
    if (in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'PATCH'])) {
        $input = file_get_contents('php://input');
        $context_options['http']['content'] = $input;
        error_log("PROXY DEBUG - Body content: " . $input);
    }
    
    $context = stream_context_create($context_options);
    
    // Hacer la petición al servidor standalone
    error_log("PROXY DEBUG - Forwarding to: " . $standalone_url);
    $response = file_get_contents($standalone_url, false, $context);
    $response_headers = $http_response_header ?? [];
    
    // Log de las cabeceras recibidas del servidor standalone
    error_log("PROXY DEBUG - Response headers from standalone: " . json_encode($response_headers));
    
    // Reenviar headers de respuesta del servidor standalone (excepto CORS que maneja el callback)
    foreach ($response_headers as $header) {
        if (stripos($header, 'HTTP/') === 0) {
            // Extraer código de estado
            if (preg_match('/HTTP\/\d\.\d\s+(\d+)/', $header, $matches)) {
                http_response_code((int)$matches[1]);
            }
        } else {
            // Solo reenviar headers que NO sean CORS (el callback los maneja)
            $skip_headers = ['Host', 'Connection', 'Transfer-Encoding', 'Content-Encoding', 'Access-Control-Allow-Origin', 'Access-Control-Allow-Credentials', 'Access-Control-Allow-Methods', 'Access-Control-Allow-Headers', 'Access-Control-Max-Age'];
            $header_name = explode(':', $header)[0];
            if (!in_array($header_name, $skip_headers)) {
                header($header);
                error_log("PROXY DEBUG - Forwarding header: " . $header);
            }
        }
    }
    
    if ($response === false) {
        http_response_code(500);
        echo json_encode(['error' => 'Proxy to standalone server failed']);
    } else {
        // Headers CORS se establecen automáticamente por el callback
        echo $response;
    }
}

// PROXY: Ejecutar proxy para TODAS las peticiones - COMENTADO PARA USAR CONTROLADORES REALES
// proxyToStandalone();

// === MAIN PROCESSING ===
// Headers CORS manejados exclusivamente por .htaccess
// Content-Type para JSON
header("Content-Type: application/json; charset=UTF-8");

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