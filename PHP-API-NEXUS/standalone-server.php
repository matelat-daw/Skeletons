<?php
/**
 * Servidor PHP Standalone para bypass completo de Apache
 * Ejecutar con: php -S localhost:9000 standalone-server.php
 */

// Headers CORS manuales - sin interferencia de Apache
function setCORSHeaders($origin = '') {
    $allowed_origins = [
        'http://localhost:4200',
        'https://localhost:4200',
        'http://127.0.0.1:4200',
        'https://127.0.0.1:4200',
    ];
    
    $is_ngrok = preg_match('/^https:\/\/[a-zA-Z0-9\-]+\.ngrok-free\.app$/', $origin);
    
    error_log("STANDALONE CORS - Origin: '$origin', Is Ngrok: " . ($is_ngrok ? 'YES' : 'NO'));
    
    // Permitir el origen si está en la lista o es de Ngrok
    if (in_array($origin, $allowed_origins)) {
        header("Access-Control-Allow-Origin: $origin");
        error_log("STANDALONE CORS - Set exact origin: $origin");
    } else if ($is_ngrok) {
        header("Access-Control-Allow-Origin: $origin");
        error_log("STANDALONE CORS - Set Ngrok origin: $origin");
    } else if (!empty($origin) && strpos($origin, 'ngrok') !== false) {
        header("Access-Control-Allow-Origin: $origin");
        error_log("STANDALONE CORS - Set Ngrok fallback: $origin");
    } else {
        // Para cualquier otro caso, usar el origin solicitado si existe, sino localhost:4200
        if (!empty($origin)) {
            header("Access-Control-Allow-Origin: $origin");
            error_log("STANDALONE CORS - Set requesting origin: $origin");
        } else {
            header("Access-Control-Allow-Origin: http://localhost:4200");
            error_log("STANDALONE CORS - Set default fallback");
        }
    }
    
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin");
    header("Access-Control-Max-Age: 86400");
    header("Content-Type: application/json; charset=UTF-8");
    
    return true;
}

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = $_SERVER['REQUEST_URI'] ?? '/';

error_log("STANDALONE - Origin: $origin, Method: $method, URI: $uri");

// Establecer headers CORS
setCORSHeaders($origin);

// Manejar OPTIONS
if ($method === 'OPTIONS') {
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Standalone CORS - OPTIONS handled']);
    exit();
}

// Router simple
switch (true) {
    case preg_match('#^/test/?$#', $uri):
        echo json_encode([
            'success' => true,
            'message' => 'Standalone server working!',
            'origin' => $origin,
            'method' => $method,
            'uri' => $uri
        ]);
        break;
        
    case preg_match('#^/api/Auth/Login/?$#', $uri):
        // Manejar login directamente (no proxy)
        if ($method !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
        }
        
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        error_log("STANDALONE LOGIN - Received data: " . $input);
        
        if (!$data || !isset($data['email']) || !isset($data['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Email and password are required']);
            break;
        }
        
        // SIMULACIÓN DE LOGIN (reemplazar con lógica real)
        $email = $data['email'];
        $password = $data['password'];
        
        // Credenciales de prueba múltiples (reemplazar con DB real)
        $validCredentials = [
            'cesarmatelat@gmail.com' => ['test123', 'Cesar@Peon', 'CesarPeon', 'admin123'],
            'test@test.com' => ['test123', 'password']
        ];
        
        $loginSuccess = false;
        if (isset($validCredentials[$email]) && in_array($password, $validCredentials[$email])) {
            $loginSuccess = true;
        }
        
        error_log("STANDALONE LOGIN - Email: $email, Password: $password, Success: " . ($loginSuccess ? 'YES' : 'NO'));
        
        if ($loginSuccess) {
            // Login exitoso - establecer cookie
            $token = 'test_jwt_token_' . time();
            
            // Establecer cookie HttpOnly segura
            setcookie('authToken', $token, [
                'expires' => time() + 3600, // 1 hora
                'path' => '/',
                'domain' => '',
                'secure' => false, // Para localhost
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'user' => [
                    'email' => $email,
                    'name' => 'César Matelat',
                    'id' => 1
                ]
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
        }
        break;
        
    case preg_match('#^/api/Account/Profile/?$#', $uri):
        // Manejar perfil directamente (no proxy)
        if ($method !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
        }
        
        // Verificar cookie de autenticación
        $authToken = $_COOKIE['authToken'] ?? '';
        
        if (empty($authToken) || !str_starts_with($authToken, 'test_jwt_token_')) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            break;
        }
        
        // Usuario autenticado - devolver perfil
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'user' => [
                'id' => 1,
                'email' => 'cesarmatelat@gmail.com',
                'name' => 'César Matelat',
                'createdAt' => '2025-01-01T00:00:00Z',
                'isEmailConfirmed' => true
            ]
        ]);
        break;
        
    case preg_match('#^/api/Account/Logout/?$#', $uri):
        // Manejar logout directamente
        if ($method !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
        }
        
        // Limpiar cookie de autenticación
        setcookie('authToken', '', [
            'expires' => time() - 3600, // Expirar en el pasado
            'path' => '/',
            'domain' => '',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Logout successful'
        ]);
        break;
        
    default:
        http_response_code(404);
        echo json_encode([
            'error' => 'Endpoint not found in standalone server',
            'uri' => $uri,
            'available_endpoints' => ['/test', '/api/Auth/Login', '/api/Account/Profile', '/api/Account/Logout']
        ]);
}
?>
