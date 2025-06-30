<?php
/**
 * Script de prueba para verificar el acceso a la API desde el navegador
 */

// Mostrar información de debugging
echo "<h2>Debugging API Access</h2>";

echo "<p><strong>Request URI:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p><strong>Request Method:</strong> " . $_SERVER['REQUEST_METHOD'] . "</p>";
echo "<p><strong>Script Name:</strong> " . $_SERVER['SCRIPT_NAME'] . "</p>";
echo "<p><strong>HTTP Host:</strong> " . $_SERVER['HTTP_HOST'] . "</p>";

// Incluir el router para testear
require_once 'config/Router.php';

echo "<h3>Rutas registradas:</h3>";
$router = new Router();
$routes = $router->showRoutes();

echo "<ul>";
foreach ($routes as $route) {
    echo "<li><strong>{$route['method']}</strong> {$route['path']} → {$route['controller']}::{$route['action']}</li>";
}
echo "</ul>";

// Test directo del endpoint
echo "<h3>Test directo del endpoint Constellations:</h3>";
try {
    require_once 'controllers/ConstellationsController.php';
    $controller = new ConstellationsController();
    
    echo "<p>Controlador cargado exitosamente.</p>";
    
    // Simular llamada
    ob_start();
    $controller->getAll();
    $output = ob_get_clean();
    
    echo "<p><strong>Respuesta del controlador:</strong></p>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    
    // Analizar la estructura JSON
    $data = json_decode($output, true);
    if ($data) {
        echo "<h4>Estructura de datos:</h4>";
        echo "<p><strong>Claves principales:</strong> " . implode(', ', array_keys($data)) . "</p>";
        if (isset($data['data'])) {
            echo "<p><strong>Claves en 'data':</strong> " . implode(', ', array_keys($data['data'])) . "</p>";
            if (isset($data['data']['constellations'])) {
                echo "<p><strong>Tipo de 'constellations':</strong> " . gettype($data['data']['constellations']) . "</p>";
                echo "<p><strong>Cantidad de elementos:</strong> " . (is_array($data['data']['constellations']) ? count($data['data']['constellations']) : 'No es array') . "</p>";
                if (is_array($data['data']['constellations']) && count($data['data']['constellations']) > 0) {
                    echo "<p><strong>Primer elemento:</strong></p>";
                    echo "<pre>" . htmlspecialchars(json_encode($data['data']['constellations'][0], JSON_PRETTY_PRINT)) . "</pre>";
                }
            }
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

echo "<h3>Test directo del endpoint Stars:</h3>";
try {
    require_once 'controllers/StarsController.php';
    $controller = new StarsController();
    
    echo "<p>Controlador cargado exitosamente.</p>";
    
    // Simular llamada (solo los primeros 5 para no sobrecargar)
    ob_start();
    $controller->getAll();
    $output = ob_get_clean();
    
    // Analizar la estructura JSON
    $data = json_decode($output, true);
    if ($data) {
        echo "<h4>Estructura de datos Stars:</h4>";
        echo "<p><strong>Claves principales:</strong> " . implode(', ', array_keys($data)) . "</p>";
        if (isset($data['data'])) {
            echo "<p><strong>Claves en 'data':</strong> " . implode(', ', array_keys($data['data'])) . "</p>";
            if (isset($data['data']['stars'])) {
                echo "<p><strong>Tipo de 'stars':</strong> " . gettype($data['data']['stars']) . "</p>";
                echo "<p><strong>Cantidad de elementos:</strong> " . (is_array($data['data']['stars']) ? count($data['data']['stars']) : 'No es array') . "</p>";
                if (is_array($data['data']['stars']) && count($data['data']['stars']) > 0) {
                    echo "<p><strong>Primer elemento:</strong></p>";
                    echo "<pre>" . htmlspecialchars(json_encode($data['data']['stars'][0], JSON_PRETTY_PRINT)) . "</pre>";
                }
            }
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
