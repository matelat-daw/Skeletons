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
        $this->addRoute('GET', '/api/Auth/ConfirmEmail', 'AuthController', 'confirmEmail');
        $this->addRoute('POST', '/api/Auth/ResendConfirmation', 'AuthController', 'resendConfirmation');
        
        // Rutas de Cuenta/Perfil
        $this->addRoute('GET', '/api/Account/Profile', 'AccountController', 'getProfile');
        $this->addRoute('POST', '/api/Account/Logout', 'AccountController', 'logout');
        $this->addRoute('PATCH', '/api/Account/Update', 'AccountController', 'updateProfile');
        $this->addRoute('DELETE', '/api/Account/Delete', 'AccountController', 'deleteAccount');
        
        // Rutas de Favoritos
        $this->addRoute('GET', '/api/Account/Favorites', 'FavoritesController', 'getUserFavorites');
        $this->addRoute('GET', '/api/Account/Favorites/{id}', 'FavoritesController', 'checkFavorite');
        $this->addRoute('POST', '/api/Account/Favorites/{id}', 'FavoritesController', 'addFavorite');
        $this->addRoute('DELETE', '/api/Account/Favorites/{id}', 'FavoritesController', 'removeFavorite');
        
        // Rutas de Comentarios
        $this->addRoute('GET', '/api/Account/Comments', 'CommentsController', 'getUserComments');
        $this->addRoute('GET', '/api/Account/Comments/{id}', 'CommentsController', 'getComment');
        $this->addRoute('POST', '/api/Account/Comments', 'CommentsController', 'addComment');
        $this->addRoute('PUT', '/api/Account/Comments/{id}', 'CommentsController', 'updateComment');
        $this->addRoute('DELETE', '/api/Account/Comments/{id}', 'CommentsController', 'deleteComment');
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
        $pattern = preg_quote($path, '/');
        
        // Convertir {id} a grupos de captura
        $pattern = preg_replace('/\\\{([^}]+)\\\}/', '([^/]+)', $pattern);
        
        return '/^' . $pattern . '$/';
    }

    /**
     * Obtiene la ruta actual de la request
     */
    private function getCurrentPath() {
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
        
        // Buscar ruta coincidente
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $currentPath)) {
                // Extraer parámetros de la URL
                $params = $this->extractParams($route['path'], $currentPath);
                
                // Ejecutar controlador
                $this->executeController($route['controller'], $route['action'], $params);
                return;
            }
        }
        
        // Si no se encuentra la ruta, devolver 404
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
        http_response_code(404);
        echo json_encode([
            'message' => 'Endpoint no encontrado',
            'success' => false,
            'path' => $this->getCurrentPath(),
            'method' => $_SERVER['REQUEST_METHOD']
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
