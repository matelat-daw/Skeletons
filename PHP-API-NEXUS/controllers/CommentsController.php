<?php
/**
 * CommentsController - Maneja operaciones de comentarios
 */
require_once 'BaseController.php';

class CommentsController extends BaseController {
    private $comments;
    private $constellation;
    
    public function __construct() {
        parent::__construct();
        require_once 'models/Comments.php';
        require_once 'models/Constellation.php';
        
        $this->comments = new Comments($this->dbManager->getNexusUsersConnection());
        $this->constellation = new Constellation($this->dbManager->getNexusStarsConnection());
    }
    
    /**
     * GET /api/Account/Comments
     * Obtiene todos los comentarios del usuario
     */
    public function getUserComments($params = []) {
        try {
            // Requiere autenticación
            $tokenData = $this->requireAuth();
            $userId = $tokenData['user_id'];
            
            // Obtener comentarios del usuario
            $userComments = $this->comments->getUserComments($userId);
            
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
            
            $this->sendResponse(200, "Comentarios obtenidos correctamente", $formattedComments, true);
            
        } catch (Exception $e) {
            error_log("Error en getUserComments: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor", null, false);
        }
    }
    
    /**
     * GET /api/Account/Comments/{id}
     * Obtiene un comentario específico por ID
     */
    public function getComment($params = []) {
        try {
            // Requiere autenticación
            $tokenData = $this->requireAuth();
            $userId = $tokenData['user_id'];
            
            // Validar ID de comentario
            if (!isset($params['id'])) {
                $this->sendResponse(400, "ID de comentario requerido", null, false);
            }
            
            $commentId = $this->validateId($params['id']);
            
            // Obtener comentario por ID
            if ($this->comments->getById($commentId)) {
                // Verificar que el comentario pertenece al usuario
                if ($this->comments->user_id !== $userId) {
                    $this->sendResponse(403, "No tienes permisos para ver este comentario", null, false);
                }
                
                $commentData = [
                    "id" => intval($this->comments->id),
                    "userId" => $this->comments->user_id,
                    "userNick" => $this->comments->user_nick,
                    "comment" => $this->comments->comment,
                    "constellationId" => intval($this->comments->constellation_id),
                    "constellationName" => $this->comments->constellation_name
                ];
                
                $this->sendResponse(200, "Comentario obtenido correctamente", $commentData, true);
            } else {
                $this->sendResponse(404, "Comentario no encontrado", null, false);
            }
            
        } catch (Exception $e) {
            error_log("Error en getComment: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor", null, false);
        }
    }
    
    /**
     * POST /api/Account/Comments
     * Agrega un nuevo comentario
     */
    public function addComment($params = []) {
        try {
            // Requiere autenticación
            $tokenData = $this->requireAuth();
            $userId = $tokenData['user_id'];
            $userNick = $tokenData['nick'] ?? $tokenData['username'] ?? 'Usuario';
            
            // Obtener datos de entrada
            $input = $this->getJsonInput();
            
            if (!$input) {
                $this->sendResponse(400, "Datos de entrada requeridos", null, false);
            }
            
            // Validar campos requeridos
            $this->validateRequired($input, ['constellationId', 'comment']);
            
            $constellationId = $this->validateId($input['constellationId']);
            $commentText = $this->sanitizeString($input['comment']);
            
            // Validar que el comentario no esté vacío
            if (empty($commentText)) {
                $this->sendResponse(400, "El comentario no puede estar vacío", null, false);
            }
            
            // Obtener información de la constelación
            $constellationData = $this->constellation->getById($constellationId);
            if (!$constellationData) {
                $this->sendResponse(404, "Constelación no encontrada", null, false);
            }
            
            $constellationName = $constellationData['english_name'] ?? 'Desconocida';
            
            // Agregar el comentario
            if ($this->comments->addComment($userId, $userNick, $commentText, $constellationId, $constellationName)) {
                $responseData = [
                    "id" => intval($this->comments->id),
                    "userId" => $this->comments->user_id,
                    "userNick" => $this->comments->user_nick,
                    "comment" => $this->comments->comment,
                    "constellationId" => intval($this->comments->constellation_id),
                    "constellationName" => $this->comments->constellation_name
                ];
                
                $this->sendResponse(200, "Comentario agregado correctamente", $responseData, true);
            } else {
                $this->sendResponse(500, "Error al agregar comentario", null, false);
            }
            
        } catch (Exception $e) {
            error_log("Error en addComment: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor", null, false);
        }
    }
    
    /**
     * PUT /api/Account/Comments/{id}
     * Actualiza un comentario existente
     */
    public function updateComment($params = []) {
        try {
            // Requiere autenticación
            $tokenData = $this->requireAuth();
            $userId = $tokenData['user_id'];
            
            // Validar ID de comentario
            if (!isset($params['id'])) {
                $this->sendResponse(400, "ID de comentario requerido", null, false);
            }
            
            $commentId = $this->validateId($params['id']);
            
            // Obtener datos de entrada
            $input = $this->getJsonInput();
            
            if (!$input) {
                $this->sendResponse(400, "Datos de entrada requeridos", null, false);
            }
            
            // Validar campos requeridos
            $this->validateRequired($input, ['comment']);
            
            $newComment = $this->sanitizeString($input['comment']);
            
            // Validar que el comentario no esté vacío
            if (empty($newComment)) {
                $this->sendResponse(400, "El comentario no puede estar vacío", null, false);
            }
            
            // Verificar que el comentario existe y pertenece al usuario
            if (!$this->comments->isOwner($commentId, $userId)) {
                $this->sendResponse(403, "No tienes permisos para modificar este comentario", null, false);
            }
            
            // Actualizar el comentario
            if ($this->comments->updateComment($commentId, $userId, $newComment)) {
                $this->sendResponse(200, "Comentario actualizado correctamente", null, true);
            } else {
                $this->sendResponse(500, "Error al actualizar comentario", null, false);
            }
            
        } catch (Exception $e) {
            error_log("Error en updateComment: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor", null, false);
        }
    }
    
    /**
     * DELETE /api/Account/Comments/{id}
     * Elimina un comentario
     */
    public function deleteComment($params = []) {
        try {
            // Requiere autenticación
            $tokenData = $this->requireAuth();
            $userId = $tokenData['user_id'];
            
            // Validar ID de comentario
            if (!isset($params['id'])) {
                $this->sendResponse(400, "ID de comentario requerido", null, false);
            }
            
            $commentId = $this->validateId($params['id']);
            
            // Verificar que el comentario existe y pertenece al usuario
            if (!$this->comments->isOwner($commentId, $userId)) {
                $this->sendResponse(403, "No tienes permisos para eliminar este comentario", null, false);
            }
            
            // Eliminar el comentario
            if ($this->comments->deleteComment($commentId, $userId)) {
                $this->sendResponse(200, "Comentario eliminado correctamente", null, true);
            } else {
                $this->sendResponse(500, "Error al eliminar comentario", null, false);
            }
            
        } catch (Exception $e) {
            error_log("Error en deleteComment: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor", null, false);
        }
    }
}
?>
