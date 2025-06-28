<?php
// Configuración de CORS robusta para desarrollo
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowedOrigins = [
    'http://localhost:4200',
    'http://localhost:8080',
    'http://127.0.0.1:4200',
    'http://127.0.0.1:8080',
    'http://localhost:3000',
    'http://127.0.0.1:3000'
];

// Verificar si es un origen permitido o si es desarrollo local
$isAllowed = false;
if (in_array($origin, $allowedOrigins)) {
    $isAllowed = true;
} else if (strpos($origin, 'localhost') !== false || strpos($origin, '127.0.0.1') !== false) {
    $isAllowed = true;
}

// Establecer cabeceras CORS
if ($isAllowed || empty($origin)) {
    if (!empty($origin)) {
        header("Access-Control-Allow-Origin: $origin");
    } else {
        header("Access-Control-Allow-Origin: *");
    }
    header("Access-Control-Allow-Credentials: true");
} else {
    header("Access-Control-Allow-Origin: *");
}

header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin, X-Csrf-Token");
header("Access-Control-Max-Age: 86400");

// Manejar preflight requests OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Headers de contenido
header("Content-Type: application/json; charset=UTF-8");

// Incluir archivos necesarios
include_once '../../config/jwt.php';

// Función para enviar respuesta JSON
function sendResponse($status_code, $message = null, $data = null) {
    http_response_code($status_code);
    $response = array();
    
    if ($message) {
        $response['message'] = $message;
    }
    
    if ($data) {
        $response['data'] = $data;
    }
    
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit();
}

try {
    // Instanciar JWT handler
    $jwt = new JWTHandler();
    
    // Obtener método HTTP
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'POST') {
        // Obtener token desde cookie
        $token = $jwt->getTokenFromCookie();
        
        if (!$token) {
            sendResponse(401, "No hay sesión activa");
        }
        
        // Validar token
        $tokenData = $jwt->validateToken($token);
        
        if (!$tokenData) {
            sendResponse(401, "Token inválido o expirado");
        }
        
        // Limpiar cookie
        $jwt->clearCookie();
        
        // Respuesta exitosa
        sendResponse(200, "Logout exitoso");
        
    } else {
        sendResponse(405, "Método no permitido");
    }
    
} catch (Exception $e) {
    error_log("Error en Account/Logout: " . $e->getMessage());
    sendResponse(500, "Error interno del servidor");
}
?>
