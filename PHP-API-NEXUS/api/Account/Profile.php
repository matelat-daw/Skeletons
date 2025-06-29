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
include_once '../../config/database_manager.php';
include_once '../../config/jwt.php';
include_once '../../models/User.php';
include_once '../../models/Favorites.php';
include_once '../../models/Comments.php';

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
    $jwt = new JWTHandler();
    
    // Obtener método HTTP
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'GET') {
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
        
        // Obtener el usuario desde el token
        $userId = $tokenData['user_id'];
        $userEmail = $tokenData['email'];
        
        // Buscar usuario por email para obtener todos los datos del perfil
        if (!$user->findProfileByEmail($userEmail)) {
            sendResponse(404, "ERROR: Ese Usuario no Existe.");
        }
        
        // Obtener favoritos del usuario usando el modelo Favorites
        $favorites = [];
        try {
            $favoritesModel = new Favorites($dbNexusUsers);
            $userFavorites = $favoritesModel->getUserFavorites($userId);
            
            // Transformar a formato esperado por el frontend
            foreach ($userFavorites as $fav) {
                $favorites[] = [
                    'id' => $fav['ConstellationId'],
                    'name' => $fav['ConstellationName'] ?? '',
                    'english_name' => $fav['ConstellationName'] ?? ''
                ];
            }
        } catch (Exception $e) {
            // Error obteniendo favoritos, continuar sin favoritos
            error_log("Error obteniendo favoritos: " . $e->getMessage());
        }
        
        // Obtener comentarios del usuario usando el modelo Comments
        $comments = [];
        try {
            $commentsModel = new Comments($dbNexusUsers);
            $userComments = $commentsModel->getUserComments($userId);
            
            // Transformar a formato esperado por el frontend
            foreach ($userComments as $comment) {
                $comments[] = [
                    'id' => intval($comment['Id']),
                    'userNick' => $comment['UserNick'],
                    'comment' => $comment['Comment'],
                    'constellationId' => intval($comment['ConstellationId']),
                    'constellationName' => $comment['ConstellationName']
                ];
            }
        } catch (Exception $e) {
            // Error obteniendo comentarios, continuar sin comentarios
            error_log("Error obteniendo comentarios: " . $e->getMessage());
        }
        
        // Preparar respuesta con información del perfil (nombres en camelCase para frontend)
        $profileData = [
            'nick' => $user->nick ?? '',
            'name' => $user->name ?? '',
            'surname1' => $user->surname1 ?? '',
            'surname2' => $user->surname2 ?? '',
            'email' => $user->email ?? '',
            'phoneNumber' => !empty($user->phone_number) ? $user->phone_number : '',
            'profileImage' => $user->profile_image ?? '',
            'bday' => $user->birthday ?? '',
            'about' => $user->about ?? '',
            'userLocation' => $user->user_location ?? '',
            'publicProfile' => (bool)($user->public_profile ?? true),  // Convertir a boolean
            'favorites' => $favorites,
            'comments' => $comments
        ];
        
        // Respuesta exitosa
        sendResponse(200, "Perfil obtenido exitosamente", $profileData);
        
    } else {
        sendResponse(405, "Método no permitido");
    }
    
} catch (Exception $e) {
    error_log("Error en Account/Profile: " . $e->getMessage());
    sendResponse(500, "Error interno del servidor");
}
?>
