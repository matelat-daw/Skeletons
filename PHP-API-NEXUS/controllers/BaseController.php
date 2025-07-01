<?php
/**
 * BaseController - Controlador base con funcionalidades comunes
 */
abstract class BaseController {
    protected $jwt;
    protected $dbManager;
    
    public function __construct() {
        // Incluir dependencias comunes
        require_once 'config/database_manager.php';
        require_once 'config/jwt.php';
        
        $this->dbManager = new DatabaseManager();
        $this->jwt = new JWTHandler();
    }
    
    /**
     * Envía respuesta JSON estándar
     */
    protected function sendResponse($statusCode, $message = null, $data = null, $success = null) {
        http_response_code($statusCode);
        $response = [];
        
        // Determinar success automáticamente si no se especifica
        if ($success === null) {
            $success = $statusCode >= 200 && $statusCode < 300;
        }
        
        $response['success'] = $success;
        
        if ($message) {
            $response['message'] = $message;
        }
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit();
    }
    
    /**
     * Obtiene y valida el token JWT
     */
    protected function requireAuth() {
        $token = $this->jwt->getTokenFromCookie();
        
        if (!$token) {
            $this->sendResponse(401, "No hay sesión activa", null, false);
        }
        
        $tokenData = $this->jwt->validateToken($token);
        
        if (!$tokenData) {
            $this->sendResponse(401, "Token inválido o expirado", null, false);
        }
        
        return $tokenData;
    }
    
    /**
     * Obtiene datos del cuerpo de la request (JSON)
     */
    protected function getJsonInput() {
        $input = file_get_contents("php://input");
        return json_decode($input, true);
    }
    
    /**
     * Obtiene datos de la request (JSON o multipart/form-data)
     * Método universal que maneja ambos formatos
     */
    protected function getRequestData() {
        static $cachedData = null;
        static $cached = false;
        
        // Usar caché para evitar múltiples lecturas de php://input
        if ($cached) {
            return $cachedData;
        }
        
        $data = [];
        
        // Verificar el tipo de contenido
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? '';
        
        // DEBUG TEMPORAL
        error_log("BaseController DEBUG: Content-Type = '$contentType'");
        error_log("BaseController DEBUG: Method = '$requestMethod'");
        error_log("BaseController DEBUG: POST = " . json_encode($_POST));
        
        // Detectar si es multipart/form-data o POST normal
        $isMultipart = strpos(strtolower($contentType), 'multipart/form-data') !== false;
        $isFormUrlEncoded = strpos(strtolower($contentType), 'application/x-www-form-urlencoded') !== false;
        
        if ($isMultipart || $isFormUrlEncoded || !empty($_POST)) {
            // Datos enviados como multipart/form-data o application/x-www-form-urlencoded
            $data = $_POST;
            error_log("BaseController DEBUG: Using POST data (multipart/form-encoded)");
        } else {
            // Intentar leer JSON desde php://input
            $jsonInput = file_get_contents('php://input');
            error_log("BaseController DEBUG: Raw input = '$jsonInput'");
            
            if (!empty($jsonInput)) {
                $jsonData = json_decode($jsonInput, true);
                if ($jsonData !== null && json_last_error() === JSON_ERROR_NONE) {
                    $data = $jsonData;
                    error_log("BaseController DEBUG: Using JSON data");
                } else {
                    error_log("BaseController DEBUG: JSON decode error: " . json_last_error_msg());
                }
            }
        }
        
        // Cachear el resultado
        $cachedData = $data;
        $cached = true;
        
        error_log("BaseController DEBUG: Final data = " . json_encode($data));
        
        return $data;
    }
    
    /**
     * Valida que los campos requeridos estén presentes
     */
    protected function validateRequired($data, $requiredFields) {
        $missing = [];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            $this->sendResponse(400, "Campos requeridos faltantes: " . implode(', ', $missing), null, false);
        }
        
        return true;
    }
    
    /**
     * Sanitiza una cadena de texto
     */
    protected function sanitizeString($string) {
        return htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Valida que un ID sea numérico y positivo
     */
    protected function validateId($id) {
        if (!is_numeric($id) || $id <= 0) {
            $this->sendResponse(400, "ID inválido", null, false);
        }
        return intval($id);
    }
}
?>
