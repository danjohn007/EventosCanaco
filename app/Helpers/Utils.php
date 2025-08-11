<?php
class Utils {
    public static function log($userId, $accion, $tabla, $referenciaId, $descripcion = '') {
        try {
            $db = Database::getInstance();
            
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            
            $db->execute(
                "INSERT INTO logs (user_id, accion, tabla, referencia_id, descripcion, ip, user_agent) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)",
                [$userId, $accion, $tabla, $referenciaId, $descripcion, $ip, $userAgent]
            );
        } catch (Exception $e) {
            error_log("Error logging action: " . $e->getMessage());
        }
    }
    
    public static function generateUniqueCode($length = 8) {
        do {
            $code = '';
            for ($i = 0; $i < $length; $i++) {
                $code .= mt_rand(0, 9);
            }
            
            // Check if code already exists
            $db = Database::getInstance();
            $exists = $db->fetch("SELECT id FROM registros WHERE codigo_unico = ?", [$code]);
        } while ($exists);
        
        return $code;
    }
    
    public static function generateSlug($text) {
        // Convert to lowercase
        $slug = strtolower($text);
        
        // Replace spaces and special characters
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        
        // Remove leading/trailing dashes
        $slug = trim($slug, '-');
        
        // Ensure uniqueness
        $db = Database::getInstance();
        $originalSlug = $slug;
        $counter = 1;
        
        while (true) {
            $exists = $db->fetch("SELECT id FROM eventos WHERE slug = ?", [$slug]);
            if (!$exists) break;
            
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
    
    public static function uploadFile($file, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif']) {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($extension, $allowedTypes)) {
            return false;
        }
        
        $filename = uniqid() . '.' . $extension;
        $uploadPath = __DIR__ . '/../../storage/uploads/' . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return $filename;
        }
        
        return false;
    }
    
    public static function formatDate($date, $format = 'd/m/Y H:i') {
        if (!$date) return '';
        return date($format, strtotime($date));
    }
    
    public static function truncate($text, $length = 100) {
        if (strlen($text) <= $length) return $text;
        return substr($text, 0, $length) . '...';
    }
    
    public static function sanitize($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitize'], $input);
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    public static function validateRFC($rfc) {
        // Basic RFC validation for Mexico
        $rfc = strtoupper(trim($rfc));
        
        // Pattern for individual (13 chars) or company (12 chars)
        if (preg_match('/^[A-Z&Ñ]{3,4}[0-9]{6}[A-Z0-9]{3}$/', $rfc)) {
            return $rfc;
        }
        
        return false;
    }
    
    public static function validatePhone($phone) {
        // Basic phone validation
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (strlen($phone) >= 10 && strlen($phone) <= 15) {
            return $phone;
        }
        
        return false;
    }
    
    public static function generateQRHash($registroId, $codigo) {
        return hash_hmac('sha256', $registroId . ':' . $codigo . ':' . time(), SECRET_KEY);
    }
    
    public static function validateQRHash($registroId, $codigo, $hash) {
        // For validation, we need to check against possible hashes within a time window
        // This is a simplified version - in production you might want to store the hash
        $expectedHash = hash_hmac('sha256', $registroId . ':' . $codigo, SECRET_KEY);
        return hash_equals($expectedHash, $hash);
    }
    
    public static function redirect($url, $statusCode = 302) {
        header("Location: $url", true, $statusCode);
        exit;
    }
    
    public static function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}