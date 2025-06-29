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
include_once '../../config/database.php';
include_once '../../config/database_manager.php';
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
        
        // Obtener favoritos del usuario (si existe la tabla)
        $favorites = [];
        try {
            $favoritesQuery = "SELECT f.ConstellationId, c.name as ConstellationName 
                              FROM Favorites f 
                              INNER JOIN constellations c ON f.ConstellationId = c.id 
                              WHERE f.UserId = :userId";
            $favoritesStmt = $dbNexusUsers->prepare($favoritesQuery);
            $favoritesStmt->bindParam(":userId", $userId);
            $favoritesStmt->execute();
            $favoriteIds = $favoritesStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Obtener datos completos de las constelaciones desde nexus_stars
            if (!empty($favoriteIds)) {
                $constellationIds = array_column($favoriteIds, 'ConstellationId');
                $placeholders = str_repeat('?,', count($constellationIds) - 1) . '?';
                $constellationsQuery = "SELECT id, name FROM constellations WHERE id IN ({$placeholders})";
                $constellationsStmt = $dbNexusStars->prepare($constellationsQuery);
                $constellationsStmt->execute($constellationIds);
                $favorites = $constellationsStmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (Exception $e) {
            // Las tablas Favorites o constellations no existen, continuar sin favoritos
            error_log("Error obteniendo favoritos: " . $e->getMessage());
        }
        
        // Obtener comentarios del usuario (si existe la tabla)
        $comments = [];
        try {
            $commentsQuery = "SELECT Id, UserNick, ConstellationName, Comment, UserId, ConstellationId 
                             FROM Comments 
                             WHERE UserId = :userId";
            $commentsStmt = $dbNexusUsers->prepare($commentsQuery);
            $commentsStmt->bindParam(":userId", $userId);
            $commentsStmt->execute();
            $comments = $commentsStmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            // La tabla Comments no existe, continuar sin comentarios
            error_log("Error obteniendo comentarios: " . $e->getMessage());
        }
        
        // Preparar respuesta con información del perfil
        $profileData = [
            'Nick' => $user->nick,
            'Name' => $user->name ?? '',
            'Surname1' => $user->surname1 ?? '',
            'Surname2' => '', // ASP.NET Identity no tiene surname2 por defecto
            'Email' => $user->email,
            'PhoneNumber' => $user->phone_number ?? '',
            'ProfileImage' => $user->profile_image ?? '',
            'Bday' => $user->birthday ?? '',
            'About' => $user->about ?? '',
            'UserLocation' => $user->user_location ?? '',
            'PublicProfile' => $user->public_profile ?? true,
            'Favorites' => $favorites,
            'Comments' => $comments
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
