<?php
/**
 * AuthController - Maneja la autenticación de usuarios
 */
require_once 'BaseController.php';

class AuthController extends BaseController {
    private $userRepository;
    private $authService;
    
    public function __construct() {
        parent::__construct();
        require_once 'models/UserRepository.php';
        require_once 'services/AuthService.php';
        $this->userRepository = new UserRepository($this->dbManager->getNexusUsersConnection());
        $this->authService = new AuthService();
    }
    
    /**
     * POST /api/Auth/Login
     * Autentica un usuario y establece cookie JWT
     */
    public function login($params = []) {
        try {
            // Obtener datos de entrada
            $input = $this->getJsonInput();
            
            if (!$input) {
                $this->sendResponse(400, "Datos de entrada requeridos", null, false);
            }
            
            // Validar campos requeridos
            $this->validateRequired($input, ['email', 'password']);
            
            $email = $this->sanitizeString($input['email']);
            $password = $input['password']; // No sanitizar la contraseña
            
            // Validar credenciales básicas
            if (!AuthService::validateCredentials($email, $password)) {
                $this->sendResponse(400, "Formato de credenciales inválido", null, false);
            }
            
            // Buscar usuario por email
            $user = $this->userRepository->findByEmail($email);
            if (!$user) {
                $this->sendResponse(401, "Credenciales inválidas", null, false);
            }
            
            // Verificar que el usuario puede hacer login
            $loginCheck = AuthService::canLogin($user);
            if (!$loginCheck['can_login']) {
                $this->sendResponse(401, $loginCheck['reason'], null, false);
            }
            
            // Verificar contraseña
            if (!AuthService::verifyPassword($password, $user->password_hash)) {
                $this->sendResponse(401, "Credenciales inválidas", null, false);
            }
            
            // Generar JWT
            $jwtPayload = AuthService::generateJwtPayload($user);
            $token = $this->jwt->generateToken($jwtPayload);
            
            // Establecer cookie
            $this->jwt->setTokenCookie($token);
            
            // Respuesta exitosa
            $this->sendResponse(200, "Login exitoso", [
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'nick' => $user->nick,
                    'name' => $user->name
                ]
            ], true);
            
        } catch (Exception $e) {
            error_log("Error en login: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor", null, false);
        }
    }
}
?>