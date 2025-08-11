<?php
require_once '../config/config.php';
require_once '../config/Database.php';
require_once '../app/Helpers/Auth.php';
require_once '../app/Helpers/Utils.php';

class Router {
    private $routes = [];
    private $defaultController = 'Dashboard';
    private $defaultAction = 'index';
    
    public function __construct() {
        // Define routes
        $this->routes = [
            '' => ['controller' => 'Dashboard', 'action' => 'index'],
            'login' => ['controller' => 'Auth', 'action' => 'login'],
            'logout' => ['controller' => 'Auth', 'action' => 'logout'],
            'dashboard' => ['controller' => 'Dashboard', 'action' => 'index'],
            'eventos' => ['controller' => 'Eventos', 'action' => 'index'],
            'eventos/crear' => ['controller' => 'Eventos', 'action' => 'crear'],
            'eventos/editar' => ['controller' => 'Eventos', 'action' => 'editar'],
            'eventos/asistentes' => ['controller' => 'Eventos', 'action' => 'asistentes'],
            'usuarios' => ['controller' => 'Usuarios', 'action' => 'index'],
            'reportes' => ['controller' => 'Reportes', 'action' => 'index'],
            'configuracion' => ['controller' => 'Configuracion', 'action' => 'index'],
            
            // Public routes
            'evento' => ['controller' => 'PublicEvent', 'action' => 'show'],
            'registro' => ['controller' => 'PublicEvent', 'action' => 'register'],
            'historial' => ['controller' => 'PublicEvent', 'action' => 'historial'],
            'validar' => ['controller' => 'Validation', 'action' => 'validate'],
        ];
    }
    
    public function route() {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $path = trim($path, '/');
        
        // Remove public/ from path if present
        if (strpos($path, 'public/') === 0) {
            $path = substr($path, 7);
        }
        
        // Handle dynamic routes
        if (strpos($path, 'evento/') === 0) {
            $_GET['slug'] = substr($path, 7);
            $route = $this->routes['evento'];
        } elseif (isset($this->routes[$path])) {
            $route = $this->routes[$path];
        } else {
            $route = ['controller' => $this->defaultController, 'action' => $this->defaultAction];
        }
        
        $controllerName = $route['controller'] . 'Controller';
        $actionName = $route['action'];
        
        $controllerFile = '../app/Controllers/' . $controllerName . '.php';
        
        if (file_exists($controllerFile)) {
            require_once $controllerFile;
            
            if (class_exists($controllerName)) {
                $controller = new $controllerName();
                
                if (method_exists($controller, $actionName)) {
                    $controller->$actionName();
                } else {
                    $this->showError(404, 'Action not found');
                }
            } else {
                $this->showError(404, 'Controller not found');
            }
        } else {
            $this->showError(404, 'Controller file not found');
        }
    }
    
    private function showError($code, $message) {
        http_response_code($code);
        echo "<h1>Error $code</h1><p>$message</p>";
    }
}

// Start routing
$router = new Router();
$router->route();