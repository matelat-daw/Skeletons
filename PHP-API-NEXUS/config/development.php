<?php
// Configuración de desarrollo para CORS más permisivo
// Este archivo debe ser incluido en endpoints durante desarrollo

// Función para configurar CORS de manera más permisiva en desarrollo
function setupDevelopmentCORS() {
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    
    // Lista de puertos comunes para desarrollo
    $developmentPorts = ['3000', '4200', '5173', '8080', '8081', '9000'];
    $allowedHosts = ['localhost', '127.0.0.1'];
    
    $isAllowed = false;
    
    // Verificar si el origen es de desarrollo
    foreach ($allowedHosts as $host) {
        foreach ($developmentPorts as $port) {
            if ($origin === "http://$host:$port" || $origin === "https://$host:$port") {
                $isAllowed = true;
                break 2;
            }
        }
    }
    
    if ($isAllowed) {
        header("Access-Control-Allow-Origin: $origin");
        header("Access-Control-Allow-Credentials: true");
    } else {
        // Fallback para desarrollo local
        if (strpos($origin, 'localhost') !== false || strpos($origin, '127.0.0.1') !== false) {
            header("Access-Control-Allow-Origin: $origin");
            header("Access-Control-Allow-Credentials: true");
        }
    }
    
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin");
    header("Access-Control-Max-Age: 86400");
    
    // Manejar preflight requests
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
}

// Función para verificar si estamos en desarrollo
function isDevelopment() {
    return ($_SERVER['SERVER_NAME'] === 'localhost' || 
            $_SERVER['SERVER_NAME'] === '127.0.0.1' ||
            strpos($_SERVER['HTTP_HOST'], 'localhost') !== false);
}
?>
