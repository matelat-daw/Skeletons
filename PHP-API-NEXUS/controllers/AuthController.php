<?php
/**
 * AuthController - Maneja la autenticación de usuarios
 */
require_once 'BaseController.php';

class AuthController extends BaseController {
    private $user;
    
    public function __construct() {
        parent::__construct();
        require_once 'models/User.php';
        $this->user = new User($this->dbManager->getNexusUsersConnection());
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
            
            // Validar formato de email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->sendResponse(400, "Formato de email inválido", null, false);
            }
            
            // Buscar usuario por email
            if (!$this->user->findByEmail($email)) {
                $this->sendResponse(401, "Credenciales inválidas", null, false);
            }
            
            // Verificar que el email esté confirmado
            if (!$this->user->email_confirmed) {
                $this->sendResponse(401, "Email no confirmado. Verifica tu correo electrónico.", null, false);
            }
            
            // Verificar contraseña
            if (!$this->user->verifyPassword($password)) {
                $this->sendResponse(401, "Credenciales inválidas", null, false);
            }
            
            // Generar JWT
            $userData = [
                'user_id' => $this->user->id,
                'email' => $this->user->email,
                'nick' => $this->user->nick,
                'username' => $this->user->nick
            ];
            
            $token = $this->jwt->generateToken($userData);
            
            // Establecer cookie
            $this->jwt->setTokenCookie($token);
            
            // Respuesta exitosa
            $this->sendResponse(200, "Login exitoso", [
                'user' => [
                    'id' => $this->user->id,
                    'email' => $this->user->email,
                    'nick' => $this->user->nick,
                    'name' => $this->user->name
                ]
            ], true);
            
        } catch (Exception $e) {
            error_log("Error en login: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor", null, false);
        }
    }
}
?>
