<?php
/**
 * Debug del sistema de ruteo
 */

// Configuración de errores para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json; charset=UTF-8');

echo "=== DEBUG DE RUTEO ===\n\n";

echo "REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "\n";
echo "PATH_INFO: " . ($_SERVER['PATH_INFO'] ?? 'NO DEFINIDO') . "\n";
echo "QUERY_STRING: " . ($_SERVER['QUERY_STRING'] ?? 'NO DEFINIDO') . "\n";

// Simular la lógica del router
$scriptName = $_SERVER['SCRIPT_NAME'];
$basePath = dirname($scriptName);
if ($basePath === '/') {
    $basePath = '';
}

echo "BASE_PATH calculado: '$basePath'\n";

$requestUri = $_SERVER['REQUEST_URI'];
$path = strtok($requestUri, '?');

echo "PATH sin query string: '$path'\n";

if ($basePath !== '' && strpos($path, $basePath) === 0) {
    $path = substr($path, strlen($basePath));
}

echo "PATH final: '$path'\n\n";

// Cargar el router y mostrar las rutas
require_once 'config/Router.php';
$router = new Router();
$routes = $router->showRoutes();

echo "RUTAS REGISTRADAS:\n";
foreach ($routes as $route) {
    echo "- {$route['method']} {$route['path']}\n";
}

echo "\nBUSCANDO COINCIDENCIAS:\n";
$method = $_SERVER['REQUEST_METHOD'];
foreach ($routes as $route) {
    if ($route['method'] === $method) {
        echo "Probando: {$route['path']} vs '$path'\n";
        if (preg_match($route['pattern'], $path)) {
            echo "  ✅ COINCIDE!\n";
        } else {
            echo "  ❌ No coincide (pattern: {$route['pattern']})\n";
        }
    }
}
?>
