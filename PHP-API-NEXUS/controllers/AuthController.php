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
        require_once 'models/UserRepository.php';
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
            
            // Respuesta exitosa
            $this->sendResponse(200, "Login exitoso", [
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
     * Autentica un usuario usando Google y establece cookie JWT
     */
    public function googleLogin($params = []) {
        try {
            // Obtener datos de entrada (JSON o multipart/form-data)
            $input = $this->getRequestData();
            
            if (!$input) {
                $this->sendResponse(400, "Datos de entrada requeridos", null, false);
            }
            
            // Crear y llenar el modelo ExternalLogin
            $externalLoginModel = new ExternalLogin($input);
            
            // Validar el modelo
            if (!$externalLoginModel->isValid()) {
                $errors = $externalLoginModel->getValidationErrors();
                $this->sendResponse(400, "Datos de login inválidos", [
                    'errors' => $errors
                ], false);
            }
            
            // Verificar token de Google
            $googleData = $this->googleAuthService->verifyIdToken($externalLoginModel->id_token);
            
            if (!$googleData) {
                $this->sendResponse(401, "Token de Google inválido", null, false);
            }
            
            // Buscar o crear usuario
            $user = $this->userRepository->findByEmail($googleData->email);
            
            if (!$user) {
                // Crear nuevo usuario si no existe
                $user = new stdClass();
                $user->id = $this->userRepository->generateId();
                $user->email = $googleData->email;
                $user->nick = $googleData->name; // Usar nombre como nick por defecto
                $user->name = $googleData->name;
                $user->surname1 = ''; // Sin apellido por defecto
                $user->passwordHash = ''; // Sin contraseña
                $user->emailConfirmed = 1; // Confirmar email automáticamente
                
                // Crear usuario en la base de datos
                $this->userRepository->create($user);
            } else {
                // Actualizar datos del usuario si es necesario
                $user->name = $googleData->name;
                $user->surname1 = '';
                $user->emailConfirmed = 1; // Asegurarse de que el email esté confirmado
                $this->userRepository->update($user);
            }
            
            // Generar JWT
            $expiration = 86400; // 1 día
            $jwtPayload = AuthService::generateJwtPayload($user, $expiration);
            $token = $this->jwt->generateTokenFromPayload($jwtPayload);
            
            // Establecer cookie con la misma expiración
            $this->jwt->setCookie($token, 'auth_token', $expiration);
            
            // Respuesta exitosa
            $this->sendResponse(200, "Login con Google exitoso", [
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'nick' => $user->nick,
                    'name' => $user->name
                ]
            ], true);
            
        } catch (Exception $e) {
            error_log("Error en googleLogin: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor", null, false);
        }
    }

    /**
     * POST /api/Auth/ExternalLogin
     * Autentica un usuario con login externo (Google)
     * Compatible con ASP.NET Identity ExternalLogin
     */
    public function externalLogin($params = []) {
        try {
            // Obtener datos de entrada (JSON o multipart/form-data)
            $input = $this->getRequestData();
            
            if (!$input) {
                $this->sendResponse(400, "Datos de entrada requeridos", null, false);
            }
            
            // Crear y validar el modelo ExternalLogin
            $externalLoginModel = new ExternalLogin($input);
            
            if (!$externalLoginModel->isValid()) {
                $errors = $externalLoginModel->getValidationErrors();
                $this->sendResponse(400, "Datos de login externo inválidos", [
                    'errors' => $errors
                ], false);
            }
            
            // Validar token de Google
            $googleUserInfo = null;
            if ($externalLoginModel->provider === 'Google') {
                $googleUserInfo = $this->googleAuthService->validateToken($externalLoginModel->providerKey);
                
                if (!$googleUserInfo) {
                    $this->sendResponse(401, "Token de Google inválido o expirado", null, false);
                }
                
                // Verificar que el email coincida
                if ($googleUserInfo['email'] !== $externalLoginModel->email) {
                    $this->sendResponse(400, "El email del token no coincide con el proporcionado", null, false);
                }
            } else {
                $this->sendResponse(400, "Proveedor de autenticación no soportado", null, false);
            }
            
            // Buscar usuario existente por email
            $user = $this->userRepository->findByEmail($externalLoginModel->email);
            
            if ($user) {
                // Usuario existente - vincular cuenta externa si no está vinculada
                $loginExistente = $this->userRepository->findExternalLogin($user->id, $externalLoginModel->provider);
                
                if (!$loginExistente) {
                    // Vincular cuenta externa
                    $externalLoginRecord = [
                        'user_id' => $user->id,
                        'login_provider' => $externalLoginModel->provider,
                        'provider_key' => $externalLoginModel->providerKey,
                        'provider_display_name' => $googleUserInfo['name'] ?? $externalLoginModel->provider
                    ];
                    
                    if (!$this->userRepository->addExternalLogin($externalLoginRecord)) {
                        error_log("Error vinculando cuenta externa para usuario: " . $user->id);
                    }
                }
                
                // Verificar que el usuario puede hacer login
                $loginCheck = AuthService::canLogin($user);
                if (!$loginCheck['can_login']) {
                    $this->sendResponse(401, $loginCheck['reason'], null, false);
                }
                
                // Si el email no está confirmado y viene de Google, confirmarlo automáticamente
                if (!$user->emailConfirmed && $googleUserInfo['email_verified']) {
                    $user->emailConfirmed = true;
                    $this->userRepository->update($user);
                }
                
            } else {
                // Usuario nuevo - crear cuenta automáticamente
                $newUser = new User();
                $newUser->generateId();
                $newUser->email = $externalLoginModel->email;
                $newUser->nick = $this->generateNickFromEmail($externalLoginModel->email);
                $newUser->name = $googleUserInfo['given_name'] ?? '';
                $newUser->surname1 = $googleUserInfo['family_name'] ?? '';
                $newUser->emailConfirmed = $googleUserInfo['email_verified'] ?? false;
                $newUser->lockout_enabled = false;
                $newUser->access_failed_count = 0;
                $newUser->two_factor_enabled = false;
                $newUser->phone_number_confirmed = false;
                $newUser->created_at = date('Y-m-d H:i:s');
                $newUser->security_stamp = bin2hex(random_bytes(16));
                
                // No establecer contraseña para cuentas externas
                $newUser->passwordHash = '';
                
                // Crear usuario
                if ($this->userRepository->create($newUser)) {
                    // Agregar login externo
                    $externalLoginRecord = [
                        'user_id' => $newUser->id,
                        'login_provider' => $externalLoginModel->provider,
                        'provider_key' => $externalLoginModel->providerKey,
                        'provider_display_name' => $googleUserInfo['name'] ?? $externalLoginModel->provider
                    ];
                    
                    if (!$this->userRepository->addExternalLogin($externalLoginRecord)) {
                        error_log("Error agregando login externo para nuevo usuario: " . $newUser->id);
                    }
                    
                    // Enviar email de bienvenida
                    $emailService = new EmailService();
                    $welcomeResult = $emailService->sendWelcomeEmail($newUser->email, $newUser->name);
                    
                    $user = $newUser;
                } else {
                    $this->sendResponse(500, "Error creando usuario con cuenta externa", null, false);
                }
            }
            
            // Generar JWT
            $expiration = 86400; // 1 día por defecto para login externo
            $jwtPayload = AuthService::generateJwtPayload($user, $expiration);
            $token = $this->jwt->generateTokenFromPayload($jwtPayload);
            
            // Establecer cookie
            $this->jwt->setCookie($token, 'auth_token', $expiration);
            
            // Respuesta exitosa
            $this->sendResponse(200, "Login externo exitoso", [
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'nick' => $user->nick,
                    'name' => $user->name,
                    'emailConfirmed' => $user->emailConfirmed
                ],
                'provider' => $externalLoginModel->provider,
                'isNewUser' => !isset($loginExistente)
            ], true);
            
        } catch (Exception $e) {
            error_log("Error en externalLogin: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor", null, false);
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