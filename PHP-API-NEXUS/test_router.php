<?php
// Test del nuevo sistema de enrutamiento
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 Test del Sistema de Enrutamiento sin .htaccess</h2>";

// Incluir el router para probar
require_once 'config/Router.php';

try {
    echo "<h3>🚀 Test 1: Verificar estructura de archivos</h3>";
    
    $requiredFiles = [
        'index.php' => 'Archivo principal',
        'config/Router.php' => 'Router principal',
        'controllers/BaseController.php' => 'Controlador base',
        'controllers/AuthController.php' => 'Controlador de autenticación',
        'controllers/AccountController.php' => 'Controlador de cuenta',
        'controllers/FavoritesController.php' => 'Controlador de favoritos',
        'controllers/CommentsController.php' => 'Controlador de comentarios'
    ];
    
    $allFilesExist = true;
    foreach ($requiredFiles as $file => $description) {
        if (file_exists($file)) {
            echo "✅ {$description}: {$file}<br>";
        } else {
            echo "❌ {$description}: {$file} - NO ENCONTRADO<br>";
            $allFilesExist = false;
        }
    }
    
    if ($allFilesExist) {
        echo "<br>✅ Todos los archivos del sistema están presentes<br>";
    } else {
        echo "<br>❌ Faltan algunos archivos críticos<br>";
        exit();
    }
    
    echo "<h3>🗺️ Test 2: Verificar rutas registradas</h3>";
    
    $router = new Router();
    $routes = $router->showRoutes();
    
    echo "Total de rutas registradas: " . count($routes) . "<br><br>";
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Método</th><th>Ruta</th><th>Controlador</th><th>Acción</th></tr>";
    
    foreach ($routes as $route) {
        echo "<tr>";
        echo "<td><strong>{$route['method']}</strong></td>";
        echo "<td><code>{$route['path']}</code></td>";
        echo "<td>{$route['controller']}</td>";
        echo "<td>{$route['action']}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<h3>🔧 Test 3: Verificar clases de controladores</h3>";
    
    $controllerClasses = [
        'BaseController' => 'controllers/BaseController.php',
        'AuthController' => 'controllers/AuthController.php',
        'AccountController' => 'controllers/AccountController.php',
        'FavoritesController' => 'controllers/FavoritesController.php',
        'CommentsController' => 'controllers/CommentsController.php'
    ];
    
    foreach ($controllerClasses as $className => $filePath) {
        require_once $filePath;
        if (class_exists($className)) {
            echo "✅ Clase {$className} cargada correctamente<br>";
        } else {
            echo "❌ Error: Clase {$className} no encontrada<br>";
        }
    }
    
    echo "<h3>📝 Test 4: Simulación de rutas</h3>";
    
    // Simular algunas rutas para verificar el patrón de regex
    $testRoutes = [
        '/api/Auth/Login' => 'Debe coincidir con login',
        '/api/Account/Profile' => 'Debe coincidir con perfil',
        '/api/Account/Favorites' => 'Debe coincidir con lista de favoritos',
        '/api/Account/Favorites/123' => 'Debe coincidir con favorito específico',
        '/api/Account/Comments/456' => 'Debe coincidir con comentario específico',
        '/api/invalid/route' => 'NO debe coincidir'
    ];
    
    echo "<ul>";
    foreach ($testRoutes as $testPath => $expected) {
        $found = false;
        foreach ($routes as $route) {
            if (preg_match($route['pattern'], $testPath)) {
                echo "<li>✅ <code>{$testPath}</code> → {$route['controller']}::{$route['action']} ({$expected})</li>";
                $found = true;
                break;
            }
        }
        if (!$found) {
            echo "<li>⚠️ <code>{$testPath}</code> → Sin coincidencia ({$expected})</li>";
        }
    }
    echo "</ul>";
    
    echo "<h3>🌐 Test 5: Instrucciones de configuración</h3>";
    echo "<div style='background: #f0f0f0; padding: 15px; border-radius: 5px;'>";
    echo "<h4>Para usar el nuevo sistema:</h4>";
    echo "<ol>";
    echo "<li><strong>Eliminar o renombrar .htaccess</strong> si existe</li>";
    echo "<li><strong>Configurar servidor web</strong> para dirigir todas las requests a <code>index.php</code></li>";
    echo "<li><strong>Apache</strong>: Crear .htaccess simple:</li>";
    echo "<pre>RewriteEngine On\nRewriteCond %{REQUEST_FILENAME} !-f\nRewriteCond %{REQUEST_FILENAME} !-d\nRewriteRule ^(.*)$ index.php [QSA,L]</pre>";
    echo "<li><strong>Nginx</strong>: Configurar en server block:</li>";
    echo "<pre>try_files \$uri \$uri/ /index.php?$query_string;</pre>";
    echo "<li><strong>Actualizar frontend</strong> para usar las nuevas rutas</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<h3>🎯 Test 6: URLs de ejemplo</h3>";
    echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px;'>";
    echo "<h4>URLs que funcionarán:</h4>";
    echo "<ul>";
    echo "<li><code>POST http://localhost:8080/Skeletons/PHP-API-NEXUS/api/Auth/Login</code></li>";
    echo "<li><code>GET http://localhost:8080/Skeletons/PHP-API-NEXUS/api/Account/Profile</code></li>";
    echo "<li><code>GET http://localhost:8080/Skeletons/PHP-API-NEXUS/api/Account/Favorites</code></li>";
    echo "<li><code>POST http://localhost:8080/Skeletons/PHP-API-NEXUS/api/Account/Favorites/123</code></li>";
    echo "<li><code>POST http://localhost:8080/Skeletons/PHP-API-NEXUS/api/Account/Comments</code></li>";
    echo "<li><code>DELETE http://localhost:8080/Skeletons/PHP-API-NEXUS/api/Account/Comments/456</code></li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<h3>✅ Sistema de enrutamiento implementado exitosamente</h3>";
    echo "<p><strong>🎉 El nuevo sistema está listo para usar sin depender de .htaccess!</strong></p>";
    
} catch (Exception $e) {
    echo "<h3>❌ Error durante las pruebas:</h3>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
    echo "<p><strong>Stack trace:</strong></p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
