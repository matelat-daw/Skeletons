<?php
/**
 * CommentsController - Maneja operaciones de comentarios
 * Equivalente al CommentsController de ASP.NET Core
 */
require_once 'BaseController.php';

class CommentsController extends BaseController {
    private $userRepository;
    private $commentsRepository;
    private $constellationsRepository;
    
    public function __construct() {
        parent::__construct();
        require_once 'models/UserRepository.php';
        require_once 'models/Comments.php';
        require_once 'models/Constellation.php';
        
        // Usar las bases de datos correspondientes como en ASP.NET
        $this->userRepository = new UserRepository($this->dbManager->getConnection('NexusUsers'));
        $this->commentsRepository = new Comments($this->dbManager->getConnection('NexusUsers'));
        $this->constellationsRepository = new Constellation($this->dbManager->getConnection('nexus_stars'));
    }
    
    /**
     * GET: api/Comments
     * Obtiene todos los comentarios (equivalente a GetAllComments)
     */
    public function getAllComments($params = []) {
        try {
            // En ASP.NET requiere autenticación Bearer
            $tokenData = $this->requireAuth();
            
            // Obtener todos los comentarios
            $comments = $this->commentsRepository->getAllComments();
            
            // Formatear para respuesta JSON
            $formattedComments = array_map(function($comment) {
                return [
                    "id" => intval($comment['Id']),
                    "userId" => $comment['UserId'],
                    "constellationId" => intval($comment['ConstellationId']),
                    "constellationName" => $comment['ConstellationName'],
                    "comment" => $comment['Comment'],
                    "userNick" => $comment['UserNick'] ?? null
                ];
            }, $comments);
            
            $this->sendResponse(200, "Comentarios obtenidos exitosamente", $formattedComments, true);
            
        } catch (Exception $e) {
            error_log("Error en getAllComments: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor", null, false);
        }
    }
    
    /**
     * GET: api/Comments/ById/{id}
     * Obtiene un comentario por ID (equivalente a GetCommentById)
     */
    public function getCommentById($params = []) {
        try {
            // Requiere autenticación
            $tokenData = $this->requireAuth();
            
            if (!isset($params['id'])) {
                $this->sendResponse(400, "ID de comentario requerido", null, false);
                return;
            }
            
            $commentId = intval($params['id']);
            
            // Buscar comentario por ID
            $comment = $this->commentsRepository->getById($commentId);
            
            if (!$comment) {
                $this->sendResponse(404, "Comentario no encontrado", null, false);
                return;
            }
            
            $commentData = [
                "id" => intval($comment['Id']),
                "userId" => $comment['UserId'],
                "constellationId" => intval($comment['ConstellationId']),
                "constellationName" => $comment['ConstellationName'],
                "comment" => $comment['Comment'],
                "userNick" => $comment['UserNick'] ?? null
            ];
            
            $this->sendResponse(200, "Comentario obtenido exitosamente", $commentData, true);
            
        } catch (Exception $e) {
            error_log("Error en getCommentById: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor", null, false);
        }
    }
    
    /**
     * GET: api/Comments/User/{userId}
     * Obtiene comentarios por usuario (equivalente a GetCommentsByUser)
     */
    public function getCommentsByUser($params = []) {
        try {
            // Requiere autenticación
            $tokenData = $this->requireAuth();
            
            if (!isset($params['userId'])) {
                $this->sendResponse(400, "ID de usuario requerido", null, false);
                return;
            }
            
            $userId = $params['userId'];
            
            // Obtener comentarios del usuario
            $comments = $this->commentsRepository->getUserComments($userId);
            
            if (empty($comments)) {
                $this->sendResponse(404, "No se encontraron comentarios para este usuario", null, false);
                return;
            }
            
            // Formatear para respuesta JSON
            $formattedComments = array_map(function($comment) {
                return [
                    "id" => intval($comment['Id']),
                    "userId" => $comment['UserId'],
                    "constellationId" => intval($comment['ConstellationId']),
                    "constellationName" => $comment['ConstellationName'],
                    "comment" => $comment['Comment'],
                    "userNick" => $comment['UserNick'] ?? null
                ];
            }, $comments);
            
            $this->sendResponse(200, "Comentarios del usuario obtenidos exitosamente", $formattedComments, true);
            
        } catch (Exception $e) {
            error_log("Error en getCommentsByUser: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor", null, false);
        }
    }
    
    /**
     * PUT: api/Comments/{id}
     * Actualiza un comentario (equivalente a PutComment)
     */
    public function putComment($params = []) {
        try {
            // Requiere autenticación
            $tokenData = $this->requireAuth();
            
            if (!isset($params['id'])) {
                $this->sendResponse(400, "ID de comentario requerido", null, false);
                return;
            }
            
            $commentId = intval($params['id']);
            
            // Obtener datos del cuerpo de la petición
            $input = $this->getJsonInput();
            if (!$input) {
                $this->sendResponse(400, "Datos de comentario requeridos", null, false);
                return;
            }
            
            // Validar que el ID coincida (como en ASP.NET)
            if (isset($input['id']) && intval($input['id']) !== $commentId) {
                $this->sendResponse(400, "El ID del comentario no coincide", null, false);
                return;
            }
            
            // Verificar que el comentario existe
            $existingComment = $this->commentsRepository->getById($commentId);
            if (!$existingComment) {
                $this->sendResponse(404, "Comentario no encontrado", null, false);
                return;
            }
            
            // Actualizar comentario
            $updateData = [
                'id' => $commentId,
                'userId' => $input['userId'] ?? $existingComment['UserId'],
                'constellationId' => $input['constellationId'] ?? $existingComment['ConstellationId'],
                'constellationName' => $input['constellationName'] ?? $existingComment['ConstellationName'],
                'comment' => $input['comment'] ?? $existingComment['Comment'],
                'userNick' => $input['userNick'] ?? $existingComment['UserNick']
            ];
            
            if ($this->commentsRepository->updateComment($commentId, $updateData['userId'], $updateData['comment'])) {
                // Respuesta 204 No Content como en ASP.NET
                http_response_code(204);
                exit();
            } else {
                $this->sendResponse(500, "Error al actualizar el comentario", null, false);
            }
            
        } catch (Exception $e) {
            error_log("Error en putComment: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor", null, false);
        }
    }
    
