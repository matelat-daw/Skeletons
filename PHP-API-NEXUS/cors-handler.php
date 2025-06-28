<?php
// Fallback para manejar CORS cuando mod_headers no está disponible

// Obtener el origen de la request
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

// Lista de orígenes permitidos
$allowedOrigins = [
    'http://localhost:4200',
    'http://127.0.0.1:4200',
    'http://localhost:8080',
    'http://127.0.0.1:8080',
    'http://localhost:3000',
    'http://127.0.0.1:3000'
];

// Verificar si el origen está permitido
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
}

header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept");
header("Access-Control-Max-Age: 3600");

// Para requests OPTIONS, devolver 200 y terminar
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Si no es OPTIONS, continuar con el archivo solicitado
$requestUri = $_SERVER['REQUEST_URI'];
$scriptName = $_SERVER['SCRIPT_NAME'];

// Redirigir a la ruta correcta
if (preg_match('/^\/api\/([^\/]+)\/([^\/]+)/', $requestUri, $matches)) {
    $file = "api/{$matches[1]}/{$matches[2]}.php";
    if (file_exists($file)) {
        include $file;
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint no encontrado']);
    }
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Ruta no válida']);
}
?>
