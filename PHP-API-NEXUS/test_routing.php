<?php
/**
 * Test específico para verificar el routing
 */

// Simular la request que viene del frontend
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI'] = '/api/Auth/Login';
$_SERVER['SCRIPT_NAME'] = '/index.php';

echo "=== SIMULACIÓN DE REQUEST ===\n";
echo "REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "\n\n";

require_once 'config/Router.php';

try {
    $router = new Router();
    echo "Router creado exitosamente\n";
    
    // Probar el método getCurrentPath manualmente
    $reflection = new ReflectionClass($router);
    $method = $reflection->getMethod('getCurrentPath');
    $method->setAccessible(true);
    
    $currentPath = $method->invoke($router);
    echo "getCurrentPath() devuelve: '$currentPath'\n\n";
    
    // Mostrar rutas registradas
    $routes = $router->showRoutes();
    echo "Rutas registradas:\n";
    foreach ($routes as $route) {
        if ($route['method'] === 'POST' && strpos($route['path'], 'Login') !== false) {
            echo "- {$route['method']} {$route['path']} (pattern: {$route['pattern']})\n";
            
            // Probar el patrón
            if (preg_match($route['pattern'], $currentPath)) {
                echo "  ✅ PATRÓN COINCIDE\n";
            } else {
                echo "  ❌ PATRÓN NO COINCIDE\n";
            }
        }
    }
    
    echo "\nEjecutando handleRequest()...\n";
    $router->handleRequest();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>
