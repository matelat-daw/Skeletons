<?php
/**
 * Servidor PHP Simple para API - Servidor único sin router
 */

// Headers CORS SIEMPRE primero
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin");
header("Content-Type: application/json; charset=UTF-8");

// Configuración de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log para debug
error_log("=== SIMPLE SERVER REQUEST ===");
error_log("Method: " . $_SERVER['REQUEST_METHOD']);
error_log("URI: " . $_SERVER['REQUEST_URI']);
error_log("Origin: " . ($_SERVER['HTTP_ORIGIN'] ?? 'none'));

// Manejar OPTIONS (preflight CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    echo json_encode(['message' => 'CORS preflight OK']);
    exit();
}

// Solo responder a las rutas que necesitamos si estamos en modo servidor built-in
if (php_sapi_name() === 'cli-server') {
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($uri === '/test' || $uri === '/') {
        echo json_encode([
            'success' => true,
            'message' => 'Simple server working!',
            'timestamp' => date('Y-m-d H:i:s'),
            'uri' => $uri,
            'method' => $method
        ]);
        exit();
    }
    
    if ($uri === '/api/Auth/Login') {
        if ($method !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit();
        }
        
        // Leer datos POST
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        error_log("Login data received: " . $input);
        
        if (!$data || !isset($data['email']) || !isset($data['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Email and password required']);
            exit();
        }
        
        $email = $data['email'];
        $password = $data['password'];
        
        // Credenciales de prueba
        if ($email === 'cesarmatelat@gmail.com' && $password === 'test123') {
            $user = [
                'id' => 1,
                'email' => $email,
                'name' => 'Test User',
                'createdAt' => date('Y-m-d H:i:s'),
                'isEmailConfirmed' => true
            ];
            
            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'user' => $user
            ]);
            error_log("Login successful for: $email");
        } else {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'error' => 'Invalid credentials'
            ]);
            error_log("Login failed for: $email");
        }
        exit();
    }
    
    if ($uri === '/api/Account/Profile') {
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
        exit();
    }
    
    if ($uri === '/api/Account/Logout') {
        echo json_encode([
            'success' => true,
            'message' => 'Logout successful'
        ]);
        exit();
    }
    
    // 404 para cualquier otra ruta
    http_response_code(404);
    echo json_encode(['error' => 'Endpoint not found', 'uri' => $uri, 'method' => $method]);
    exit();
}
?>

