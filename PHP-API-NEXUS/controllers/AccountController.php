<?php
/**
 * AccountController - Maneja operaciones de cuenta y perfil
 */
require_once 'BaseController.php';

class AccountController extends BaseController {
    private $userRepository;
    private $favorites;
    private $commentsRepository;
    private $constellations;
    
    public function __construct() {
        parent::__construct();
        require_once 'repositories/UserRepository.php';
        require_once 'repositories/FavoritesRepository.php';
        require_once 'repositories/CommentsRepository.php';
        require_once 'models/Constellation.php';
        
        // Usar las bases de datos correspondientes
        $this->userRepository = new UserRepository($this->dbManager->getConnection('NexusUsers'));
        $this->favorites = new FavoritesRepository($this->dbManager->getConnection('NexusUsers'));
        $this->commentsRepository = new CommentsRepository($this->dbManager->getConnection('NexusUsers'));
        $this->constellations = new Constellation($this->dbManager->getConnection('nexus_stars'));
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
                    // Obtener información completa de la constelación desde nexus_stars
                    $constellation = $this->constellations->getById($fav['ConstellationId']);
                    if ($constellation) {
                        $userFavorites[] = [
                            'id' => intval($fav['ConstellationId']),
                            'latin_name' => $constellation['latin_name'] ?? '',
                            'english_name' => $constellation['english_name'] ?? '',
                            'mythology' => $constellation['mythology'] ?? '',
                            'image_url' => $constellation['image_url'] ?? ''
                        ];
                    }
                }
            } catch (Exception $e) {
                error_log("Error obteniendo favoritos: " . $e->getMessage());
            }
            
            // Obtener comentarios del usuario
            $userComments = [];
            try {
                $commentsData = $this->commentsRepository->findByUserId($userId);
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
     * Actualiza el perfil del usuario - Lógica idéntica a ASP.NET
     */
    public function updateProfile($params = []) {
        try {
            // Requiere autenticación
            $tokenData = $this->requireAuth();
            
            // DEBUG TEMPORAL: Ver qué está llegando de todas las formas posibles
            error_log("=== UPDATE PROFILE DEBUG ===");
            error_log("Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'not_set'));
            error_log("Method: " . $_SERVER['REQUEST_METHOD']);
            error_log("POST data: " . json_encode($_POST));
            error_log("GET data: " . json_encode($_GET));
            
            // Leer input raw
            $rawInput = file_get_contents('php://input');
            error_log("Raw input: '$rawInput'");
            
            // Usar el método universal del BaseController
            $data = $this->getRequestData();
            
            error_log("Final data from getRequestData(): " . json_encode($data));
            error_log("=== END DEBUG ===");
            
            if (empty($data)) {
                $this->sendResponse(400, "Datos de actualización requeridos", [
                    'debug_info' => [
                        'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'not_set',
                        'method' => $_SERVER['REQUEST_METHOD'],
                        'post_count' => count($_POST),
                        'post_data' => $_POST,
                        'raw_input_length' => strlen($rawInput),
                        'raw_input_preview' => substr($rawInput, 0, 200),
                        'data_received' => $data
                    ]
                ], false);
                return;
            }
            
            // Validar solo campos básicos obligatorios (más flexible)
            if (!isset($data['Name']) || empty(trim($data['Name']))) {
                $this->sendResponse(400, "El nombre es obligatorio", null, false);
                return;
            }
            
            if (!isset($data['Nick']) || empty(trim($data['Nick']))) {
                $this->sendResponse(400, "El nick es obligatorio", null, false);
                return;
            }
            
            if (!isset($data['Email']) || empty(trim($data['Email']))) {
                $this->sendResponse(400, "El email es obligatorio", null, false);
                return;
            }
            
            // Buscar usuario
            $userId = $tokenData['user_id'];
            $user = $this->userRepository->findById($userId);
            if (!$user) {
                $this->sendResponse(404, "Usuario no encontrado", null, false);
                return;
            }
            
            // Sanitizar datos de entrada
            $newEmail = $this->sanitizeString($data['Email']);
            $newNick = $this->sanitizeString($data['Nick']);
            
            // Validar formato de email
            if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
                $this->sendResponse(400, "El formato del email no es válido", null, false);
                return;
            }
            
            // Validar longitud de nick (igual que ASP.NET)
            if (strlen($newNick) < 3 || strlen($newNick) > 50) {
                $this->sendResponse(400, "El nick debe tener entre 3 y 50 caracteres", null, false);
                return;
            }
            
            // Verificar unicidad de email (si cambió)
            if ($newEmail !== $user->email) {
                if ($this->userRepository->emailExistsForOtherUser($newEmail, $userId)) {
                    $this->sendResponse(400, "El email ya está en uso por otro usuario", null, false);
                    return;
                }
            }
            
            // Verificar unicidad de nick (si cambió)
            if ($newNick !== $user->nick) {
                if ($this->userRepository->nickExistsForOtherUser($newNick, $userId)) {
                    $this->sendResponse(400, "El nick ya está en uso por otro usuario", null, false);
                    return;
                }
            }
            
            // Manejar cambio de contraseña (si se proporciona)
            $passwordChanged = false;
            if (isset($data['NewPassword']) && !empty($data['NewPassword'])) {
                // Validar contraseña actual
                if (!isset($data['CurrentPassword']) || empty($data['CurrentPassword'])) {
                    $this->sendResponse(400, "La contraseña actual es requerida para cambiar la contraseña", null, false);
                    return;
                }
                
                // Verificar contraseña actual
                if (!password_verify($data['CurrentPassword'], $user->passwordHash)) {
                    $this->sendResponse(400, "La contraseña actual es incorrecta", null, false);
                    return;
                }
                
                // Validar nueva contraseña (igual que ASP.NET)
                if (strlen($data['NewPassword']) < 6) {
                    $this->sendResponse(400, "La nueva contraseña debe tener al menos 6 caracteres", null, false);
                    return;
                }
                
                // Confirmar nueva contraseña
                if (!isset($data['ConfirmPassword']) || $data['NewPassword'] !== $data['ConfirmPassword']) {
                    $this->sendResponse(400, "La confirmación de contraseña no coincide", null, false);
                    return;
                }
                
                // Hash de la nueva contraseña
                $newPasswordHash = password_hash($data['NewPassword'], PASSWORD_DEFAULT);
                
                // Actualizar contraseña
                if (!$this->userRepository->updatePassword($userId, $newPasswordHash)) {
                    $this->sendResponse(500, "Error al actualizar la contraseña", null, false);
                    return;
                }
                
                $passwordChanged = true;
            }
            
            // Actualizar campos del usuario
            $user->name = $this->sanitizeString($data['Name']);
            $user->nick = $newNick;
            $user->surname1 = isset($data['Surname1']) ? $this->sanitizeString($data['Surname1']) : '';
            $user->surname2 = isset($data['Surname2']) ? $this->sanitizeString($data['Surname2']) : '';
            $user->phoneNumber = isset($data['PhoneNumber']) ? $this->sanitizeString($data['PhoneNumber']) : '';
            $user->userLocation = isset($data['UserLocation']) ? $this->sanitizeString($data['UserLocation']) : '';
            $user->about = isset($data['About']) ? $this->sanitizeString($data['About']) : '';
            
            // Manejar imagen de perfil - usar imagen por defecto si no existe o está vacía
            if (isset($data['ProfileImage']) && !empty(trim($data['ProfileImage']))) {
                $user->profileImage = $this->sanitizeString($data['ProfileImage']);
            } elseif (empty($user->profileImage)) {
                // Si no tiene imagen actualmente, asignar la por defecto
                $user->profileImage = 'https://88.24.26.59/imgs/default-profile.jpg';
            }
            // Si ya tiene una imagen y no se envía nueva, mantener la actual
            
            // Manejar PublicProfile de forma robusta (string "1"/"0", boolean true/false, checkbox presente/ausente)
            if (isset($data['PublicProfile'])) {
                if (is_bool($data['PublicProfile'])) {
                    $user->publicProfile = $data['PublicProfile'];
                } else {
                    // Convertir string a boolean: "1", "true", "on" = true; "0", "false", "" = false
                    $value = strtolower(trim($data['PublicProfile']));
                    $user->publicProfile = in_array($value, ['1', 'true', 'on', 'yes'], true);
                }
            } else {
                // Si no se envía PublicProfile (checkbox no marcado), asumir false
                $user->publicProfile = false;
            }
            
            // Debug temporal para PublicProfile
            error_log("UPDATE PROFILE: PublicProfile - Original: " . json_encode($data['PublicProfile'] ?? 'not_set') . 
                     ", Final: " . ($user->publicProfile ? 'true' : 'false'));
            
            // Manejar fecha de nacimiento
            if (isset($data['Bday']) && !empty($data['Bday'])) {
                error_log("UPDATE PROFILE: Processing Bday = '" . $data['Bday'] . "'");
                
                // Intentar diferentes formatos de fecha
                $dateFormats = [
                    'Y-m-d',           // 2024-12-31
                    'd/m/Y',           // 31/12/2024
                    'm/d/Y',           // 12/31/2024
                    'Y-m-d H:i:s',     // 2024-12-31 00:00:00
                    'd-m-Y',           // 31-12-2024
                    'm-d-Y'            // 12-31-2024
                ];
                
                $validDate = null;
                $inputDate = trim($data['Bday']);
                
                // Intentar parsear con cada formato
                foreach ($dateFormats as $format) {
                    $dateTime = DateTime::createFromFormat($format, $inputDate);
                    if ($dateTime !== false) {
                        // Verificar que la fecha parseada coincida con el input
                        $formattedBack = $dateTime->format($format);
                        if ($formattedBack === $inputDate) {
                            $validDate = $dateTime->format('Y-m-d'); // Formato MySQL
                            error_log("UPDATE PROFILE: Date matched format '$format', converted to '$validDate'");
                            break;
                        }
                    }
                }
                
                // Si no se pudo parsear con formatos específicos, intentar con strtotime
                if (!$validDate) {
                    $timestamp = strtotime($inputDate);
                    if ($timestamp !== false) {
                        $validDate = date('Y-m-d', $timestamp);
                        error_log("UPDATE PROFILE: Date parsed with strtotime, converted to '$validDate'");
                    }
                }
                
                if ($validDate) {
                    // Validar que la fecha sea razonable (no en el futuro, no demasiado antigua)
                    $dateTime = new DateTime($validDate);
                    $now = new DateTime();
                    $minDate = new DateTime('1900-01-01');
                    
                    if ($dateTime <= $now && $dateTime >= $minDate) {
                        $user->bday = $validDate;
                        error_log("UPDATE PROFILE: Date successfully set to '$validDate'");
                    } else {
                        error_log("UPDATE PROFILE: Date '$validDate' is out of valid range");
                        $this->sendResponse(400, "La fecha de nacimiento no es válida (debe estar entre 1900 y hoy)", null, false);
                        return;
                    }
                } else {
                    error_log("UPDATE PROFILE: Could not parse date format: " . $inputDate);
                    $this->sendResponse(400, "Formato de fecha de nacimiento inválido. Use YYYY-MM-DD", null, false);
                    return;
                }
            } else {
                // Si no se proporciona fecha, mantener la existente
                error_log("UPDATE PROFILE: No Bday provided, keeping existing value");
            }
            
            // Si el email cambió, actualizar y marcar como no confirmado
            $emailChanged = false;
            if ($newEmail !== $user->email) {
                if ($this->userRepository->updateEmail($userId, $newEmail)) {
                    $user->email = $newEmail;
                    $user->emailConfirmed = false;
                    $emailChanged = true;
                } else {
                    $this->sendResponse(500, "Error al actualizar el email", null, false);
                    return;
                }
            }
            
            // Actualizar perfil en base de datos
            error_log("UPDATE PROFILE: About to update user with bday = " . ($user->bday ?? 'null'));
            error_log("UPDATE PROFILE: User data before update: " . json_encode([
                'id' => $user->id,
                'name' => $user->name,
                'nick' => $user->nick,
                'email' => $user->email,
                'bday' => $user->bday,
                'publicProfile' => $user->publicProfile
            ]));
            
            $updateResult = $this->userRepository->update($user);
            error_log("UPDATE PROFILE: UserRepository->update() returned: " . ($updateResult ? 'TRUE' : 'FALSE'));
            
            if ($updateResult) {
                $responseMessage = "Perfil actualizado exitosamente";
                
                if ($passwordChanged && $emailChanged) {
                    $responseMessage .= ". Contraseña cambiada y email actualizado (requiere confirmación)";
                } elseif ($passwordChanged) {
                    $responseMessage .= ". Contraseña cambiada exitosamente";
                } elseif ($emailChanged) {
                    $responseMessage .= ". Email actualizado (requiere confirmación)";
                }
                
                error_log("UPDATE PROFILE: Success - " . $responseMessage);
                error_log("UPDATE PROFILE: Sending success response with status 200");
                $this->sendResponse(200, $responseMessage, null, true);
            } else {
                error_log("UPDATE PROFILE: Database update failed");
                error_log("UPDATE PROFILE: Sending error response with status 500");
                $this->sendResponse(500, "Error al actualizar perfil en la base de datos", null, false);
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
            $user = $this->userRepository->findById($userId);
            if (!$user) {
                $this->sendResponse(404, "Usuario no encontrado", null, false);
                return;
            }
            
            // Eliminar cuenta
            if ($user->delete()) {
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
    
    /**
     * GET /api/Account/GetComments/{id}
     * Obtiene comentarios por ID de constelación (PÚBLICO - equivalente a ASP.NET)
     * Este método está en Account porque los comentarios pertenecen a usuarios
     */
    public function getComments($params = []) {
        try {
            // NO requiere autenticación - es público (igual que en ASP.NET)
            
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
            
            // Buscar comentarios en la base de datos de usuarios por id de constelación
            // Equivalente a: context.Comments.Where(c => c.ConstellationId == id).ToListAsync()
            $comments = $this->commentsRepository->findByConstellationId($constellationId);
            
            // Formatear respuesta igual que ASP.NET
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
            
            // Respuesta Ok() equivalente a ASP.NET
            $this->sendResponse(200, "Comentarios obtenidos exitosamente", $formattedComments, true);
            
        } catch (Exception $e) {
            error_log("Error en getComments: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor", null, false);
        }
    }
    
    /**
     * POST /api/Account/DirectTest
     * Test directo para debug - Lee $_POST directamente
     */
    public function directTest($params = []) {
        try {
            $this->sendResponse(200, "Test directo funcionando", [
                'post_data' => $_POST,
                'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'not_set',
                'method' => $_SERVER['REQUEST_METHOD'],
                'post_count' => count($_POST),
                'has_name' => isset($_POST['Name']),
                'has_email' => isset($_POST['Email'])
            ], true);
            
        } catch (Exception $e) {
            $this->sendResponse(500, "Error en directTest: " . $e->getMessage(), null, false);
        }
    }
    
    /**
     * POST /api/Account/TestData
     * Método de test completo para debug de datos recibidos
     */
    public function testDataReceive($params = []) {
        try {
            // Leer input raw
            $rawInput = file_get_contents('php://input');
            
            // Usar getRequestData()
            $requestData = $this->getRequestData();
            
            $this->sendResponse(200, "Test de recepción de datos", [
                'method' => $_SERVER['REQUEST_METHOD'],
                'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'not_set',
                'headers' => array_filter($_SERVER, function($key) {
                    return strpos($key, 'HTTP_') === 0;
                }, ARRAY_FILTER_USE_KEY),
                'post_data' => $_POST,
                'get_data' => $_GET,
                'raw_input' => [
                    'length' => strlen($rawInput),
                    'content' => substr($rawInput, 0, 500), // Solo primeros 500 chars
                    'is_json' => json_decode($rawInput) !== null
                ],
                'request_data_result' => $requestData,
                'php_input_empty' => empty(trim($rawInput)),
                'post_empty' => empty($_POST)
            ], true);
            
        } catch (Exception $e) {
            $this->sendResponse(500, "Error en testDataReceive: " . $e->getMessage(), null, false);
        }
    }
}
?>