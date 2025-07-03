<?php
/**
 * BaseController - Controlador base con funcionalidades comunes
 */
abstract class BaseController {
    protected $jwt;
    protected $dbManager;
    
    public function __construct() {
        // Configurar CORS headers primero
        $this->setupCORS();
        
        // Incluir dependencias comunes
        require_once 'config/database_manager.php';
        require_once 'config/jwt.php';
        
        $this->dbManager = new DatabaseManager();
        $this->jwt = new JWTHandler();
    }
    
    /**
     * Configura headers CORS para todas las respuestas
     */
    private function setupCORS() {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        $host = $_SERVER['HTTP_HOST'] ?? '';
        
        error_log("CORS DEBUG - Origin: '$origin', Host: '$host'");
        
        // Lista de orígenes permitidos
        $allowed_origins = [
            'http://localhost:4200',
            'https://localhost:4200',
            'http://127.0.0.1:4200',
            'https://127.0.0.1:4200',
        ];
        
        // Verificar si el origen es un subdominio de ngrok-free.app
        $is_ngrok_origin = preg_match('/^https:\/\/[a-zA-Z0-9\-]+\.ngrok-free\.app$/', $origin);
        
        // Verificar si estamos siendo accedidos a través de Ngrok
        $is_ngrok_host = strpos($host, '.ngrok-free.app') !== false;
        
        error_log("CORS DEBUG - is_ngrok_origin: " . ($is_ngrok_origin ? 'true' : 'false'));
        error_log("CORS DEBUG - is_ngrok_host: " . ($is_ngrok_host ? 'true' : 'false'));
        error_log("CORS DEBUG - in_allowed_origins: " . (in_array($origin, $allowed_origins) ? 'true' : 'false'));
        
        // Si estamos en Ngrok o el origen está permitido, configurar CORS
        if ($is_ngrok_host || $is_ngrok_origin || in_array($origin, $allowed_origins)) {
            // Si tenemos un origen específico, usarlo. Si no, permitir el origen que viene en la petición
            if ($origin && ($is_ngrok_host || $is_ngrok_origin || in_array($origin, $allowed_origins))) {
                header("Access-Control-Allow-Origin: " . $origin);
                header("Access-Control-Allow-Credentials: true");
                error_log("CORS DEBUG - Set specific origin: $origin with credentials: true");
            } else {
                // Fallback: permitir cualquier origen pero sin credentials
                header("Access-Control-Allow-Origin: *");
                header("Access-Control-Allow-Credentials: false");
                error_log("CORS DEBUG - Set wildcard origin with credentials: false");
            }
            
            header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
            header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin, ngrok-skip-browser-warning");
            header("Access-Control-Max-Age: 86400");
        } else {
            error_log("CORS DEBUG - CORS headers NOT set - criteria not met");
        }
        
        // Manejar preflight OPTIONS
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
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
        
        // Log temporal para debugging
        error_log("SEND RESPONSE: Status=$statusCode, Success=" . ($success ? 'true' : 'false') . ", Message='$message'");
        
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
     * Método universal que maneja ambos formatos y todos los métodos HTTP
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
        
        // Detectar si es multipart/form-data o form-urlencoded
        $isMultipart = strpos(strtolower($contentType), 'multipart/form-data') !== false;
        $isFormUrlEncoded = strpos(strtolower($contentType), 'application/x-www-form-urlencoded') !== false;
        
        if ($isMultipart) {
            error_log("BaseController DEBUG: Detected multipart/form-data");
            
            // Para PATCH, PUT, etc. con multipart, tenemos que parsear php://input manualmente
            if ($requestMethod !== 'POST' || empty($_POST)) {
                error_log("BaseController DEBUG: Method is $requestMethod, parsing multipart manually");
                $data = $this->parseMultipartFormData();
            } else {
                // Para POST normal, usar $_POST
                $data = $_POST;
                error_log("BaseController DEBUG: Using $_POST for POST method");
            }
            
        } elseif ($isFormUrlEncoded && !empty($_POST)) {
            // Datos enviados como application/x-www-form-urlencoded
            $data = $_POST;
            error_log("BaseController DEBUG: Using POST data (form-urlencoded)");
            
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
     * Parsea multipart/form-data desde php://input para métodos no-POST
     */
    private function parseMultipartFormData() {
        $input = file_get_contents('php://input');
        $data = [];
        
        if (empty($input)) {
            error_log("BaseController DEBUG: Empty php://input for multipart");
            return $data;
        }
        
        // Obtener el boundary del Content-Type
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        preg_match('/boundary=(.+)$/', $contentType, $matches);
        
        if (empty($matches[1])) {
            error_log("BaseController DEBUG: No boundary found in Content-Type");
            return $data;
        }
        
        $boundary = '--' . $matches[1];
        error_log("BaseController DEBUG: Found boundary: $boundary");
        
        // Dividir por boundary
        $parts = explode($boundary, $input);
        
        foreach ($parts as $part) {
            $part = trim($part);
            if (empty($part) || $part === '--') continue;
            
            // Separar headers y contenido
            if (strpos($part, "\r\n\r\n") !== false) {
                list($headers, $content) = explode("\r\n\r\n", $part, 2);
            } elseif (strpos($part, "\n\n") !== false) {
                list($headers, $content) = explode("\n\n", $part, 2);
            } else {
                continue;
            }
            
            // Extraer el nombre del campo
            if (preg_match('/name="([^"]*)"/', $headers, $nameMatch)) {
                $fieldName = $nameMatch[1];
                $data[$fieldName] = rtrim($content, "\r\n");
                error_log("BaseController DEBUG: Parsed field '$fieldName' = '" . $data[$fieldName] . "'");
            }
        }
        
        error_log("BaseController DEBUG: Parsed multipart data: " . json_encode($data));
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
