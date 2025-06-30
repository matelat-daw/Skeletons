<?php
/**
 * GoogleAuthService - Servicio para autenticación con Google
 * Equivalente al manejo de GoogleJsonWebSignature en ASP.NET
 */
class GoogleAuthService {
    private $googleClientId;
    
    public function __construct() {
        // Cargar Google Client ID desde variables de entorno
        $this->googleClientId = $_ENV['GOOGLE_CLIENT_ID'] ?? getenv('GOOGLE_CLIENT_ID') ?? getenv('Google-Client-Id');
        
        if (empty($this->googleClientId)) {
            throw new Exception('Google Client ID no está configurado en las variables de entorno');
        }
    }

    /**
     * Validar token de Google (equivalente a GoogleJsonWebSignature.ValidateAsync)
     * Requiere la librería google/apiclient para validación completa
     */
    public function validateGoogleToken($token) {
        if (empty($token)) {
            throw new InvalidArgumentException('Token de Google requerido');
        }

        try {
            // Para validación completa, necesitarías instalar: composer require google/apiclient
            // Por ahora, validación básica del formato JWT
            if (!$this->isValidJWTFormat($token)) {
                throw new Exception('Formato de token inválido');
            }

            // Validar contra la API de Google
            $payload = $this->verifyWithGoogleAPI($token);
            
            if (!$payload) {
                throw new Exception('Token inválido o expirado');
            }

            // Validar audiencia (Client ID)
            if (!isset($payload['aud']) || $payload['aud'] !== $this->googleClientId) {
                throw new Exception('Token no pertenece a esta aplicación');
            }

            return [
                'email' => $payload['email'] ?? null,
                'name' => $payload['name'] ?? null,
                'picture' => $payload['picture'] ?? null,
                'email_verified' => $payload['email_verified'] ?? false,
                'sub' => $payload['sub'] ?? null // Google user ID
            ];

        } catch (Exception $e) {
            error_log("Error validando token de Google: " . $e->getMessage());
            throw new Exception('Token de Google inválido: ' . $e->getMessage());
        }
    }

    /**
     * Verificar formato básico de JWT
     */
    private function isValidJWTFormat($token) {
        $parts = explode('.', $token);
        return count($parts) === 3;
    }

    /**
     * Verificar token con la API de Google
     */
    private function verifyWithGoogleAPI($token) {
        $url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . $token;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            return false;
        }
        
        $payload = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }
        
        return $payload;
    }

    /**
     * Obtener Google Client ID configurado
     */
    public function getClientId() {
        return $this->googleClientId;
    }

    /**
     * Verificar si la configuración de Google está completa
     */
    public function isConfigured() {
        return !empty($this->googleClientId);
    }

    /**
     * Obtener información básica del token sin validar (solo para debug)
     */
    public function getTokenInfo($token) {
        if (!$this->isValidJWTFormat($token)) {
            return null;
        }

        try {
            $parts = explode('.', $token);
            $payload = base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1]));
            return json_decode($payload, true);
        } catch (Exception $e) {
            return null;
        }
    }
}
?>
