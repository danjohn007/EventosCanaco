<?php
class BaseController {
    protected $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
        
        // If database is not available, show setup page
        if (!$this->db->isConnected()) {
            $this->showSetupPage();
            exit;
        }
    }
    
    protected function view($viewName, $data = []) {
        extract($data);
        
        $viewFile = __DIR__ . '/../Views/' . $viewName . '.php';
        
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            echo "View not found: $viewName";
        }
    }
    
    protected function layout($layoutName, $content, $data = []) {
        $data['content'] = $content;
        extract($data);
        
        $layoutFile = __DIR__ . '/../Views/layouts/' . $layoutName . '.php';
        
        if (file_exists($layoutFile)) {
            include $layoutFile;
        } else {
            echo $content;
        }
    }
    
    protected function requireAuth() {
        Auth::requireLogin();
    }
    
    protected function requireSuperAdmin() {
        Auth::requireSuperAdmin();
    }
    
    protected function getCurrentUser() {
        return Auth::getCurrentUser();
    }
    
    protected function isPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
    
    protected function getInput($key, $default = null) {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }
    
    protected function validateCsrf() {
        $token = $this->getInput('csrf_token');
        if (!$token || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            die('CSRF token validation failed');
        }
    }
    
    protected function generateCsrfToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    protected function showSetupPage() {
        echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - CANACO Eventos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/public/assets/css/auth.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-body text-center">
                        <h2 class="text-canaco mb-4">
                            <i class="fas fa-database me-2"></i>
                            Configuración de Base de Datos
                        </h2>
                        <div class="alert alert-warning">
                            <h5>Base de datos no disponible</h5>
                            <p>Para usar el sistema de eventos CANACO, necesitas configurar la base de datos MySQL.</p>
                        </div>
                        
                        <div class="row text-start">
                            <div class="col-md-6">
                                <h6>Pasos de configuración:</h6>
                                <ol class="list-group list-group-numbered">
                                    <li class="list-group-item">Instalar MySQL/MariaDB</li>
                                    <li class="list-group-item">Crear base de datos <code>canaco_eventos</code></li>
                                    <li class="list-group-item">Ejecutar el script <code>sql/schema.sql</code></li>
                                    <li class="list-group-item">Configurar credenciales en <code>config/config.php</code></li>
                                </ol>
                            </div>
                            <div class="col-md-6">
                                <h6>Credenciales predeterminadas:</h6>
                                <ul class="list-group">
                                    <li class="list-group-item"><strong>Host:</strong> localhost</li>
                                    <li class="list-group-item"><strong>Database:</strong> canaco_eventos</li>
                                    <li class="list-group-item"><strong>Usuario:</strong> root</li>
                                    <li class="list-group-item"><strong>Contraseña:</strong> (vacía)</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <a href="javascript:location.reload()" class="btn btn-canaco">
                                <i class="fas fa-sync-alt me-2"></i>
                                Reintentar Conexión
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>';
    }
}