    /**
     * POST: api/Comments
     * Crea un nuevo comentario (equivalente a PostComment)
     */
    public function postComment($params = []) {
        try {
            // Obtener información del token (equivalente a GetUserFromTokenAsync)
            $tokenData = $this->requireAuth();
            $currentUserId = $tokenData['user_id'];
            
            // Verificar que el usuario existe
            $user = $this->userRepository->findById($currentUserId);
            if (!$user) {
                $this->sendResponse(404, "ERROR: Ese Usuario no Existe.", null, false);
                return;
            }
            
            // Obtener datos del comentario
            $input = $this->getJsonInput();
            if (!$input) {
                $this->sendResponse(400, "Datos de comentario requeridos", null, false);
                return;
            }
            
            if (!isset($input['constellationId'])) {
                $this->sendResponse(400, "ID de constelación requerido", null, false);
                return;
            }
            
            $constellationId = intval($input['constellationId']);
            
            // Verificar que la constelación existe (como en ASP.NET)
            $constellation = $this->constellationsRepository->getById($constellationId);
            if (!$constellation) {
                $this->sendResponse(404, "ERROR: La constelación no existe.", null, false);
                return;
            }
            
            // Preparar datos del comentario
            $commentData = [
                'userId' => $currentUserId,
                'constellationId' => $constellationId,
                'constellationName' => $constellation['latin_name'], // Como en ASP.NET
                'comment' => $input['comment'] ?? '',
                'userNick' => $user->nick ?? $user->userName ?? 'Usuario'
            ];
            
            // Crear el comentario
            if ($this->commentsRepository->addComment(
                $commentData['userId'],
                $commentData['userNick'],
                $commentData['comment'],
                $commentData['constellationId'],
                $commentData['constellationName']
            )) {
                $newCommentId = $this->commentsRepository->id;
                
                // Respuesta CreatedAtAction como en ASP.NET
                $responseData = [
                    "id" => intval($newCommentId),
                    "userId" => $commentData['userId'],
                    "constellationId" => $commentData['constellationId'],
                    "constellationName" => $commentData['constellationName'],
                    "comment" => $commentData['comment'],
                    "userNick" => $commentData['userNick']
                ];
                
                $this->sendResponse(201, "Comentario creado exitosamente", $responseData, true);
            } else {
                $this->sendResponse(500, "Error al crear el comentario", null, false);
            }
            
        } catch (Exception $e) {
            error_log("Error en postComment: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor", null, false);
        }
    }
    
    /**
     * DELETE: api/Comments/{id}
     * Elimina un comentario (equivalente a DeleteComment)
     */
    public function deleteComment($params = []) {
        try {
            // Obtener información del token (equivalente a GetUserFromTokenAsync)
            $tokenData = $this->requireAuth();
            $currentUserId = $tokenData['user_id'];
            
            // Verificar que el usuario existe
            $user = $this->userRepository->findById($currentUserId);
            if (!$user) {
                $this->sendResponse(404, "ERROR: Ese Usuario no Existe.", null, false);
                return;
            }
            
            if (!isset($params['id'])) {
                $this->sendResponse(400, "ID de comentario requerido", null, false);
                return;
            }
            
            $commentId = intval($params['id']);
            
            // Buscar el comentario
            $comment = $this->commentsRepository->getById($commentId);
            if (!$comment) {
                $this->sendResponse(404, "Comentario no encontrado", null, false);
                return;
            }
            
            // Eliminar el comentario
            if ($this->commentsRepository->deleteComment($commentId, $currentUserId)) {
                // Respuesta 204 No Content como en ASP.NET
                http_response_code(204);
                exit();
            } else {
                $this->sendResponse(500, "Error al eliminar el comentario", null, false);
            }
            
        } catch (Exception $e) {
            error_log("Error en deleteComment: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor", null, false);
        }
    }
    
    /**
     * GET: api/Comments/Constellation/{id}
     * Obtiene comentarios por constelación (método adicional útil)
     */
    public function getCommentsByConstellation($params = []) {
        try {
            // Requiere autenticación
            $tokenData = $this->requireAuth();
            
            if (!isset($params['id'])) {
                $this->sendResponse(400, "ID de constelación requerido", null, false);
                return;
            }
            
            $constellationId = intval($params['id']);
            
            // Obtener comentarios de la constelación
            $comments = $this->commentsRepository->getByConstellationId($constellationId);
            
            // Formatear para respuesta JSON
            $formattedComments = array_map(function($comment) {
                return [
                    "id" => intval($comment['Id']),
                    "userId" => $comment['UserId'],
                    "constellationId" => intval($comment['ConstellationId']),
                    "constellationName" => $comment['ConstellationName'],
                    "comment" => $comment['Comment'],
                    "userNick" => $comment['UserNick'] ?? null
                ];
            }, $comments);
            
            $this->sendResponse(200, "Comentarios de la constelación obtenidos exitosamente", $formattedComments, true);
            
        } catch (Exception $e) {
            error_log("Error en getCommentsByConstellation: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor", null, false);
        }
    }
}
?>
