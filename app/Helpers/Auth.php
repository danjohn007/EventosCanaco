<?php
class Auth {
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header('Location: /public/login');
            exit;
        }
    }
    
    public static function isSuperAdmin() {
        return self::isLoggedIn() && $_SESSION['user_role'] === 'superadmin';
    }
    
    public static function isGestor() {
        return self::isLoggedIn() && $_SESSION['user_role'] === 'gestor';
    }
    
    public static function requireSuperAdmin() {
        self::requireLogin();
        if (!self::isSuperAdmin()) {
            header('HTTP/1.1 403 Forbidden');
            die('Access denied');
        }
    }
    
    public static function login($email, $password) {
        $db = Database::getInstance();
        
        $user = $db->fetch(
            "SELECT * FROM users WHERE email = ? AND activo = 1",
            [$email]
        );
        
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nombre'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            // Log successful login
            Utils::log($user['id'], 'login', 'users', $user['id'], 'Usuario logueado exitosamente');
            
            return true;
        }
        
        return false;
    }
    
    public static function logout() {
        $userId = $_SESSION['user_id'] ?? null;
        
        if ($userId) {
            Utils::log($userId, 'logout', 'users', $userId, 'Usuario cerró sesión');
        }
        
        session_destroy();
        header('Location: /public/login');
        exit;
    }
    
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    public static function getCurrentUser() {
        if (!self::isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'nombre' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'],
            'role' => $_SESSION['user_role']
        ];
    }
}