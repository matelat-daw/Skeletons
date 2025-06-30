<?php
/**
 * Test del proceso de creación de usuario paso a paso
 */

echo "=== DEBUG DEL PROCESO DE REGISTRO ===\n\n";

try {
    require_once 'config/env.php';
    require_once 'config/database_manager.php';
    require_once 'models/Register.php';
    require_once 'models/UserRepository.php';
    require_once 'services/AuthService.php';
    
    // Datos de prueba
    $userData = [
        'email' => 'testdebug' . time() . '@example.com',
        'password' => 'TestPassword123!',
        'confirmPassword' => 'TestPassword123!',
        'nick' => 'debuguser' . time(),
        'name' => 'Debug',
        'surname1' => 'User'
    ];
    
    echo "1. DATOS DE ENTRADA:\n";
    print_r($userData);
    echo "\n";
    
    // Paso 1: Crear modelo Register
    echo "2. CREANDO MODELO REGISTER...\n";
    $registerModel = new Register($userData);
    echo "✅ Modelo creado\n";
    echo "Email: " . $registerModel->email . "\n";
    echo "Password: " . (empty($registerModel->password) ? 'VACÍO' : 'ESTABLECIDO') . "\n";
    echo "Password2: " . (empty($registerModel->password2) ? 'VACÍO' : 'ESTABLECIDO') . "\n";
    echo "Nick: " . $registerModel->nick . "\n\n";
    
    // Paso 2: Validar modelo
    echo "3. VALIDANDO MODELO...\n";
    $isValid = $registerModel->isValid();
    echo "¿Es válido?: " . ($isValid ? 'SÍ' : 'NO') . "\n";
    if (!$isValid) {
        $errors = $registerModel->getValidationErrors();
        echo "Errores:\n";
        foreach ($errors as $error) {
            echo "- $error\n";
        }
        exit();
    }
    echo "✅ Modelo válido\n\n";
    
    // Paso 3: Conectar a base de datos
    echo "4. CONECTANDO A BASE DE DATOS...\n";
    $dbManager = new DatabaseManager();
    $conn = $dbManager->getNexusUsersConnection();
    $userRepository = new UserRepository($conn);
    echo "✅ Conexión establecida\n\n";
    
    // Paso 4: Verificar emails duplicados
    echo "5. VERIFICANDO EMAIL DUPLICADO...\n";
    $existingUser = $userRepository->findByEmail($registerModel->email);
    if ($existingUser) {
        echo "❌ Email ya existe\n";
        exit();
    }
    echo "✅ Email disponible\n\n";
    
    // Paso 5: Verificar nick duplicado
    echo "6. VERIFICANDO NICK DUPLICADO...\n";
    $nickExists = $userRepository->nickExists($registerModel->nick);
    if ($nickExists) {
        echo "❌ Nick ya existe\n";
        exit();
    }
    echo "✅ Nick disponible\n\n";
    
    // Paso 6: Generar ID
    echo "7. GENERANDO ID...\n";
    $registerModel->generateId();
    echo "ID generado: " . $registerModel->id . "\n\n";
    
    // Paso 7: Hash de contraseña
    echo "8. GENERANDO HASH DE CONTRASEÑA...\n";
    $hashedPassword = AuthService::hashPassword($registerModel->password);
    echo "Hash generado: " . substr($hashedPassword, 0, 30) . "...\n\n";
    
    // Paso 8: Convertir a User
    echo "9. CONVIRTIENDO A MODELO USER...\n";
    $user = $registerModel->toUser();
    $user->password_hash = $hashedPassword;
    echo "✅ Usuario convertido\n";
    echo "User ID: " . $user->id . "\n";
    echo "User Email: " . $user->email . "\n";
    echo "User Nick: " . $user->nick . "\n\n";
    
    // Paso 9: Crear en base de datos
    echo "10. CREANDO USUARIO EN BASE DE DATOS...\n";
    $createResult = $userRepository->create($user);
    
    if ($createResult) {
        echo "✅ Usuario creado exitosamente en la base de datos\n\n";
        
        // Paso 10: Probar EmailConfirmation
        echo "11. PROBANDO EmailConfirmation...\n";
        require_once 'models/EmailConfirmation.php';
        $emailConfirmation = new EmailConfirmation($conn);
        
        $token = $emailConfirmation->generateToken($user->id, $user->email);
        echo "Token generado: " . substr($token, 0, 20) . "...\n";
        
        if ($emailConfirmation->saveToken()) {
            echo "✅ Token guardado en base de datos\n\n";
            
            // Paso 11: EmailService
            echo "12. PROBANDO EmailService...\n";
            require_once 'services/EmailService.php';
            $emailService = new EmailService();
            
            echo "Configuración de email:\n";
            echo "From Email: " . ($_ENV['SMTP_FROM_EMAIL'] ?? 'No configurado') . "\n";
            echo "From Name: " . ($_ENV['SMTP_FROM_NAME'] ?? 'No configurado') . "\n";
            echo "Base URL: " . ($_ENV['APP_BASE_URL'] ?? 'No configurado') . "\n\n";
            
            echo "✅ TODAS LAS PRUEBAS EXITOSAS\n";
            echo "El sistema debería funcionar correctamente\n\n";
            
            // Limpiar datos de prueba
            echo "13. LIMPIANDO DATOS DE PRUEBA...\n";
            $conn->prepare("DELETE FROM EmailConfirmationTokens WHERE email = ?")->execute([$user->email]);
            $conn->prepare("DELETE FROM AspNetUsers WHERE Email = ?")->execute([$user->email]);
            echo "✅ Datos de prueba eliminados\n";
            
        } else {
            echo "❌ Error guardando token\n";
        }
    } else {
        echo "❌ Error creando usuario en la base de datos\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== FIN DEL DEBUG ===\n";
?>
