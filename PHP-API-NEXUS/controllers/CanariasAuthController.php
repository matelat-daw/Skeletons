<?php
/**
 * Controlador de Autenticación para Economía Circular Canarias
 * Maneja registro, login, logout y validación de tokens JWT
 */

require_once __DIR__ . '/../models/CanariasUser.php';
require_once __DIR__ . '/../config/jwt.php';
require_once __DIR__ . '/../config/CorsHandler.php';

class CanariasAuthController {
    private $userModel;
    private $jwtHandler;

    public function __construct() {
        $this->userModel = new CanariasUser();
        $this->jwtHandler = new JWTHandler();
        
        // Configurar CORS para todas las respuestas
        CorsHandler::handleCors();
    }

    /**
     * Registro de nuevo usuario
     * POST /api/auth/register
     */
    public function register() {
        try {
            // Verificar método HTTP
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->sendResponse(405, 'Método no permitido');
                return;
            }

            // Obtener datos JSON del cuerpo de la petición
            $jsonData = file_get_contents('php://input');
            $userData = json_decode($jsonData, true);

            if (!$userData) {
                $this->sendResponse(400, 'Datos JSON inválidos');
                return;
            }

            // Log de intento de registro
            error_log("🔄 Intento de registro: " . $userData['email'] ?? 'email no proporcionado');

            // Validar que se proporcionaron los campos requeridos
            $requiredFields = ['name', 'email', 'password'];
            foreach ($requiredFields as $field) {
                if (empty($userData[$field])) {
                    $this->sendResponse(400, "El campo '{$field}' es requerido");
                    return;
                }
            }

            // Validar confirmación de contraseña
            if (!empty($userData['confirmPassword']) && $userData['password'] !== $userData['confirmPassword']) {
                $this->sendResponse(400, 'Las contraseñas no coinciden');
                return;
            }

            // Registrar usuario
            $result = $this->userModel->register($userData);

            if ($result['success']) {
                // Enviar email de confirmación (simular por ahora)
                $this->sendConfirmationEmail($result['email'], $result['confirmation_hash']);

                $this->sendResponse(201, 'Usuario registrado exitosamente', [
                    'message' => $result['message'],
                    'user_id' => $result['user_id'],
                    'next_step' => 'Revisa tu email para confirmar tu cuenta'
                ]);
            } else {
                $statusCode = isset($result['errors']) ? 422 : 400;
                $this->sendResponse($statusCode, $result['message'], [
                    'errors' => $result['errors'] ?? []
                ]);
            }

        } catch (Exception $e) {
            error_log("❌ Error en registro: " . $e->getMessage());
            $this->sendResponse(500, 'Error interno del servidor');
        }
    }

    /**
     * Inicio de sesión de usuario
     * POST /api/auth/login
     */
    public function login() {
        try {
            // Verificar método HTTP
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->sendResponse(405, 'Método no permitido');
                return;
            }

            // Obtener datos JSON del cuerpo de la petición
            $jsonData = file_get_contents('php://input');
            $loginData = json_decode($jsonData, true);

            if (!$loginData) {
                $this->sendResponse(400, 'Datos JSON inválidos');
                return;
            }

            // Log de intento de login
            error_log("🔄 Intento de login: " . $loginData['email'] ?? 'email no proporcionado');

            // Validar campos requeridos
            if (empty($loginData['email']) || empty($loginData['password'])) {
                $this->sendResponse(400, 'Email y contraseña son requeridos');
                return;
            }

            // Intentar login
            $result = $this->userModel->login($loginData['email'], $loginData['password']);

            if ($result['success']) {
                // Generar token JWT
                $token = $this->jwtHandler->generateToken(
                    $result['user']['id'],
                    $result['user']['email'],
                    $result['user']['name']
                );

                // Configurar cookie con el token JWT
                $this->setJWTCookie($token, isset($loginData['rememberMe']) && $loginData['rememberMe']);

                // Respuesta exitosa
                $this->sendResponse(200, 'Login exitoso', [
                    'message' => $result['message'],
                    'user' => $result['user'],
                    'token' => $token,
                    'expires_in' => 86400 // 24 horas en segundos
                ]);

            } else {
                $this->sendResponse(401, $result['message']);
            }

        } catch (Exception $e) {
            error_log("❌ Error en login: " . $e->getMessage());
            $this->sendResponse(500, 'Error interno del servidor');
        }
    }

    /**
     * Cerrar sesión
     * POST /api/auth/logout
     */
    public function logout() {
        try {
            // Limpiar cookie JWT
            $this->clearJWTCookie();

            $this->sendResponse(200, 'Logout exitoso', [
                'message' => 'Sesión cerrada correctamente'
            ]);

        } catch (Exception $e) {
            error_log("❌ Error en logout: " . $e->getMessage());
            $this->sendResponse(500, 'Error interno del servidor');
        }
    }

    /**
     * Validar token JWT
     * GET /api/auth/validate
     */
    public function validateToken() {
        try {
            // Obtener token desde cookie o header
            $token = $this->getTokenFromRequest();

            if (!$token) {
                $this->sendResponse(401, 'Token no encontrado');
                return;
            }

            // Validar token
            $payload = $this->jwtHandler->validateToken($token);

            if ($payload) {
                // Obtener datos actualizados del usuario
                $user = $this->userModel->getUserById($payload['user_id']);

                if ($user && $user['active'] == 1) {
                    $this->sendResponse(200, 'Token válido', [
                        'valid' => true,
                        'user' => [
                            'id' => $user['id'],
                            'name' => $user['name'],
                            'surname' => $user['surname'],
                            'email' => $user['email'],
                            'phone' => $user['phone'],
                            'gender' => $user['gender'],
                            'path' => $user['path']
                        ],
                        'expires_at' => $payload['exp']
                    ]);
                } else {
                    $this->sendResponse(401, 'Usuario no encontrado o inactivo');
                }
            } else {
                $this->sendResponse(401, 'Token inválido o expirado');
            }

        } catch (Exception $e) {
            error_log("❌ Error validando token: " . $e->getMessage());
            $this->sendResponse(401, 'Token inválido');
        }
    }

    /**
     * Confirmar email del usuario
     * GET /api/auth/confirm?email=...&hash=...
     */
    public function confirmEmail() {
        try {
            $email = $_GET['email'] ?? '';
            $hash = $_GET['hash'] ?? '';

            if (empty($email) || empty($hash)) {
                $this->sendResponse(400, 'Email y hash son requeridos');
                return;
            }

            $result = $this->userModel->confirmEmail($email, $hash);

            if ($result['success']) {
                $this->sendResponse(200, 'Email confirmado exitosamente', [
                    'message' => $result['message'],
                    'next_step' => 'Ya puedes iniciar sesión en tu cuenta'
                ]);
            } else {
                $this->sendResponse(400, $result['message']);
            }

        } catch (Exception $e) {
            error_log("❌ Error confirmando email: " . $e->getMessage());
            $this->sendResponse(500, 'Error interno del servidor');
        }
    }

    /**
     * Configurar cookie JWT
     * @param string $token
     * @param bool $remember
     */
    private function setJWTCookie($token, $remember = false) {
        $expires = $remember ? time() + (30 * 24 * 60 * 60) : 0; // 30 días si recuerda, sino sesión
        
        setcookie(
            'canarias_auth_token',
            $token,
            [
                'expires' => $expires,
                'path' => '/',
                'domain' => '',
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax'
            ]
        );
    }

    /**
     * Limpiar cookie JWT
     */
    private function clearJWTCookie() {
        setcookie(
            'canarias_auth_token',
            '',
            [
                'expires' => time() - 3600,
                'path' => '/',
                'domain' => '',
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax'
            ]
        );
    }

    /**
     * Obtener token desde petición (cookie o header)
     * @return string|null
     */
    private function getTokenFromRequest() {
        // Primero intentar desde cookie
        if (isset($_COOKIE['canarias_auth_token'])) {
            return $_COOKIE['canarias_auth_token'];
        }

        // Después desde header Authorization
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
            if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Simular envío de email de confirmación
     * @param string $email
     * @param string $hash
     */
    private function sendConfirmationEmail($email, $hash) {
        // En producción, aquí integrarías con un servicio de email real
        $confirmationUrl = "http://localhost/api/auth/confirm?email=" . urlencode($email) . "&hash=" . $hash;
        
        error_log("📧 Email de confirmación para {$email}: {$confirmationUrl}");
        
        // Para desarrollo, podrías escribir el link a un archivo
        file_put_contents(
            __DIR__ . '/../logs/confirmation_emails.log',
            "[" . date('Y-m-d H:i:s') . "] Email: {$email}, URL: {$confirmationUrl}\n",
            FILE_APPEND
        );
    }

    /**
     * Enviar respuesta JSON
     * @param int $statusCode
     * @param string $message
     * @param array $data
     */
    private function sendResponse($statusCode, $message, $data = []) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        
        $response = array_merge([
            'success' => $statusCode >= 200 && $statusCode < 300,
            'message' => $message,
            'timestamp' => date('c')
        ], $data);

        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

?>
