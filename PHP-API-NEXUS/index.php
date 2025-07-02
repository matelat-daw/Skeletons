<?php
/**
 * Nexus-API Server - Servidor de API directo
 * Maneja todas las rutas de la API con headers CORS apropiados
 */

// Configuración de errores y charset
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'C:\Server\apache\logs\php_errors.log');

// Headers CORS SIEMPRE primero
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin");
header("Content-Type: application/json; charset=UTF-8");

// Header para evitar la página de advertencia de Ngrok
header('ngrok-skip-browser-warning: true');

// Log para debug
error_log("=== API REQUEST ===");
error_log("Method: " . $_SERVER['REQUEST_METHOD']);
error_log("URI: " . $_SERVER['REQUEST_URI']);
error_log("Origin: " . ($_SERVER['HTTP_ORIGIN'] ?? 'none'));

// Manejar OPTIONS (preflight CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    echo json_encode(['message' => 'CORS preflight OK']);
    exit();
}

// Obtener la URI y limpiarla
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Router simple
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
        handleLogin();
        exit();

    case '/api/Account/Profile':
        handleProfile();
        exit();

    case '/api/Account/Logout':
        handleLogout();
        exit();

    default:
        // Si no es una ruta de API, continuar con el código original
        break;
}

function handleLogin() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }
    
    // Leer datos POST
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    error_log("Login data received: " . $input);
    
    if (!$data || !isset($data['email']) || !isset($data['password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Email and password required']);
        return;
    }
    
    $email = $data['email'];
    $password = $data['password'];
    
    // Credenciales de prueba
    $valid_credentials = [
        'cesarmatelat@gmail.com' => 'test123',
        'test@example.com' => 'password123'
    ];
    
    if (isset($valid_credentials[$email]) && $valid_credentials[$email] === $password) {
        // Login exitoso
        $user = [
            'id' => 1,
            'email' => $email,
            'name' => 'Test User',
            'createdAt' => date('Y-m-d H:i:s'),
            'isEmailConfirmed' => true
        ];
        
        $response = [
            'success' => true,
            'message' => 'Login successful',
            'user' => $user
        ];
        
        error_log("Login successful for: $email");
        echo json_encode($response);
    } else {
        // Login fallido
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid credentials'
        ]);
        error_log("Login failed for: $email");
    }
}

function handleProfile() {
    // Simular usuario autenticado
    $user = [
        'id' => 1,
        'email' => 'cesarmatelat@gmail.com',
        'name' => 'Test User',
        'createdAt' => date('Y-m-d H:i:s'),
        'isEmailConfirmed' => true
    ];
    
    echo json_encode([
        'success' => true,
        'user' => $user
    ]);
}

function handleLogout() {
    echo json_encode([
        'success' => true,
        'message' => 'Logout successful'
    ]);
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

// Función de callback para establecer headers CORS en el último momento
function setCORSHeadersCallback() {
    global $origin, $allowed_origins, $is_ngrok;
    
    // Determinar el origin a usar
    if (in_array($origin, $allowed_origins) || $is_ngrok || (!empty($origin) && strpos($origin, 'ngrok') !== false)) {
        $cors_origin = $origin;
    } else if (!empty($origin)) {
        $cors_origin = $origin;
    } else {
        $cors_origin = 'http://localhost:4200';
    }
    
    // Establecer headers CORS
    header("Access-Control-Allow-Origin: $cors_origin", true);
    header("Access-Control-Allow-Credentials: true", true);
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH", true);
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin", true);
    header("Access-Control-Max-Age: 86400", true);
    
    error_log("CORS CALLBACK - Set origin: $cors_origin");
}

// Registrar callback para establecer headers justo antes del envío
header_register_callback('setCORSHeadersCallback');

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

// PROXY: Ejecutar proxy para TODAS las peticiones
proxyToStandalone();

// === CÓDIGO ORIGINAL (ya no se ejecuta) ===
error_log("CORS DEBUG - Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'NO URI'));

// Permitir el origen si está en la lista o es de Ngrok
if (in_array($origin, $allowed_origins) || $is_ngrok) {
    // Usar header con replace=true para sobrescribir cualquier header previo
    header("Access-Control-Allow-Origin: $origin", true);
    error_log("CORS DEBUG - Header set for: " . $origin);
} else {
    // TEMPORAL: Permitir Ngrok incluso si no coincide el patrón
    if (!empty($origin) && strpos($origin, 'ngrok') !== false) {
        header("Access-Control-Allow-Origin: $origin", true);
        error_log("CORS DEBUG - Ngrok fallback for: " . $origin);
    } else {
        // NUNCA usar * cuando hay credenciales
        error_log("CORS DEBUG - Origin REJECTED: " . $origin);
        // NO establecer header para orígenes no permitidos
    }
}

// Headers CORS complementarios (solo una vez) - FORZAR SOBRESCRIBIR
header("Access-Control-Allow-Credentials: true", true);
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH", true);
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin", true);
header("Access-Control-Max-Age: 86400", true);

// AHORA establecer Content-Type después de CORS
header('Content-Type: application/json; charset=UTF-8');

// DEBUG: Verificar que no se establezcan headers CORS duplicados
error_log("CORS DEBUG - Headers set - Allow-Origin: " . (headers_list() ? json_encode(preg_grep('/Access-Control-Allow-Origin/', headers_list())) : 'NONE'));
error_log("CORS DEBUG - Headers set - Allow-Credentials: " . (headers_list() ? json_encode(preg_grep('/Access-Control-Allow-Credentials/', headers_list())) : 'NONE'));

// DEBUG TEMPORAL: Agregar headers de debug a la respuesta para verificar
if ($request_method !== 'OPTIONS') {
    header("X-Debug-Origin: $origin");
    header("X-Debug-Is-Ngrok: " . ($is_ngrok ? 'YES' : 'NO'));
    header("X-Debug-Allowed: " . (in_array($origin, $allowed_origins) || $is_ngrok ? 'YES' : 'NO'));
}

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