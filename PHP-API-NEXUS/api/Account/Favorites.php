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
include_once '../../config/database_manager.php';
include_once '../../config/jwt.php';
include_once '../../models/User.php';
include_once '../../models/Favorites.php';

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
    // Instanciar el administrador de bases de datos
    $dbManager = new DatabaseManager();
    $dbNexusUsers = $dbManager->getNexusUsersConnection();
    $dbNexusStars = $dbManager->getNexusStarsConnection();
    
    // Instanciar objetos
    $user = new User($dbNexusUsers);
    $favorites = new Favorites($dbNexusUsers);
    $jwt = new JWTHandler();
    
    // Obtener método HTTP
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Obtener token desde cookie
    $token = $jwt->getTokenFromCookie();
    
    if (!$token) {
        sendResponse(401, "No hay sesión activa");
    }
    
    // Validar token y obtener datos del usuario
    $tokenData = $jwt->validateToken($token);
    
    if (!$tokenData) {
        sendResponse(401, "Token inválido o expirado");
    }
    
    $userId = $tokenData['user_id'];
    
    if ($method === 'POST') {
        // Agregar favorito
        
        // Obtener constellation ID desde la URL
        $pathInfo = $_SERVER['PATH_INFO'] ?? '';
        $pathParts = explode('/', trim($pathInfo, '/'));
        
        if (empty($pathParts) || !is_numeric($pathParts[0])) {
            sendResponse(400, "ID de constelación requerido");
        }
        
        $constellationId = intval($pathParts[0]);
        
        // Verificar que la constelación existe
        include_once '../../models/Constellation.php';
        $constellation = new Constellation($dbNexusStars);
        
        if (!$constellation->getById($constellationId)) {
            sendResponse(404, "Constelación no encontrada");
        }
        
        // Agregar a favoritos
        if ($favorites->addFavorite($userId, $constellationId)) {
            sendResponse(200, "Constelación agregada a favoritos", [
                'id' => $favorites->id,
                'userId' => $userId,
                'constellationId' => $constellationId,
                'constellationName' => $constellation->english_name
            ]);
        } else {
            // Verificar si ya es favorito
            if ($favorites->isFavorite($userId, $constellationId)) {
                sendResponse(409, "Esta constelación ya está en tus favoritos");
            } else {
                sendResponse(500, "Error al agregar a favoritos");
            }
        }
        
    } else if ($method === 'DELETE') {
        // Eliminar favorito
        
        // Obtener constellation ID desde la URL
        $pathInfo = $_SERVER['PATH_INFO'] ?? '';
        $pathParts = explode('/', trim($pathInfo, '/'));
        
        if (empty($pathParts) || !is_numeric($pathParts[0])) {
            sendResponse(400, "ID de constelación requerido");
        }
        
        $constellationId = intval($pathParts[0]);
        
        // Eliminar de favoritos
        if ($favorites->removeFavorite($userId, $constellationId)) {
            sendResponse(200, "Constelación eliminada de favoritos");
        } else {
            sendResponse(404, "Favorito no encontrado");
        }
        
    } else if ($method === 'GET') {
        // Verificar si es favorito o obtener todos los favoritos
        
        $pathInfo = $_SERVER['PATH_INFO'] ?? '';
        $pathParts = explode('/', trim($pathInfo, '/'));
        
        if (!empty($pathParts) && is_numeric($pathParts[0])) {
            // Verificar si una constelación específica es favorito
            $constellationId = intval($pathParts[0]);
            $isFavorite = $favorites->isFavorite($userId, $constellationId);
            
            sendResponse(200, $isFavorite ? "Es favorito" : "No es favorito", [
                'isFavorite' => $isFavorite,
                'constellationId' => $constellationId
            ]);
        } else {
            // Obtener todos los favoritos del usuario
            $userFavorites = $favorites->getUserFavorites($userId);
            $stats = $favorites->getUserFavoritesStats($userId);
            
            sendResponse(200, "Favoritos obtenidos exitosamente", [
                'favorites' => $userFavorites,
                'stats' => $stats
            ]);
        }
        
    } else {
        sendResponse(405, "Método no permitido");
    }
    
} catch (Exception $e) {
    error_log("Error en Account/Favorites: " . $e->getMessage());
    sendResponse(500, "Error interno del servidor");
}
?>
