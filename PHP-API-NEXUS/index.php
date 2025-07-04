<?php
/**
 * Nexus-API Server - Punto de entrada principal
 * Maneja todas las rutas de la API con configuración CORS centralizada
 */

// Cargar variables de entorno desde .env
function loadEnvFile($filePath) {
    if (file_exists($filePath)) {
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value, '"\'');
                putenv("$name=$value");
                $_ENV[$name] = $value;
            }
        }
    }
}
loadEnvFile(__DIR__ . '/.env');

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

    case '/api/Auth/GoogleLogin':
        require_once 'controllers/AuthController.php';
        (new AuthController())->googleLogin();
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

    case '/api/Auth/DebugGoogleToken':
        // Endpoint temporal para debugging de tokens de Google
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        $token = $data['token'] ?? null;
        
        if (!$token) {
            echo json_encode(['error' => 'Token requerido']);
            exit();
        }
        
        require_once 'services/GoogleAuthService.php';
        try {
            $googleService = new GoogleAuthService();
            $payload = $googleService->validateGoogleToken($token);
            echo json_encode([
                'success' => true,
                'client_id_configured' => $googleService->getClientId(),
                'payload' => $payload
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'client_id_configured' => (new GoogleAuthService())->getClientId()
            ]);
        }
        exit();

    default:
        // Delegar al router principal para rutas complejas
        require_once 'config/Router.php';
        try {
            (new Router())->handleRequest();
        } catch (Exception $e) {
            error_log("Router error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error interno del servidor'
            ]);
        }
        break;
}
?>
