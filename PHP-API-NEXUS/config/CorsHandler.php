<?php
/**
 * CorsHandler - Manejo centralizado de CORS
 */
class CorsHandler {
    private static $allowedOrigins = [
        'http://localhost:4200',
        'https://localhost:4200',
        'http://127.0.0.1:4200',
        'https://127.0.0.1:4200',
    ];

    /**
     * Configura headers CORS para todas las respuestas
     */
    public static function setupCORS(): void {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        $host = $_SERVER['HTTP_HOST'] ?? '';
        
        // Verificar condiciones para CORS
        $isNgrokOrigin = preg_match('/^https:\/\/[a-zA-Z0-9\-]+\.ngrok-free\.app$/', $origin);
        $isNgrokHost = strpos($host, '.ngrok-free.app') !== false;
        $isAllowedOrigin = in_array($origin, self::$allowedOrigins);
        
        // Configurar headers CORS si cumple alguna condición
        if ($isNgrokHost || $isNgrokOrigin || $isAllowedOrigin) {
            if ($origin && ($isNgrokHost || $isNgrokOrigin || $isAllowedOrigin)) {
                header("Access-Control-Allow-Origin: " . $origin);
                header("Access-Control-Allow-Credentials: true");
            } else {
                header("Access-Control-Allow-Origin: *");
                header("Access-Control-Allow-Credentials: false");
            }
            
            header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
            header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin, ngrok-skip-browser-warning");
            header("Access-Control-Max-Age: 86400");
        }
    }

    /**
     * Maneja peticiones OPTIONS (preflight)
     */
    public static function handlePreflight(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            self::setupCORS();
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'CORS preflight handled']);
            exit();
        }
    }

    /**
     * Configuración completa de CORS (setup + preflight)
     */
    public static function initialize(): void {
        self::setupCORS();
        self::handlePreflight();
    }
}
