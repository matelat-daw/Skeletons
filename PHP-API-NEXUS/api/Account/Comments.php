<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Manejar preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Incluir archivos necesarios
require_once '../../config/database_manager.php';
require_once '../../config/jwt.php';
require_once '../../models/Comments.php';
require_once '../../models/Constellation.php';

try {
    // Verificar autenticación JWT
    $jwt = new JWT();
    $userData = $jwt->validateToken();
    
    if (!$userData) {
        http_response_code(401);
        echo json_encode([
            "message" => "Token inválido o expirado",
            "success" => false
        ]);
        exit();
    }

    $userId = $userData['user_id'];
    $userNick = $userData['nick'] ?? $userData['username'] ?? 'Usuario';

    // Obtener conexión a la base de datos de usuarios
    $databaseManager = new DatabaseManager();
    $database = $databaseManager->getConnection('users');
    $starsDatabase = $databaseManager->getConnection('stars');
    
    // Instanciar modelo de comentarios
    $comments = new Comments($database);
    $constellation = new Constellation($starsDatabase);

    $method = $_SERVER['REQUEST_METHOD'];
    $path = $_SERVER['REQUEST_URI'];
    
    // Extraer ID de comentario de la URL si existe
    $commentId = null;
    if (preg_match('/\/api\/Account\/Comments\/(\d+)/', $path, $matches)) {
        $commentId = intval($matches[1]);
    }

    switch ($method) {
        case 'GET':
            if ($commentId) {
                // Obtener comentario específico por ID
                if ($comments->getById($commentId)) {
                    // Verificar que el comentario pertenece al usuario
                    if ($comments->user_id !== $userId) {
                        http_response_code(403);
                        echo json_encode([
                            "message" => "No tienes permisos para ver este comentario",
                            "success" => false
                        ]);
                        exit();
                    }
                    
                    echo json_encode([
                        "message" => "Comentario obtenido correctamente",
                        "success" => true,
                        "data" => [
                            "id" => intval($comments->id),
                            "userId" => $comments->user_id,
                            "userNick" => $comments->user_nick,
                            "comment" => $comments->comment,
                            "constellationId" => intval($comments->constellation_id),
                            "constellationName" => $comments->constellation_name
                        ]
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode([
                        "message" => "Comentario no encontrado",
                        "success" => false
                    ]);
                }
            } else {
                // Obtener todos los comentarios del usuario
                $userComments = $comments->getUserComments($userId);
                
                // Formatear datos para el frontend
                $formattedComments = array_map(function($comment) {
                    return [
                        "id" => intval($comment['Id']),
                        "userId" => $comment['UserId'],
                        "userNick" => $comment['UserNick'],
                        "comment" => $comment['Comment'],
                        "constellationId" => intval($comment['ConstellationId']),
                        "constellationName" => $comment['ConstellationName']
                    ];
                }, $userComments);

                echo json_encode([
                    "message" => "Comentarios obtenidos correctamente",
                    "success" => true,
                    "data" => $formattedComments
                ]);
            }
            break;

        case 'POST':
            // Agregar nuevo comentario
            $input = json_decode(file_get_contents("php://input"), true);
            
            if (!$input || !isset($input['constellationId']) || !isset($input['comment'])) {
                http_response_code(400);
                echo json_encode([
                    "message" => "Datos requeridos: constellationId, comment",
                    "success" => false
                ]);
                exit();
            }

            $constellationId = intval($input['constellationId']);
            $commentText = trim($input['comment']);

            // Validar que el comentario no esté vacío
            if (empty($commentText)) {
                http_response_code(400);
                echo json_encode([
                    "message" => "El comentario no puede estar vacío",
                    "success" => false
                ]);
                exit();
            }

            // Obtener información de la constelación
            $constellationData = $constellation->getById($constellationId);
            if (!$constellationData) {
                http_response_code(404);
                echo json_encode([
                    "message" => "Constelación no encontrada",
                    "success" => false
                ]);
                exit();
            }

            $constellationName = $constellationData['english_name'] ?? 'Desconocida';

            // Agregar el comentario
            if ($comments->addComment($userId, $userNick, $commentText, $constellationId, $constellationName)) {
                echo json_encode([
                    "message" => "Comentario agregado correctamente",
                    "success" => true,
                    "data" => [
                        "id" => intval($comments->id),
                        "userId" => $comments->user_id,
                        "userNick" => $comments->user_nick,
                        "comment" => $comments->comment,
                        "constellationId" => intval($comments->constellation_id),
                        "constellationName" => $comments->constellation_name
                    ]
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    "message" => "Error al agregar comentario",
                    "success" => false
                ]);
            }
            break;

        case 'PUT':
            // Actualizar comentario existente
            if (!$commentId) {
                http_response_code(400);
                echo json_encode([
                    "message" => "ID de comentario requerido",
                    "success" => false
                ]);
                exit();
            }

            $input = json_decode(file_get_contents("php://input"), true);
            
            if (!$input || !isset($input['comment'])) {
                http_response_code(400);
                echo json_encode([
                    "message" => "Datos requeridos: comment",
                    "success" => false
                ]);
                exit();
            }

            $newComment = trim($input['comment']);

            // Validar que el comentario no esté vacío
            if (empty($newComment)) {
                http_response_code(400);
                echo json_encode([
                    "message" => "El comentario no puede estar vacío",
                    "success" => false
                ]);
                exit();
            }

            // Verificar que el comentario existe y pertenece al usuario
            if (!$comments->isOwner($commentId, $userId)) {
                http_response_code(403);
                echo json_encode([
                    "message" => "No tienes permisos para modificar este comentario",
                    "success" => false
                ]);
                exit();
            }

            // Actualizar el comentario
            if ($comments->updateComment($commentId, $userId, $newComment)) {
                echo json_encode([
                    "message" => "Comentario actualizado correctamente",
                    "success" => true
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    "message" => "Error al actualizar comentario",
                    "success" => false
                ]);
            }
            break;

        case 'DELETE':
            // Eliminar comentario
            if (!$commentId) {
                http_response_code(400);
                echo json_encode([
                    "message" => "ID de comentario requerido",
                    "success" => false
                ]);
                exit();
            }

            // Verificar que el comentario existe y pertenece al usuario
            if (!$comments->isOwner($commentId, $userId)) {
                http_response_code(403);
                echo json_encode([
                    "message" => "No tienes permisos para eliminar este comentario",
                    "success" => false
                ]);
                exit();
            }

            // Eliminar el comentario
            if ($comments->deleteComment($commentId, $userId)) {
                echo json_encode([
                    "message" => "Comentario eliminado correctamente",
                    "success" => true
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    "message" => "Error al eliminar comentario",
                    "success" => false
                ]);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode([
                "message" => "Método no permitido",
                "success" => false
            ]);
            break;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "message" => "Error del servidor: " . $e->getMessage(),
        "success" => false
    ]);
}
?>
