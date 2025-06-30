<?php
/**
 * Debug del Login - Probar login paso a paso
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database_manager.php';
require_once 'models/UserRepository.php';
require_once 'models/Login.php';
require_once 'services/AuthService.php';

echo "<h2>Debug del Login</h2>";

try {
    // Inicializar dependencias
    $dbManager = new DatabaseManager();
    $userRepository = new UserRepository($dbManager->getConnection('NexusUsers'));
    
    echo "<h3>1. Conexión a base de datos: ✅</h3>";
    
    // Datos de prueba
    $testEmail = "cesarmatelat@gmail.com";
    $testPassword = "Cesar@Peon";
    
    echo "<h3>2. Datos de prueba:</h3>";
    echo "Email: $testEmail<br>";
    echo "Password: $testPassword<br>";
    
    // Crear modelo Login
    $loginModel = new Login([
        'email' => $testEmail,
        'password' => $testPassword,
        'rememberMe' => false
    ]);
    
    echo "<h3>3. Modelo Login creado: ✅</h3>";
    
    // Sanitizar email
    $loginModel->sanitizeEmail();
    echo "Email sanitizado: " . $loginModel->email . "<br>";
    
    // Validar modelo
    if (!$loginModel->isValid()) {
        $errors = $loginModel->getValidationErrors();
        echo "<h3>4. Errores de validación:</h3>";
        foreach ($errors as $error) {
            echo "- $error<br>";
        }
    } else {
        echo "<h3>4. Validación del modelo: ✅</h3>";
    }
    
    // Validar credenciales
    if (AuthService::validateCredentials($loginModel->email, $loginModel->password)) {
        echo "<h3>5. Validación de credenciales: ✅</h3>";
    } else {
        echo "<h3>5. Validación de credenciales: ❌</h3>";
    }
    
    // Buscar usuario
    echo "<h3>6. Buscando usuario por email...</h3>";
    $user = $userRepository->findByEmail($loginModel->email);
    
    if ($user) {
        echo "Usuario encontrado: " . $user->email . "<br>";
        echo "Nick: " . $user->nick . "<br>";
        echo "Email confirmado: " . ($user->emailConfirmed ? 'Sí' : 'No') . "<br>";
        echo "Password hash existe: " . (!empty($user->passwordHash) ? 'Sí' : 'No') . "<br>";
        
        // Verificar si puede hacer login
        $loginCheck = AuthService::canLogin($user);
        if ($loginCheck['can_login']) {
            echo "<h3>7. Usuario puede hacer login: ✅</h3>";
            
            // Verificar contraseña
            if (AuthService::verifyPassword($loginModel->password, $user->passwordHash)) {
                echo "<h3>8. Verificación de contraseña: ✅</h3>";
                echo "<h2>🎉 Login exitoso!</h2>";
            } else {
                echo "<h3>8. Verificación de contraseña: ❌</h3>";
                echo "La contraseña no coincide con el hash almacenado.<br>";
            }
        } else {
            echo "<h3>7. Usuario NO puede hacer login: ❌</h3>";
            echo "Razón: " . $loginCheck['reason'] . "<br>";
        }
    } else {
        echo "Usuario NO encontrado<br>";
        
        // Verificar si hay usuarios en la tabla
        echo "<h3>Usuarios existentes en la base de datos:</h3>";
        try {
            $conn = $dbManager->getConnection('NexusUsers');
            $stmt = $conn->query("SELECT TOP 5 Id, Email, Nick, EmailConfirmed FROM AspNetUsers");
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($users)) {
                echo "No hay usuarios en la base de datos.<br>";
                echo "<strong>Sugerencia:</strong> Registra un usuario primero.<br>";
            } else {
                echo "<table border='1'>";
                echo "<tr><th>ID</th><th>Email</th><th>Nick</th><th>Email Confirmado</th></tr>";
                foreach ($users as $u) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($u['Id']) . "</td>";
                    echo "<td>" . htmlspecialchars($u['Email']) . "</td>";
                    echo "<td>" . htmlspecialchars($u['Nick']) . "</td>";
                    echo "<td>" . ($u['EmailConfirmed'] ? 'Sí' : 'No') . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        } catch (Exception $e) {
            echo "Error consultando usuarios: " . $e->getMessage() . "<br>";
        }
    }
    
} catch (Exception $e) {
    echo "<h3>❌ Error:</h3>";
    echo $e->getMessage() . "<br>";
    echo "<h4>Stack trace:</h4>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
