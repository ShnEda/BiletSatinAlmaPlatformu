<?php

class Session {
    
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
            
            // Session güvenlik ayarları
            if (!isset($_SESSION['initialized'])) {
                session_regenerate_id(true);
                $_SESSION['initialized'] = true;
                $_SESSION['created_at'] = time();
            }
            
            // Session timeout kontrolü (2 saat)
            if (isset($_SESSION['last_activity']) && 
                (time() - $_SESSION['last_activity'] > 7200)) {
                self::destroy();
                return false;
            }
            
            $_SESSION['last_activity'] = time();
        }
        return true;
    }
    
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }
    
    public static function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }
    
    public static function has($key) {
        return isset($_SESSION[$key]);
    }
    
    public static function remove($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }
    
    public static function destroy() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
    }
    
    // Kullanıcı oturum metodları
    public static function login($user) {
        self::set('user_id', $user['id']);
        self::set('user_email', $user['email']);
        self::set('user_name', $user['name']);
        self::set('user_role', $user['role']);
        self::set('user_firma_id', $user['firma_id']);
        self::set('user_balance', $user['balance']);
    }
    
    public static function logout() {
        self::destroy();
        session_start();
    }
    
    public static function isLoggedIn() {
        return self::has('user_id');
    }
    
    public static function getUser() {
        if (!self::isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => self::get('user_id'),
            'email' => self::get('user_email'),
            'name' => self::get('user_name'),
            'role' => self::get('user_role'),
            'firma_id' => self::get('user_firma_id'),
            'balance' => self::get('user_balance')
        ];
    }
    
    public static function getUserId() {
        return self::get('user_id');
    }
    
    public static function getUserRole() {
        return self::get('user_role', 'guest');
    }
    
    public static function isAdmin() {
        return self::getUserRole() === 'admin';
    }
    
    public static function isFirmaAdmin() {
        return self::getUserRole() === 'firma_admin';
    }
    
    public static function isUser() {
        return self::getUserRole() === 'user';
    }
    
    public static function updateBalance($newBalance) {
        self::set('user_balance', $newBalance);
    }
    
    // Flash mesaj metodları
    public static function setFlash($type, $message) {
        self::set('flash_' . $type, $message);
    }
    
    public static function getFlash($type) {
        $message = self::get('flash_' . $type);
        self::remove('flash_' . $type);
        return $message;
    }
    
    public static function hasFlash($type) {
        return self::has('flash_' . $type);
    }
    
    // CSRF token metodları
    public static function generateToken() {
        $token = bin2hex(random_bytes(32));
        self::set('csrf_token', $token);
        return $token;
    }
    
    public static function getToken() {
        if (!self::has('csrf_token')) {
            return self::generateToken();
        }
        return self::get('csrf_token');
    }
    
    public static function validateToken($token) {
        return hash_equals(self::getToken(), $token);
    }
}