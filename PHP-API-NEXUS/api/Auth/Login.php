<?php
// Configuración estricta para API JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Limpiar cualquier salida previa
if (ob_get_level()) {
    ob_clean();
}

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
include_once '../../config/database.php';
include_once '../../config/jwt.php';
include_once '../../models/User.php';

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

// Función para validar email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

try {
    // Instanciar la base de datos y obtener conexión
    $database = new Database();
    $db = $database->getConnection();
    
    // Instanciar objetos
    $user = new User($db);
    $jwt = new JWTHandler();
    
    // Obtener método HTTP
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'POST') {
        // Obtener datos JSON del request
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        if (!$data) {
            sendResponse(400, "Datos JSON inválidos");
        }
        
        $email = isset($data['email']) ? trim($data['email']) : '';
        $password = isset($data['password']) ? trim($data['password']) : '';
        
        // Validar datos requeridos
        if (empty($email) || empty($password)) {
            sendResponse(400, "Email y contraseña son requeridos");
        }
        
        // Validar formato de email
        if (!isValidEmail($email)) {
            sendResponse(400, "Formato de email inválido");
        }
        
        // Buscar usuario por email
        if (!$user->findByEmail($email)) {
            sendResponse(401, "Credenciales inválidas");
        }
        
        // Verificar si el usuario está verificado
        if (!$user->is_verified) {
            sendResponse(403, "Email no verificado. Por favor revisa tu correo.");
        }
        
        // Verificar contraseña
        if (!$user->verifyPassword($password)) {
            sendResponse(401, "Credenciales inválidas");
        }
        
        // Generar token JWT
        $token = $jwt->generateToken($user->id, $user->email, $user->nick);
        
        // Establecer cookie con el token
        $jwt->setCookie($token);
        
        // Respuesta exitosa
        sendResponse(200, "Login exitoso", [
            'user' => [
                'id' => $user->id,
                'nick' => $user->nick,
                'email' => $user->email,
                'name' => $user->name,
                'surname1' => $user->surname1
            ],
            'token' => $token
        ]);
        
    } else {
        sendResponse(405, "Método no permitido");
    }
    
} catch (Exception $e) {
    error_log("Error en Auth/Login: " . $e->getMessage());
    sendResponse(500, "Error interno del servidor");
}
?>
