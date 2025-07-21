<?php
/**
 * API Router para Economía Circular Canarias
 * Maneja todas las rutas de autenticación y funcionalidades principales
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/controllers/CanariasAuthController.php';
require_once __DIR__ . '/config/CorsHandler.php';

// Manejar CORS para todas las peticiones
CorsHandler::handleCors();

// Si es una petición OPTIONS (preflight), terminar aquí
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Obtener la ruta solicitada
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);

// Remover prefijo /api si existe
$path = preg_replace('#^/api#', '', $path);

// Remover slashes iniciales y finales
$path = trim($path, '/');

// Dividir la ruta en segmentos
$segments = explode('/', $path);

// Enrutamiento principal
try {
    // Rutas de autenticación
    if ($segments[0] === 'auth') {
        $authController = new CanariasAuthController();
        
        switch ($segments[1] ?? '') {
            case 'register':
                $authController->register();
                break;
                
            case 'login':
                $authController->login();
                break;
                
            case 'logout':
                $authController->logout();
                break;
                
            case 'validate':
                $authController->validateToken();
                break;
                
            case 'confirm':
                $authController->confirmEmail();
                break;
                
            default:
                sendErrorResponse(404, 'Endpoint de autenticación no encontrado');
        }
    }
    
    // Ruta de prueba de conexión a base de datos
    elseif ($path === 'test/database') {
        testDatabaseConnection();
    }
    
    // Ruta de estado del API
    elseif ($path === 'status' || $path === '') {
        sendSuccessResponse(200, 'API Economía Circular Canarias funcionando', [
            'version' => '1.0.0',
            'environment' => $_ENV['ENVIRONMENT'] ?? 'development',
            'timestamp' => date('c'),
            'endpoints' => [
                'auth' => [
                    'POST /api/auth/register' => 'Registrar nuevo usuario',
                    'POST /api/auth/login' => 'Iniciar sesión',
                    'POST /api/auth/logout' => 'Cerrar sesión',
                    'GET /api/auth/validate' => 'Validar token JWT',
                    'GET /api/auth/confirm' => 'Confirmar email'
                ],
                'test' => [
                    'GET /api/test/database' => 'Probar conexión a base de datos'
                ]
            ]
        ]);
    }
    
    // Ruta no encontrada
    else {
        sendErrorResponse(404, 'Endpoint no encontrado', [
            'requested_path' => $path,
            'available_endpoints' => [
                '/api/auth/register',
                '/api/auth/login', 
                '/api/auth/logout',
                '/api/auth/validate',
                '/api/auth/confirm',
                '/api/test/database',
                '/api/status'
            ]
        ]);
    }

} catch (Exception $e) {
    error_log("❌ Error en API: " . $e->getMessage());
    sendErrorResponse(500, 'Error interno del servidor');
}

/**
 * Enviar respuesta de éxito
 */
function sendSuccessResponse($statusCode, $message, $data = []) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    
    $response = array_merge([
        'success' => true,
        'message' => $message,
        'timestamp' => date('c')
    ], $data);

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/**
 * Enviar respuesta de error
 */
function sendErrorResponse($statusCode, $message, $data = []) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    
    $response = array_merge([
        'success' => false,
        'message' => $message,
        'timestamp' => date('c')
    ], $data);

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

?>
