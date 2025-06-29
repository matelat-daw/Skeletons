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
