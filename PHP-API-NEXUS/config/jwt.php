<?php
// Cargar variables de entorno
require_once __DIR__ . '/env.php';

class JWTHandler {
    private $secret_key;
    private $algorithm = 'HS256';
    
    public function __construct() {
        // Clave secreta desde variable de entorno
        $this->secret_key = $_ENV['JWT_SECRET'] ?? 'tu_clave_secreta_super_segura_cambiar_en_produccion';
    }
    
    public function generateToken($userId, $email, $nick = null) {
        $header = json_encode(['typ' => 'JWT', 'alg' => $this->algorithm]);
        
        $payload = json_encode([
            'user_id' => $userId,
            'email' => $email,
            'nick' => $nick,
            'iat' => time(),
            'exp' => time() + (24 * 60 * 60) // 24 horas
        ]);
        
        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        
        $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, $this->secret_key, true);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        return $base64Header . "." . $base64Payload . "." . $base64Signature;
    }
    
    public function generateTokenFromPayload($payload) {
        $header = json_encode(['typ' => 'JWT', 'alg' => $this->algorithm]);
        $payloadJson = json_encode($payload);
        
        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payloadJson));
        
        $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, $this->secret_key, true);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        return $base64Header . "." . $base64Payload . "." . $base64Signature;
    }
    
    public function validateToken($token) {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return false;
        }
        
        list($header, $payload, $signature) = $parts;
        
        // Verificar firma
        $validSignature = hash_hmac('sha256', $header . "." . $payload, $this->secret_key, true);
        $validBase64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($validSignature));
        
        if (!hash_equals($signature, $validBase64Signature)) {
            return false;
        }
        
        // Decodificar payload
        $payloadData = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $payload)), true);
        
        // Verificar expiración
        if (isset($payloadData['exp']) && $payloadData['exp'] < time()) {
            return false;
        }
        
        return $payloadData;
    }
    
    public function setCookie($token, $name = 'auth_token', $expiration = null) {
        // Si no se especifica expiración, usar la predeterminada (24 horas)
        if ($expiration === null) {
            $expiration = 24 * 60 * 60; // 24 horas
        }
        
        setcookie(
            $name,
            $token,
            [
                'expires' => time() + $expiration,
                'path' => '/',
                'domain' => '', // Ajustar según tu dominio
                'secure' => false, // Cambiar a true en HTTPS
                'httponly' => true, // Previene acceso desde JavaScript
                'samesite' => 'Lax' // Protección CSRF
            ]
        );
    }
    
    public function getTokenFromCookie($name = 'auth_token') {
        return $_COOKIE[$name] ?? null;
    }
    
    public function clearCookie($name = 'auth_token') {
        setcookie(
            $name,
            '',
            [
                'expires' => time() - 3600, // Expira en el pasado
                'path' => '/',
                'domain' => '', // Debe coincidir con el dominio usado al crear la cookie
                'secure' => false, // Cambiar a true en HTTPS
                'httponly' => true,
                'samesite' => 'Lax'
            ]
        );
        
        // También eliminar de $_COOKIE para esta ejecución
        if (isset($_COOKIE[$name])) {
            unset($_COOKIE[$name]);
        }
    }
}
?>