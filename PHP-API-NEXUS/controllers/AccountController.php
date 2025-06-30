<?php
/**
 * AccountController - Maneja operaciones de cuenta y perfil
 */
require_once 'BaseController.php';

class AccountController extends BaseController {
    private $userRepository;
    private $favorites;
    private $comments;
    
    public function __construct() {
        parent::__construct();
        require_once 'models/UserRepository.php';
        require_once 'models/Favorites.php';
        require_once 'models/Comments.php';
        
        $this->userRepository = new UserRepository($this->dbManager->getNexusUsersConnection());
        $this->favorites = new Favorites($this->dbManager->getNexusUsersConnection());
        $this->comments = new Comments($this->dbManager->getNexusUsersConnection());
    }
    
    /**
     * GET /api/Account/Profile
     * Obtiene el perfil completo del usuario autenticado
     */
    public function getProfile($params = []) {
        try {
            // Requiere autenticación
            $tokenData = $this->requireAuth();
            
            $userId = $tokenData['user_id'];
            $userEmail = $tokenData['email'];
            
            // Buscar usuario por email con perfil completo
            $user = $this->userRepository->findByEmail($userEmail, true);
            if (!$user) {
                $this->sendResponse(404, "Usuario no encontrado", null, false);
            }
            
            // Obtener favoritos del usuario
            $userFavorites = [];
            try {
                $favoritesData = $this->favorites->getUserFavorites($userId);
                foreach ($favoritesData as $fav) {
                    $userFavorites[] = [
                        'id' => $fav['ConstellationId'],
                        'name' => $fav['ConstellationName'] ?? '',
                        'english_name' => $fav['ConstellationName'] ?? ''
                    ];
                }
            } catch (Exception $e) {
                error_log("Error obteniendo favoritos: " . $e->getMessage());
            }
            
            // Obtener comentarios del usuario
            $userComments = [];
            try {
                $commentsData = $this->comments->getUserComments($userId);
                foreach ($commentsData as $comment) {
                    $userComments[] = [
                        'id' => intval($comment['Id']),
                        'userNick' => $comment['UserNick'],
                        'comment' => $comment['Comment'],
                        'constellationId' => intval($comment['ConstellationId']),
                        'constellationName' => $comment['ConstellationName']
                    ];
                }
            } catch (Exception $e) {
                error_log("Error obteniendo comentarios: " . $e->getMessage());
            }
            
            // Preparar respuesta usando el método toArray del modelo User
            $profileData = $user->toArray();
            $profileData['favorites'] = $userFavorites;
            $profileData['comments'] = $userComments;
            
            $this->sendResponse(200, "Perfil obtenido exitosamente", $profileData, true);
            
        } catch (Exception $e) {
            error_log("Error en getProfile: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor", null, false);
        }
    }
    
    /**
     * POST /api/Account/Logout
     * Cierra la sesión del usuario
     */
    public function logout($params = []) {
        try {
            // Eliminar cookie JWT
            $this->jwt->clearTokenCookie();
            
            $this->sendResponse(200, "Sesión cerrada exitosamente", null, true);
            
        } catch (Exception $e) {
            error_log("Error en logout: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor", null, false);
        }
    }
    
    /**
     * PATCH /api/Account/Update
     * Actualiza el perfil del usuario
     */
    public function updateProfile($params = []) {
        try {
            // Requiere autenticación
            $tokenData = $this->requireAuth();
            
            // Obtener datos del formulario
            $data = $_POST;
            
            if (empty($data)) {
                $this->sendResponse(400, "Datos de actualización requeridos", null, false);
            }
            
            // Validar campos obligatorios
            $this->validateRequired($data, ['Name', 'Nick', 'Email', 'Surname1']);
            
            // Buscar usuario
            $userId = $tokenData['user_id'];
            if (!$this->user->findById($userId)) {
                $this->sendResponse(404, "Usuario no encontrado", null, false);
            }
            
            // Actualizar campos
            $this->user->name = $this->sanitizeString($data['Name']);
            $this->user->nick = $this->sanitizeString($data['Nick']);
            $this->user->email = $this->sanitizeString($data['Email']);
            $this->user->surname1 = $this->sanitizeString($data['Surname1']);
            $this->user->surname2 = isset($data['Surname2']) ? $this->sanitizeString($data['Surname2']) : '';
            $this->user->phone_number = isset($data['PhoneNumber']) ? $this->sanitizeString($data['PhoneNumber']) : '';
            $this->user->user_location = isset($data['UserLocation']) ? $this->sanitizeString($data['UserLocation']) : '';
            $this->user->about = isset($data['About']) ? $this->sanitizeString($data['About']) : '';
            $this->user->public_profile = isset($data['PublicProfile']) ? ($data['PublicProfile'] === '1') : true;
            
            // Manejar fecha de nacimiento
            if (isset($data['Bday']) && !empty($data['Bday'])) {
                $this->user->birthday = $data['Bday'];
            }
            
            // Actualizar en base de datos
            if ($this->user->update()) {
                $this->sendResponse(200, "Perfil actualizado exitosamente", null, true);
            } else {
                $this->sendResponse(500, "Error al actualizar perfil", null, false);
            }
            
        } catch (Exception $e) {
            error_log("Error en updateProfile: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor", null, false);
        }
    }
    
    /**
     * DELETE /api/Account/Delete
     * Elimina la cuenta del usuario
     */
    public function deleteAccount($params = []) {
        try {
            // Requiere autenticación
            $tokenData = $this->requireAuth();
            
            $userId = $tokenData['user_id'];
            
            // Buscar usuario
            if (!$this->user->findById($userId)) {
                $this->sendResponse(404, "Usuario no encontrado", null, false);
            }
            
            // Eliminar cuenta
            if ($this->user->delete()) {
                // Limpiar cookie
                $this->jwt->clearTokenCookie();
                
                $this->sendResponse(200, "Cuenta eliminada exitosamente", null, true);
            } else {
                $this->sendResponse(500, "Error al eliminar cuenta", null, false);
            }
            
        } catch (Exception $e) {
            error_log("Error en deleteAccount: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor", null, false);
        }
    }
}
?>
