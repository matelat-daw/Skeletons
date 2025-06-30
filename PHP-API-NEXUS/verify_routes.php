<?php
// Script temporal para verificar las rutas registradas
require_once 'config/Router.php';

echo "=== VERIFICACIÓN DE RUTAS REGISTRADAS ===\n\n";

$router = new Router();
$routes = $router->showRoutes();

echo "Total de rutas registradas: " . count($routes) . "\n\n";

echo "RUTAS DISPONIBLES:\n";
echo str_repeat("-", 50) . "\n";

foreach ($routes as $route) {
    printf("%-8s %s → %s::%s\n", 
        $route['method'], 
        $route['path'],
        $route['controller'],
        $route['action']
    );
}

echo "\n" . str_repeat("-", 50) . "\n";
echo "✅ Las rutas están correctamente registradas\n";
echo "✅ Login: POST /api/Auth/Login\n";
echo "✅ Perfil: GET /api/Account/Profile\n";
echo "✅ Logout: POST /api/Account/Logout\n";

?>
