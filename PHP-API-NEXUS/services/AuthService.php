<?php
require_once __DIR__ . '/../models/User.php';

class AuthService {
    
    // Verificar contraseña con el hash del usuario
    public static function verifyPassword($password, $hashedPassword) {
        // Si el password hash está vacío, no permitir login
        if (empty($hashedPassword)) {
            return false;
        }
        
        // Verificar contraseña usando el formato de ASP.NET Identity
        return self::verifyAspNetIdentityPassword($password, $hashedPassword);
    }
    
    // Verificar contraseña con formato ASP.NET Identity
    private static function verifyAspNetIdentityPassword($password, $hashedPassword) {
        try {
            // El hash de ASP.NET Identity está en Base64
            $hashBytes = base64_decode($hashedPassword);
            
            if ($hashBytes === false || strlen($hashBytes) < 61) {
                return false;
            }
            
            $format = ord($hashBytes[0]);
            if ($format !== 0x01) {
                return false; // Solo soportamos formato 0x01
            }
            
            $prf = unpack('N', substr($hashBytes, 1, 4))[1];
            $iterations = unpack('N', substr($hashBytes, 5, 4))[1];
            $saltLen = unpack('N', substr($hashBytes, 9, 4))[1];
            
            // Validar que sea el formato esperado
            if ($prf !== 2 || $saltLen !== 16) {
                return false;
            }
            
            // Extraer salt y subkey
            $salt = substr($hashBytes, 13, 16);
            $expectedSubkey = substr($hashBytes, 29, 32);
            
            // Generar hash de la contraseña proporcionada usando PBKDF2 con SHA512
            $actualSubkey = hash_pbkdf2('sha512', $password, $salt, $iterations, 32, true);
            
            // Comparar subkeys de forma segura
            return hash_equals($expectedSubkey, $actualSubkey);
            
        } catch (Exception $e) {
            error_log("Error verificando contraseña ASP.NET Identity: " . $e->getMessage());
            return false;
        }
    }
    
    // Generar hash de contraseña compatible con ASP.NET Identity
    public static function hashPassword($password) {
        $prf = 2; // 2 = HMACSHA512
        $iterCount = 10000;
        $saltLen = 16;
        $subKeyLen = 32;

        $salt = random_bytes($saltLen);
        $subKey = hash_pbkdf2('sha512', $password, $salt, $iterCount, $subKeyLen, true);

        $buffer = pack('C', 0x01) .
                  pack('N', $prf) .
                  pack('N', $iterCount) .
                  pack('N', $saltLen) .
                  $salt .
                  $subKey;

        return base64_encode($buffer);
    }

    // Validar credenciales de login
    public static function validateCredentials($email, $password) {
        if (empty($email) || empty($password)) {
            return false;
        }

        // Validar formato de email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        // Validar longitud de contraseña
        if (strlen($password) < 6) {
            return false;
        }

        return true;
    }

    // Generar datos para JWT
    public static function generateJwtPayload(User $user, $expiration = null) {
        // Si no se especifica expiración, usar la predeterminada (24 horas)
        if ($expiration === null) {
            $expiration = 24 * 60 * 60; // 24 horas
        }
        
        return [
            'user_id' => $user->id,
            'email' => $user->email,
            'nick' => $user->nick,
            'username' => $user->nick,
            'emailConfirmed' => $user->emailConfirmed,
            'iat' => time(),
            'exp' => time() + $expiration
        ];
    }

    // Validar que el usuario puede hacer login
    public static function canLogin(User $user) {
        // Verificar que el email esté confirmado
        if (!$user->emailConfirmed) {
            return [
                'can_login' => false,
                'reason' => 'Email no confirmado'
            ];
        }

        // Verificar que tenga hash de contraseña
        if (empty($user->passwordHash)) {
            return [
                'can_login' => false,
                'reason' => 'Usuario sin contraseña configurada'
            ];
        }

        return [
            'can_login' => true,
            'reason' => null
        ];
    }
}
?>
