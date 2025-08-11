<?php
require_once 'BaseController.php';

class AuthController extends BaseController {
    
    public function login() {
        // If already logged in, redirect to dashboard
        if (Auth::isLoggedIn()) {
            Utils::redirect('/public/dashboard');
        }
        
        $error = null;
        
        if ($this->isPost()) {
            $email = $this->getInput('email');
            $password = $this->getInput('password');
            
            if (empty($email) || empty($password)) {
                $error = 'Email y contraseña son requeridos';
            } else {
                if (Auth::login($email, $password)) {
                    Utils::redirect('/public/dashboard');
                } else {
                    $error = 'Credenciales inválidas';
                }
            }
        }
        
        $this->view('auth/login', [
            'error' => $error,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }
    
    public function logout() {
        Auth::logout();
    }
    
    public function forgotPassword() {
        $message = null;
        $error = null;
        
        if ($this->isPost()) {
            $email = $this->getInput('email');
            
            if (empty($email)) {
                $error = 'Email es requerido';
            } else {
                $user = $this->db->fetch(
                    "SELECT id, nombre FROM users WHERE email = ? AND activo = 1",
                    [$email]
                );
                
                if ($user) {
                    // Generate reset token
                    $token = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                    
                    // Store token (you might want a separate table for this)
                    $this->db->execute(
                        "UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?",
                        [$token, $expires, $user['id']]
                    );
                    
                    // Send email (implement later)
                    $message = 'Se ha enviado un enlace de recuperación a tu email';
                } else {
                    $error = 'Email no encontrado';
                }
            }
        }
        
        $this->view('auth/forgot_password', [
            'message' => $message,
            'error' => $error,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }
}