<?php
/**
 * Router para servidor PHP built-in
 * Maneja todas las rutas de la API con headers CORS apropiados
 */

// Headers CORS SIEMPRE primero
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin");
header("Content-Type: application/json; charset=UTF-8");

// Log para debug
error_log("=== ROUTER REQUEST ===");
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
            'message' => 'PHP Router working!',
            'timestamp' => date('Y-m-d H:i:s'),
            'server' => 'router.php'
        ]);
        break;

    case '/api/Auth/Login':
        handleLogin();
        break;

    case '/api/Account/Profile':
        handleProfile();
        break;

    case '/api/Account/Logout':
        handleLogout();
        break;

    default:
        // 404 para cualquier otra ruta
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found', 'uri' => $uri]);
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
?>
