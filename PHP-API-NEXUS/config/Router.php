<?php
/**
 * Router Class - Maneja el enrutamiento de la API sin .htaccess
 */
class Router {
    private $routes = [];
    private $basePath;

    public function __construct() {
        // Detectar la ruta base automáticamente
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $this->basePath = dirname($scriptName);
        if ($this->basePath === '/') {
            $this->basePath = '';
        }
        
        // Registrar todas las rutas
        $this->registerRoutes();
    }

    /**
     * Registra todas las rutas disponibles en la API
     */
    private function registerRoutes() {
        // Rutas de Autenticación
        $this->addRoute('POST', '/api/Auth/Login', 'AuthController', 'login');
        $this->addRoute('POST', '/api/Auth/Register', 'AuthController', 'register');
        $this->addRoute('POST', '/api/Auth/ExternalLogin', 'AuthController', 'externalLogin'); // Ruta para login con Google
        $this->addRoute('GET', '/api/Auth/ConfirmEmail', 'AuthController', 'confirmEmail');
        $this->addRoute('POST', '/api/Auth/ResendConfirmation', 'AuthController', 'resendConfirmation');
        
        // Rutas de Constelaciones (las más específicas primero)
        $this->addRoute('GET', '/api/Constellations/GetStars/{id}', 'ConstellationsController', 'getStars');
        $this->addRoute('GET', '/api/Constellations/ConstelationLines', 'ConstellationsController', 'getConstellationLines');
        $this->addRoute('GET', '/api/Constellations/{id}', 'ConstellationsController', 'getById');
        $this->addRoute('GET', '/api/Constellations', 'ConstellationsController', 'getAll');
        
        // Rutas de Estrellas (las más específicas primero)
        $this->addRoute('GET', '/api/Stars/{id}', 'StarsController', 'getById');
        $this->addRoute('GET', '/api/Stars', 'StarsController', 'getAll');
        
        // Rutas de Cuenta/Perfil
        $this->addRoute('GET', '/api/Account/Profile', 'AccountController', 'getProfile');
        $this->addRoute('GET', '/api/Account/GetComments/{id}', 'AccountController', 'getComments');
        $this->addRoute('POST', '/api/Account/Logout', 'AccountController', 'logout');
        $this->addRoute('PATCH', '/api/Account/Update', 'AccountController', 'updateProfile');
        $this->addRoute('DELETE', '/api/Account/Delete', 'AccountController', 'deleteAccount');
        
        // RUTA DE TEST TEMPORAL - ELIMINAR DESPUÉS
        $this->addRoute('POST', '/api/Account/TestData', 'AccountController', 'testDataReceive');
        $this->addRoute('PATCH', '/api/Account/TestData', 'AccountController', 'testDataReceive');
        
        // Rutas de Favoritos
        $this->addRoute('GET', '/api/Account/Favorites', 'FavoritesController', 'getUserFavorites');
        $this->addRoute('GET', '/api/Account/Favorites/{id}', 'FavoritesController', 'checkFavorite');
        $this->addRoute('POST', '/api/Account/Favorites/{id}', 'FavoritesController', 'addFavorite');
        $this->addRoute('DELETE', '/api/Account/Favorites/{id}', 'FavoritesController', 'removeFavorite');
        
        
        // Rutas de Comentarios (exactamente como en ASP.NET Core)
        $this->addRoute('GET', '/api/Comments', 'CommentsController', 'getAllComments');
        $this->addRoute('GET', '/api/Comments/ById/{id}', 'CommentsController', 'getCommentById');
        $this->addRoute('GET', '/api/Comments/User/{userId}', 'CommentsController', 'getCommentsByUser');
        $this->addRoute('GET', '/api/Comments/Constellation/{id}', 'CommentsController', 'getCommentsByConstellation');
        $this->addRoute('PUT', '/api/Comments/{id}', 'CommentsController', 'putComment');
        $this->addRoute('POST', '/api/Comments', 'CommentsController', 'postComment');
        $this->addRoute('DELETE', '/api/Comments/{id}', 'CommentsController', 'deleteComment');
        
        // Rutas de Comentarios adicionales (mantenidas para compatibilidad)
        $this->addRoute('GET', '/api/Account/Comments', 'CommentsController', 'getCommentsByUser');
    }

