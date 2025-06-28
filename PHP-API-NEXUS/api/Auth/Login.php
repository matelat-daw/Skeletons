<?php
// Headers para CORS y tipo de contenido
header("Access-Control-Allow-Origin: http://localhost:4200"); // Ajustar según tu frontend
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Incluir archivos necesarios
include_once '../config/database.php';
include_once '../config/jwt.php';
include_once '../models/User.php';

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
