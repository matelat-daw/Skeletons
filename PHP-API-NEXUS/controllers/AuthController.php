<?php
/**
 * AuthController - Maneja la autenticación de usuarios
 */
require_once 'BaseController.php';

class AuthController extends BaseController {
    private $userRepository;
    private $authService;
    private $googleAuthService;
    
    public function __construct() {
        parent::__construct();
        require_once 'repositories/UserRepository.php';
        require_once 'models/Login.php';
        require_once 'models/Register.php';
        require_once 'models/ExternalLogin.php'; // Nuevo modelo para login externo
        require_once 'models/EmailConfirmation.php';
        require_once 'services/AuthService.php';
        require_once 'services/GoogleAuthService.php'; // Nuevo servicio de Google
        require_once 'services/EmailService.php';
        $this->userRepository = new UserRepository($this->dbManager->getConnection('NexusUsers'));
        $this->authService = new AuthService();
        $this->googleAuthService = new GoogleAuthService(); // Inicializar servicio de Google
    }
    
    /**
     * POST /api/Auth/Login
     * Autentica un usuario y establece cookie JWT
     */
    public function login($params = []) {
        try {
            // Obtener datos de entrada (JSON o multipart/form-data)
            $input = $this->getRequestData();
            
            if (!$input) {
                $this->sendResponse(400, "Datos de entrada requeridos", null, false);
                return;
            }
            
            // Crear y llenar el modelo Login
            $loginModel = new Login($input);
            
            // Sanitizar email
            $loginModel->sanitizeEmail();
            
            // Validar el modelo
            if (!$loginModel->isValid()) {
                $errors = $loginModel->getValidationErrors();
                $this->sendResponse(400, "Datos de login inválidos", [
                    'errors' => $errors
                ], false);
                return;
            }
            
            // Validar credenciales adicionales
            if (!AuthService::validateCredentials($loginModel->email, $loginModel->password)) {
                $this->sendResponse(400, "Formato de credenciales inválido", null, false);
                return;
            }
            
            // Buscar usuario por email
            $user = $this->userRepository->findByEmail($loginModel->email);
            if (!$user) {
                $this->sendResponse(401, "Credenciales inválidas", null, false);
                return;
            }
            
            // Verificar que el usuario puede hacer login
            $loginCheck = AuthService::canLogin($user);
            if (!$loginCheck['can_login']) {
                $this->sendResponse(401, $loginCheck['reason'], null, false);
                return;
            }
            
            // Verificar contraseña
            if (!AuthService::verifyPassword($loginModel->password, $user->passwordHash)) {
                $this->sendResponse(401, "Credenciales inválidas", null, false);
                return;
            }
            
            // Generar JWT con expiración apropiada
            $expiration = $loginModel->rememberMe ? (86400 * 30) : 86400; // 30 días vs 1 día
            $jwtPayload = AuthService::generateJwtPayload($user, $expiration);
            $token = $this->jwt->generateTokenFromPayload($jwtPayload);
            
            // Establecer cookie con la misma expiración
            $this->jwt->setCookie($token, 'auth_token', $expiration);
            
            // Respuesta exitosa (incluir token tanto en cookie como en JSON)
            $this->sendResponse(200, "Login exitoso", [
                'token' => $token,  // ← Agregar token al JSON
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'nick' => $user->nick,
                    'name' => $user->name
                ],
                'rememberMe' => $loginModel->rememberMe
            ], true);
            
        } catch (Exception $e) {
            error_log("Error en login: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor", null, false);
        }
    }

    /**
     * POST /api/Auth/Register
     * Registra un nuevo usuario
     */
    public function register($params = []) {
        try {
            // Obtener datos de entrada (JSON o multipart/form-data)
            $input = $this->getRequestData();
            
            if (!$input) {
                $this->sendResponse(400, "Datos de entrada requeridos", null, false);
            }
            
            // Crear y llenar el modelo Register
            $registerModel = new Register($input);
            
            // Sanitizar campos
            $registerModel->sanitizeFields();
            
            // Validar el modelo
            if (!$registerModel->isValid()) {
                $errors = $registerModel->getValidationErrors();
                $this->sendResponse(400, "Datos de registro inválidos", [
                    'errors' => $errors
                ], false);
            }
            
            // Verificar que el email no exista
            if ($this->userRepository->emailExists($registerModel->email)) {
                $this->sendResponse(409, "El email ya está registrado", null, false);
            }
            
            // Verificar que el nick no exista
            if ($this->userRepository->nickExists($registerModel->nick)) {
                $this->sendResponse(409, "El username ya está en uso", null, false);
            }
            
            // Generar ID si no existe
            $registerModel->generateId();
            
            // Generar hash de contraseña si se proporcionó
            if (!empty($registerModel->password)) {
                $hashedPassword = AuthService::hashPassword($registerModel->password);
            } else {
                // Si no se proporciona contraseña, se puede generar una temporal
                // o dejar vacía para que el usuario la establezca posteriormente
                $hashedPassword = '';
            }
            
            // Convertir a modelo User para inserción
            $user = $registerModel->toUser();
            $user->passwordHash = $hashedPassword;
            
            // Crear usuario en la base de datos
            if ($this->userRepository->create($user)) {
                
                // Generar y enviar email de confirmación
                $emailConfirmation = new EmailConfirmation($this->dbManager->getConnection('NexusUsers'));
                $confirmationToken = $emailConfirmation->generateToken($user->id, $user->email);
                
                if ($emailConfirmation->saveToken()) {
                    // Enviar email de confirmación
                    $emailService = new EmailService();
                    $emailResult = $emailService->sendEmailConfirmation(
                        $user->email, 
                        $user->name, 
                        $confirmationToken
                    );
                    
                    // Respuesta exitosa (independientemente del resultado del email)
                    $responseData = [
                        'user' => [
                            'id' => $user->id,
                            'nick' => $user->nick,
                            'email' => $user->email,
                            'name' => $user->name,
                            'surname1' => $user->surname1,
                            'profileImage' => $user->profileImage,
                            'emailConfirmed' => false
                        ],
                        'emailSent' => $emailResult['success']
                    ];
                    
                    if ($emailResult['success']) {
                        $message = "Usuario registrado exitosamente. Se ha enviado un email de confirmación a " . $user->email;
                    } else {
                        $message = "Usuario registrado exitosamente. Error enviando email de confirmación, contacta al administrador.";
                        error_log("Error enviando email a " . $user->email . ": " . $emailResult['message']);
                    }
                    
                    $this->sendResponse(201, $message, $responseData, true);
                } else {
                    // Error guardando token de confirmación
                    error_log("Error guardando token de confirmación para usuario: " . $user->id);
                    $this->sendResponse(201, "Usuario registrado pero error configurando confirmación por email", [
                        'user' => [
                            'id' => $user->id,
                            'nick' => $user->nick,
                            'email' => $user->email,
                            'name' => $user->name,
                            'surname1' => $user->surname1,
                            'emailConfirmed' => false
                        ],
                        'emailSent' => false
                    ], true);
                }
            } else {
                $this->sendResponse(500, "Error al crear el usuario", null, false);
            }
            
        } catch (Exception $e) {
            error_log("Error en register: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor", null, false);
        }
    }

    /**
     * GET /api/Auth/ConfirmEmail?token=xxxxx
     * Confirma el email del usuario usando el token
     */
    public function confirmEmail($params = []) {
        try {
            // Obtener token de query parameters
            $token = $_GET['token'] ?? null;
            
            if (empty($token)) {
                $this->sendResponse(400, "Token de confirmación requerido", null, false);
            }
            
            // Buscar y validar token
            $emailConfirmation = new EmailConfirmation($this->dbManager->getConnection('NexusUsers'));
            
            if (!$emailConfirmation->findValidToken($token)) {
                $this->sendResponse(400, "Token de confirmación inválido o expirado", null, false);
            }
            
            // Buscar usuario
            $user = $this->userRepository->findById($emailConfirmation->user_id);
            
            if (!$user) {
                $this->sendResponse(404, "Usuario no encontrado", null, false);
            }
            
            // Verificar que el email coincida
            if ($user->email !== $emailConfirmation->email) {
                $this->sendResponse(400, "Email no coincide con el token", null, false);
            }
            
            // Si ya está confirmado
            if ($user->emailConfirmed) {
                $this->sendResponse(200, "Email ya confirmado previamente", [
                    'user' => [
                        'id' => $user->id,
                        'nick' => $user->nick,
                        'email' => $user->email,
                        'emailConfirmed' => true
                    ]
                ], true);
                return;
            }
            
            // Actualizar usuario como confirmado
            $user->emailConfirmed = true;
            
            if ($this->userRepository->update($user)) {
                // Marcar token como usado
                $emailConfirmation->confirmToken($token);
                
                // Enviar email de bienvenida
                $emailService = new EmailService();
                $welcomeResult = $emailService->sendWelcomeEmail($user->email, $user->name);
                
                // Respuesta exitosa
                $this->sendResponse(200, "Email confirmado exitosamente. ¡Bienvenido a Nexus Astralis!", [
                    'user' => [
                        'id' => $user->id,
                        'nick' => $user->nick,
                        'email' => $user->email,
                        'name' => $user->name,
                        'emailConfirmed' => true
                    ],
                    'welcomeEmailSent' => $welcomeResult['success']
                ], true);
            } else {
                $this->sendResponse(500, "Error actualizando estado de confirmación", null, false);
            }
            
        } catch (Exception $e) {
            error_log("Error en confirmEmail: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor", null, false);
        }
    }

    /**
     * POST /api/Auth/ResendConfirmation
     * Reenvía el email de confirmación
     */
    public function resendConfirmation($params = []) {
        try {
            // Obtener datos de entrada (JSON o multipart/form-data)
            $input = $this->getRequestData();
            
            if (!$input || empty($input['email'])) {
                $this->sendResponse(400, "Email requerido", null, false);
            }
            
            $email = filter_var(trim($input['email']), FILTER_SANITIZE_EMAIL);
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->sendResponse(400, "Formato de email inválido", null, false);
            }
            
            // Buscar usuario
            $user = $this->userRepository->findByEmail($email, true);
            
            if (!$user) {
                // Por seguridad, no revelamos si el usuario existe o no
                $this->sendResponse(200, "Si el email existe en nuestro sistema, se ha enviado un nuevo enlace de confirmación", null, true);
                return;
            }
            
            // Si ya está confirmado
            if ($user->emailConfirmed) {
                $this->sendResponse(400, "El email ya está confirmado", null, false);
            }
            
            // Generar nuevo token
            $emailConfirmation = new EmailConfirmation($this->dbManager->getConnection('NexusUsers'));
            $confirmationToken = $emailConfirmation->generateToken($user->id, $user->email);
            
            if ($emailConfirmation->saveToken()) {
                // Enviar nuevo email de confirmación
                $emailService = new EmailService();
                $emailResult = $emailService->sendEmailConfirmation(
                    $user->email, 
                    $user->name, 
                    $confirmationToken
                );
                
                if ($emailResult['success']) {
                    $this->sendResponse(200, "Nuevo email de confirmación enviado", null, true);
                } else {
                    error_log("Error reenviando email a " . $user->email . ": " . $emailResult['message']);
                    $this->sendResponse(500, "Error enviando email de confirmación", null, false);
                }
            } else {
                $this->sendResponse(500, "Error generando token de confirmación", null, false);
            }
            
        } catch (Exception $e) {
            error_log("Error en resendConfirmation: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor", null, false);
        }
    }

    /**
     * POST /api/Auth/GoogleLogin
     * Autenticación con Google usando token JWT (equivalente al método ASP.NET)
     */
    public function googleLogin($params = []) {
        try {
            // Obtener datos de entrada (JSON o multipart/form-data)
            $input = $this->getRequestData();
            
            if (!$input) {
                $this->sendResponse(400, "Datos de entrada requeridos", null, false);
                return;
            }
            
            // Crear y llenar el modelo ExternalLogin
            $externalLoginModel = new ExternalLogin($input);
            
            // Validar el modelo
            if (!$externalLoginModel->isValid()) {
                $errors = $externalLoginModel->getValidationErrors();
                $this->sendResponse(400, "Token de Google requerido", [
                    'errors' => $errors
                ], false);
                return;
            }
            
            // Validar el token de Google usando GoogleAuthService
            try {
                $payload = $this->googleAuthService->validateGoogleToken($externalLoginModel->token);
            } catch (Exception $e) {
                $this->sendResponse(400, "Token Inválido", [
                    'error' => $e->getMessage()
                ], false);
                return;
            }
            
            // Extraer información del payload (equivalente a ASP.NET payload)
            $email = $payload['email'];
            $name = $payload['name'];
            $picture = $payload['picture'];
            
            if (!$email) {
                $this->sendResponse(400, "Email no disponible en el token de Google", null, false);
                return;
            }
            
            // Verificar o crear usuario (equivalente a VerifyUser en ASP.NET)
            $user = $this->verifyUser($email, $name, $picture);
            
            if (!$user) {
                $this->sendResponse(500, "Error verificando o creando usuario", null, false);
                return;
            }
            
            // Generar token local JWT (equivalente a GenerateToken en ASP.NET)
            $expiration = 86400; // 1 día por defecto para login con Google
            $jwtPayload = AuthService::generateJwtPayload($user, $expiration);
            $localToken = $this->jwt->generateTokenFromPayload($jwtPayload);
            
            // Establecer cookie con el token
            $this->jwt->setCookie($localToken, 'auth_token', $expiration);
            
            // Respuesta exitosa (formato similar a ASP.NET)
            $this->sendResponse(200, "Inicio de Sesión Exitoso", [
                'token' => $localToken,
                'nick' => $user->nick,
                'email' => $user->email,
                'name' => $user->name,
                'profileImage' => $user->profileImage
            ], true);
            
        } catch (Exception $e) {
            error_log("Error en googleLogin: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor", null, false);
        }
    }

    /**
     * Verificar o crear usuario para login externo (equivalente a VerifyUser en ASP.NET)
     * @param string $email Email del usuario
     * @param string $name Nombre del usuario  
     * @param string $picture URL de la imagen de perfil
     * @return User|null Usuario verificado o creado
     */
    private function verifyUser($email, $name, $picture) {
        try {
            // Buscar usuario existente por email
            $user = $this->userRepository->findByEmail($email);
            
            if ($user) {
                // Usuario existe, actualizar información si es necesario
                $updated = false;
                
                // Actualizar nombre si está vacío o es diferente
                if (empty($user->name) && !empty($name)) {
                    $user->name = $name;
                    $updated = true;
                }
                
                // Actualizar imagen de perfil si está vacía o es diferente
                if (empty($user->profileImage) && !empty($picture)) {
                    $user->profileImage = $picture;
                    $updated = true;
                }
                
                // Marcar email como confirmado si no lo está (login con Google implica email verificado)
                if (!$user->emailConfirmed) {
                    $user->emailConfirmed = true;
                    $updated = true;
                }
                
                // Guardar cambios si hay actualizaciones
                if ($updated) {
                    $this->userRepository->update($user);
                }
                
                return $user;
            } else {
                // Usuario no existe, crear nuevo usuario
                require_once 'models/User.php';
                
                $newUser = new User();
                
                // Generar ID único (misma lógica que Register::generateId())
                $newUser->id = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                    mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                    mt_rand(0, 0xffff),
                    mt_rand(0, 0x0fff) | 0x4000,
                    mt_rand(0, 0x3fff) | 0x8000,
                    mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
                );
                
                $newUser->email = $email;
                $newUser->name = $name ?: '';
                $newUser->nick = $this->generateNickFromEmail($email);
                $newUser->profileImage = $picture ?: '';
                $newUser->emailConfirmed = true; // Google implica email verificado
                $newUser->passwordHash = ''; // Sin contraseña para cuentas de Google
                
                // Crear usuario en la base de datos
                if ($this->userRepository->create($newUser)) {
                    return $newUser;
                } else {
                    error_log("Error creando usuario para login con Google: " . $email);
                    return null;
                }
            }
            
        } catch (Exception $e) {
            error_log("Error en verifyUser: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Genera un nick único basado en el email
     * Método auxiliar para cuentas externas
     */
    private function generateNickFromEmail($email) {
        $baseName = explode('@', $email)[0];
        $baseName = preg_replace('/[^a-zA-Z0-9]/', '', $baseName);
        
        // Verificar si el nick está disponible
        $counter = 0;
        $nick = $baseName;
        
        while ($this->userRepository->nickExists($nick)) {
            $counter++;
            $nick = $baseName . $counter;
            
            // Evitar bucle infinito
            if ($counter > 999) {
                $nick = $baseName . '_' . uniqid();
                break;
            }
        }
        
        return $nick;
    }
}
?>