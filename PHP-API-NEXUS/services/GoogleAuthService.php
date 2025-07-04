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

            // Obtener información del token sin validación (para debug y desarrollo)
            $tokenInfo = $this->getTokenInfo($token);
            
            if (!$tokenInfo) {
                throw new Exception('No se pudo decodificar el token');
            }
            
            // Verificar que el token no esté expirado
            if (isset($tokenInfo['exp']) && $tokenInfo['exp'] < time()) {
                throw new Exception('Token expirado');
            }
            
            // Verificar que el issuer sea Google
            if (!isset($tokenInfo['iss']) || $tokenInfo['iss'] !== 'https://accounts.google.com') {
                throw new Exception('Token no emitido por Google');
            }
            
            // Verificar audiencia (Client ID) - ESTO ES CRÍTICO PARA SEGURIDAD
            if (!isset($tokenInfo['aud']) || $tokenInfo['aud'] !== $this->googleClientId) {
                error_log("GOOGLE AUTH VALIDATION ERROR - AUD mismatch:");
                error_log("Expected: " . $this->googleClientId);
                error_log("Received: " . ($tokenInfo['aud'] ?? 'NULL'));
                throw new Exception('Token no pertenece a esta aplicación');
            }
            
            // En desarrollo, permitir tokens válidos sin validación con API de Google
            $isDevelopment = ($_ENV['ENVIRONMENT'] ?? 'production') === 'development';
            
            if ($isDevelopment) {
                error_log("DEBUG: Usando validación de desarrollo (sin verificar con Google API)");
                return [
                    'email' => $tokenInfo['email'] ?? null,
                    'name' => $tokenInfo['name'] ?? null,
                    'picture' => $tokenInfo['picture'] ?? null,
                    'email_verified' => $tokenInfo['email_verified'] ?? true,
                    'sub' => $tokenInfo['sub'] ?? null
                ];
            }

            // En producción, validar contra la API de Google
            $payload = $this->verifyWithGoogleAPI($token);
            
            if (!$payload) {
                throw new Exception('Token inválido o expirado');
            }

            // Validar audiencia nuevamente con la respuesta de Google
            if (!isset($payload['aud']) || $payload['aud'] !== $this->googleClientId) {
                error_log("GOOGLE AUTH VALIDATION ERROR - AUD mismatch:");
                error_log("Expected: " . $this->googleClientId);
                error_log("Received: " . ($payload['aud'] ?? 'NULL'));
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
        $valid = count($parts) === 3;
        
        if (!$valid) {
            error_log("DEBUG JWT Format: Token has " . count($parts) . " parts, expected 3");
            error_log("DEBUG JWT Format: Token start: " . substr($token, 0, 50) . "...");
        }
        
        return $valid;
    }

    /**
     * Verificar token con la API de Google
     */
    private function verifyWithGoogleAPI($token) {
        $url = 'https://oauth2.googleapis.com/tokeninfo';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'id_token=' . urlencode($token));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        // Log de debugging
        error_log("DEBUG GoogleAuthService: Google API Response HTTP Code: $httpCode");
        error_log("DEBUG GoogleAuthService: Google API Response: " . substr($response, 0, 500));
        
        if ($curlError) {
            error_log("DEBUG GoogleAuthService: CURL Error: $curlError");
            return false;
        }
        
        if ($httpCode !== 200) {
            error_log("DEBUG GoogleAuthService: Google API Error HTTP $httpCode: $response");
            return false;
        }
        
        $payload = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("DEBUG GoogleAuthService: JSON Parse Error: " . json_last_error_msg());
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
