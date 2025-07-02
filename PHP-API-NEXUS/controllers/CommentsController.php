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
        require_once 'repositories/UserRepository.php';
        require_once 'repositories/CommentsRepository.php';
        require_once 'models/Constellation.php';
        
        // Usar las bases de datos correspondientes como en ASP.NET
        $this->userRepository = new UserRepository($this->dbManager->getConnection('NexusUsers'));
        $this->commentsRepository = new CommentsRepository($this->dbManager->getConnection('NexusUsers'));
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
            $comments = $this->commentsRepository->findAll();
            
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
            $comment = $this->commentsRepository->findById($commentId);
            
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
            $comments = $this->commentsRepository->findByUserId($userId);
            
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
            
            // Obtener datos del cuerpo de la petición (JSON o multipart/form-data)
            $input = $this->getRequestData();
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
            
            // Validar comentario
            $commentValidation = $this->validateComment($updateData['comment']);
            if (!$commentValidation['valid']) {
                $this->sendResponse(400, $commentValidation['message'], null, false);
                return;
            }
            
            // Sanitizar comentario
            $updateData['comment'] = $this->sanitizeComment($updateData['comment']);
            
            if ($this->commentsRepository->update($commentId, $updateData['userId'], $updateData['comment'])) {
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
            
            // Obtener datos del comentario (JSON o multipart/form-data)
            $input = $this->getRequestData();
            if (!$input) {
                $this->sendResponse(400, "Datos de comentario requeridos", null, false);
                return;
            }
            
            if (!isset($input['constellationId'])) {
                $this->sendResponse(400, "ID de constelación requerido", null, false);
                return;
            }
            
            $constellationId = intval($input['constellationId']);
            $commentText = $input['comment'] ?? '';
            
            // Validaciones usando métodos del controlador
            $commentValidation = $this->validateComment($commentText);
            if (!$commentValidation['valid']) {
                $this->sendResponse(400, $commentValidation['message'], null, false);
                return;
            }
            
            $userValidation = $this->validateUserId($currentUserId);
            if (!$userValidation['valid']) {
                $this->sendResponse(400, $userValidation['message'], null, false);
                return;
            }
            
            $constellationValidation = $this->validateConstellationId($constellationId);
            if (!$constellationValidation['valid']) {
                $this->sendResponse(400, $constellationValidation['message'], null, false);
                return;
            }
            
            // Verificar que la constelación existe (como en ASP.NET)
            $constellation = $this->constellationsRepository->getById($constellationId);
            if (!$constellation) {
                $this->sendResponse(404, "ERROR: La constelación no existe.", null, false);
                return;
            }
            
            // Sanitizar comentario
            $commentText = $this->sanitizeComment($commentText);
            
            // Preparar datos del comentario
            $commentData = [
                'userId' => $currentUserId,
                'userNick' => $user->nick ?? $user->userName ?? 'Usuario',
                'constellationName' => $constellation['latin_name'], // Como en ASP.NET
                'comment' => $commentText,
                'constellationId' => $constellationId
            ];
            
            // Crear el comentario
            $newCommentId = $this->commentsRepository->create($commentData);
            
            if ($newCommentId) {
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
            $comment = $this->commentsRepository->findById($commentId);
            if (!$comment) {
                $this->sendResponse(404, "Comentario no encontrado", null, false);
                return;
            }
            
            // Eliminar el comentario
            if ($this->commentsRepository->delete($commentId, $currentUserId)) {
                $this->sendResponse(200, "Comentario eliminado exitosamente", null, true);
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
            // NO requiere autenticación - es público
            
            if (!isset($params['id'])) {
                $this->sendResponse(400, "ID de constelación requerido", null, false);
                return;
            }
            
            $constellationId = intval($params['id']);
            
            // Validar que el ID sea válido
            if ($constellationId <= 0) {
                $this->sendResponse(400, "ID de constelación inválido", null, false);
                return;
            }
            
            // Obtener comentarios de la constelación
            $comments = $this->commentsRepository->findByConstellationId($constellationId);
            
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
    
    // ===============================
    // MÉTODOS DE VALIDACIÓN Y LÓGICA DE NEGOCIO
    // Movidos desde el modelo para separar responsabilidades
    // ===============================
    
    /**
     * Validar comentario - Equivalente a DataAnnotations de ASP.NET
     */
    private function validateComment($comment) {
        if (empty($comment) || trim($comment) === '') {
            return ['valid' => false, 'message' => 'El comentario no puede estar vacío'];
        }
        
        // Longitud máxima (equivalente a StringLength de ASP.NET)
        if (strlen($comment) > 1000) {
            return ['valid' => false, 'message' => 'El comentario no puede exceder 1000 caracteres'];
        }
        
        return ['valid' => true, 'message' => 'Válido'];
    }
    
    /**
     * Validar ID de usuario - Equivalente a [Required] de ASP.NET
     */
    private function validateUserId($userId) {
        if (empty($userId) || !is_string($userId)) {
            return ['valid' => false, 'message' => 'ID de usuario inválido'];
        }
        return ['valid' => true, 'message' => 'Válido'];
    }
    
    /**
     * Validar ID de constelación - Equivalente a [Required] de ASP.NET
     */
    private function validateConstellationId($constellationId) {
        if (empty($constellationId) || (!is_numeric($constellationId) && !is_int($constellationId))) {
            return ['valid' => false, 'message' => 'ID de constelación inválido'];
        }
        return ['valid' => true, 'message' => 'Válido'];
    }
    
    /**
     * Sanitizar texto del comentario
     * Equivalente a la validación de entrada de ASP.NET
     */
    private function sanitizeComment($comment) {
        // Limpiar HTML y caracteres especiales
        $comment = htmlspecialchars($comment, ENT_QUOTES, 'UTF-8');
        $comment = trim($comment);
        return $comment;
    }
    
    /**
     * Validar que la constelación existe
     */
    private function validateConstellationExists($constellationId) {
        try {
            $constellation = $this->constellationsRepository->findById($constellationId);
            return $constellation !== false;
        } catch (Exception $e) {
            error_log("Error validando constelación: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validar que el usuario existe
     */
    private function validateUserExists($userId) {
        try {
            $user = $this->userRepository->findById($userId);
            return $user !== false;
        } catch (Exception $e) {
            error_log("Error validando usuario: " . $e->getMessage());
            return false;
        }
    }
}
?>