    /**
     * Agrega una ruta al router
     */
    private function addRoute($method, $path, $controller, $action) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'controller' => $controller,
            'action' => $action,
            'pattern' => $this->pathToRegex($path)
        ];
    }

    /**
     * Convierte una ruta con parámetros a regex
     */
    private function pathToRegex($path) {
        // Escapar caracteres especiales de regex excepto {}
        $pattern = preg_quote($path, '#');  // Usar # como delimitador en lugar de /
        
        // Convertir {id} a grupos de captura
        $pattern = preg_replace('/\\\{([^}]+)\\\}/', '([^/]+)', $pattern);
        
        return '#^' . $pattern . '$#';  // Usar # como delimitador
    }

    /**
     * Obtiene la ruta actual de la request
     */
    private function getCurrentPath() {
        // Primero verificar si hay un parámetro 'request' en la query string
        if (isset($_GET['request'])) {
            return '/' . ltrim($_GET['request'], '/');
        }
        
        // Si no, usar REQUEST_URI (para casos de URLs limpias)
        $requestUri = $_SERVER['REQUEST_URI'];
        
        // Remover query string
        $path = strtok($requestUri, '?');
        
        // Remover base path si existe
        if ($this->basePath !== '' && strpos($path, $this->basePath) === 0) {
            $path = substr($path, strlen($this->basePath));
        }
        
        return $path;
    }

    /**
     * Extrae parámetros de la URL
     */
    private function extractParams($routePath, $currentPath) {
        $params = [];
        
        // Encontrar parámetros en la ruta
        preg_match_all('/\{([^}]+)\}/', $routePath, $paramNames);
        
        // Extraer valores usando regex
        $pattern = $this->pathToRegex($routePath);
        if (preg_match($pattern, $currentPath, $matches)) {
            // Omitir el primer match (que es la cadena completa)
            array_shift($matches);
            
            // Asignar valores a nombres de parámetros
            for ($i = 0; $i < count($paramNames[1]); $i++) {
                if (isset($matches[$i])) {
                    $params[$paramNames[1][$i]] = $matches[$i];
                }
            }
        }
        
        return $params;
    }

    /**
     * Maneja la request actual
     */
    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $currentPath = $this->getCurrentPath();
        
        // Debug temporal - Log de la petición
        error_log("ROUTER: Method=$method, Path='$currentPath', URI='" . $_SERVER['REQUEST_URI'] . "'");
        
        // NO manejar OPTIONS aquí - ya se maneja en index.php
        // El Router solo maneja rutas normales (GET, POST, PUT, DELETE, PATCH)
        
        // Buscar ruta coincidente
        foreach ($this->routes as $route) {
            error_log("ROUTER: Checking route: {$route['method']} {$route['path']} vs $method $currentPath");
            if ($route['method'] === $method && preg_match($route['pattern'], $currentPath)) {
                error_log("ROUTER: Route matched! Executing {$route['controller']}::{$route['action']}");
                // Extraer parámetros de la URL
                $params = $this->extractParams($route['path'], $currentPath);
                
                // Ejecutar controlador
                $this->executeController($route['controller'], $route['action'], $params);
                return;
            }
        }
        
        // Si no se encuentra la ruta, devolver 404
        error_log("ROUTER: No route found for $method $currentPath");
        $this->sendNotFound();
    }

    /**
     * Ejecuta el controlador correspondiente
     */
    private function executeController($controllerName, $action, $params = []) {
        $controllerFile = "controllers/{$controllerName}.php";
        
        if (!file_exists($controllerFile)) {
            throw new Exception("Controlador no encontrado: {$controllerName}");
        }
        
        require_once $controllerFile;
        
        if (!class_exists($controllerName)) {
            throw new Exception("Clase de controlador no encontrada: {$controllerName}");
        }
        
        $controller = new $controllerName();
        
        if (!method_exists($controller, $action)) {
            throw new Exception("Método no encontrado: {$controllerName}::{$action}");
        }
        
        // Ejecutar la acción con parámetros
        $controller->$action($params);
    }

    /**
     * Envía respuesta 404
     */
    private function sendNotFound() {
        // Debug temporal
        error_log("404 - Path: " . $this->getCurrentPath() . " - Method: " . $_SERVER['REQUEST_METHOD']);
        error_log("Origin: " . ($_SERVER['HTTP_ORIGIN'] ?? 'No origin'));
        
        http_response_code(404);
        echo json_encode([
            'message' => 'Endpoint no encontrado',
            'success' => false,
            'path' => $this->getCurrentPath(),
            'method' => $_SERVER['REQUEST_METHOD'],
            'debug' => [
                'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
                'script_name' => $_SERVER['SCRIPT_NAME'] ?? '',
                'origin' => $_SERVER['HTTP_ORIGIN'] ?? ''
            ]
        ]);
    }

    /**
     * Método para debugging - muestra todas las rutas registradas
     */
    public function showRoutes() {
        return $this->routes;
    }
}
?>
