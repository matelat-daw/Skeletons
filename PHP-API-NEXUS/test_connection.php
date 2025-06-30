<?php
/**
 * Test de conexión básica
 */

echo "=== TEST DE CONEXIÓN BÁSICA ===\n\n";

// Test 1: Cargar variables de entorno
echo "1. Cargando variables de entorno...\n";
try {
    require_once 'config/env.php';
    echo "✅ Variables de entorno cargadas\n";
    echo "Environment: " . ($_ENV['ENVIRONMENT'] ?? 'No definido') . "\n";
    echo "Debug: " . ($_ENV['DEBUG'] ?? 'No definido') . "\n\n";
} catch (Exception $e) {
    echo "❌ Error cargando env: " . $e->getMessage() . "\n\n";
}

// Test 2: Conexión a base de datos
echo "2. Probando conexión a base de datos...\n";
try {
    require_once 'config/database_manager.php';
    $dbManager = new DatabaseManager();
    $conn = $dbManager->getNexusUsersConnection();
    echo "✅ Conexión a base de datos exitosa\n";
    
    // Probar una consulta simple
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "Total usuarios en BD: " . $row['count'] . "\n\n";
    }
} catch (Exception $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "\n\n";
}

// Test 3: Verificar tabla de confirmación de email
echo "3. Verificando tabla EmailConfirmationTokens...\n";
try {
    $result = $conn->query("SELECT COUNT(*) as count FROM EmailConfirmationTokens");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "✅ Tabla existe - Total tokens: " . $row['count'] . "\n\n";
    }
} catch (Exception $e) {
    echo "❌ Error con tabla EmailConfirmationTokens: " . $e->getMessage() . "\n";
    echo "Puede que necesites ejecutar el script SQL de creación\n\n";
}

// Test 4: Configuración JWT
echo "4. Verificando configuración JWT...\n";
try {
    require_once 'config/jwt.php';
    $jwt = new JWTHandler();
    echo "✅ JWT configurado correctamente\n\n";
} catch (Exception $e) {
    echo "❌ Error con JWT: " . $e->getMessage() . "\n\n";
}

// Test 5: Probar modelos
echo "5. Probando modelos...\n";
try {
    require_once 'models/Register.php';
    $register = new Register();
    echo "✅ Modelo Register cargado\n";
    
    require_once 'models/User.php';
    $user = new User();
    echo "✅ Modelo User cargado\n";
    
    require_once 'models/EmailConfirmation.php';
    $emailConfirm = new EmailConfirmation($conn);
    echo "✅ Modelo EmailConfirmation cargado\n\n";
} catch (Exception $e) {
    echo "❌ Error cargando modelos: " . $e->getMessage() . "\n\n";
}

// Test 6: Probar servicios
echo "6. Probando servicios...\n";
try {
    require_once 'services/AuthService.php';
    $authService = new AuthService();
    echo "✅ AuthService cargado\n";
    
    require_once 'services/EmailService.php';
    $emailService = new EmailService();
    echo "✅ EmailService cargado\n\n";
} catch (Exception $e) {
    echo "❌ Error cargando servicios: " . $e->getMessage() . "\n\n";
}

echo "=== FIN DEL TEST DE CONEXIÓN ===\n";
?>
