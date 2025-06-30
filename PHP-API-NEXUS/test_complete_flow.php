<?php
/**
 * Test completo del flujo de registro y confirmación de email
 * Este archivo prueba el flujo completo de la API de autenticación
 */

// Incluir archivos necesarios
require_once 'config/env.php';
require_once 'config/database_manager.php';
require_once 'config/jwt.php';
require_once 'controllers/AuthController.php';

echo "=== PRUEBA COMPLETA DEL FLUJO DE AUTENTICACIÓN ===\n\n";

// Función para simular una request JSON
function simulateJsonRequest($data) {
    // Simular entrada JSON
    $GLOBALS['input_json'] = json_encode($data);
    
    // Mockear getJsonInput en BaseController
    return $data;
}

// Función para capturar la respuesta
function captureResponse() {
    ob_start();
    return ob_get_clean();
}

// Datos de prueba
$testUser = [
    'email' => 'test' . time() . '@example.com',
    'password' => 'TestPassword123!',
    'confirmPassword' => 'TestPassword123!',
    'nick' => 'testuser' . time(),
    'name' => 'Test',
    'surname1' => 'User',
    'surname2' => 'Example',
    'birthdate' => '1990-01-01',
    'gender' => 'M',
    'country' => 'ES',
    'city' => 'Madrid',
    'postal_code' => '28001',
    'phone' => '+34600000000'
];

echo "1. PROBANDO REGISTRO DE USUARIO\n";
echo "Email: " . $testUser['email'] . "\n";
echo "Nick: " . $testUser['nick'] . "\n\n";

try {
    // Simular request de registro
    $_POST = $testUser;
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['CONTENT_TYPE'] = 'application/json';
    
    // Crear instancia del controlador
    $authController = new AuthController();
    
    // Capturar respuesta
    ob_start();
    $authController->register();
    $registerResponse = ob_get_clean();
    
    echo "Respuesta del registro:\n";
    echo $registerResponse . "\n\n";
    
    // Decodificar respuesta para obtener datos
    $registerData = json_decode($registerResponse, true);
    
    if ($registerData && $registerData['success']) {
        echo "✅ REGISTRO EXITOSO\n";
        
        // Buscar token de confirmación en la base de datos
        $dbManager = new DatabaseManager();
        $conn = $dbManager->getNexusUsersConnection();
        
        $stmt = $conn->prepare("SELECT token FROM EmailConfirmationTokens WHERE email = ? AND used = 0 ORDER BY created_at DESC LIMIT 1");
        $stmt->bind_param('s', $testUser['email']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $confirmationToken = $row['token'];
            echo "Token de confirmación encontrado: " . substr($confirmationToken, 0, 20) . "...\n\n";
            
            echo "2. PROBANDO CONFIRMACIÓN DE EMAIL\n";
            
            // Simular request de confirmación
            $_GET['token'] = $confirmationToken;
            $_SERVER['REQUEST_METHOD'] = 'GET';
            
            ob_start();
            $authController->confirmEmail();
            $confirmResponse = ob_get_clean();
            
            echo "Respuesta de confirmación:\n";
            echo $confirmResponse . "\n\n";
            
            $confirmData = json_decode($confirmResponse, true);
            
            if ($confirmData && $confirmData['success']) {
                echo "✅ CONFIRMACIÓN EXITOSA\n\n";
                
                echo "3. PROBANDO LOGIN\n";
                
                // Simular request de login
                $loginData = [
                    'email' => $testUser['email'],
                    'password' => $testUser['password'],
                    'remember_me' => false
                ];
                
                $_POST = $loginData;
                $_SERVER['REQUEST_METHOD'] = 'POST';
                
                ob_start();
                $authController->login();
                $loginResponse = ob_get_clean();
                
                echo "Respuesta de login:\n";
                echo $loginResponse . "\n\n";
                
                $loginResponseData = json_decode($loginResponse, true);
                
                if ($loginResponseData && $loginResponseData['success']) {
                    echo "✅ LOGIN EXITOSO\n\n";
                    
                    echo "4. PROBANDO REENVÍO DE CONFIRMACIÓN (debe fallar porque ya está confirmado)\n";
                    
                    $resendData = ['email' => $testUser['email']];
                    $_POST = $resendData;
                    $_SERVER['REQUEST_METHOD'] = 'POST';
                    
                    ob_start();
                    $authController->resendConfirmation();
                    $resendResponse = ob_get_clean();
                    
                    echo "Respuesta de reenvío:\n";
                    echo $resendResponse . "\n\n";
                    
                    $resendResponseData = json_decode($resendResponse, true);
                    
                    if (!$resendResponseData['success']) {
                        echo "✅ REENVÍO CORRECTAMENTE RECHAZADO (email ya confirmado)\n\n";
                    } else {
                        echo "❌ Error: El reenvío no debería haber sido exitoso\n\n";
                    }
                } else {
                    echo "❌ Error en login: " . ($loginResponseData['message'] ?? 'Error desconocido') . "\n\n";
                }
            } else {
                echo "❌ Error en confirmación: " . ($confirmData['message'] ?? 'Error desconocido') . "\n\n";
            }
        } else {
            echo "❌ No se encontró token de confirmación en la base de datos\n\n";
        }
    } else {
        echo "❌ Error en registro: " . ($registerData['message'] ?? 'Error desconocido') . "\n\n";
    }
    
    // Limpiar datos de prueba
    echo "5. LIMPIANDO DATOS DE PRUEBA\n";
    
    $dbManager = new DatabaseManager();
    $conn = $dbManager->getNexusUsersConnection();
    
    // Eliminar tokens de confirmación
    $stmt = $conn->prepare("DELETE FROM EmailConfirmationTokens WHERE email = ?");
    $stmt->bind_param('s', $testUser['email']);
    $stmt->execute();
    
    // Eliminar usuario de prueba
    $stmt = $conn->prepare("DELETE FROM users WHERE email = ?");
    $stmt->bind_param('s', $testUser['email']);
    $stmt->execute();
    
    echo "✅ Datos de prueba eliminados\n\n";
    
} catch (Exception $e) {
    echo "❌ Error durante las pruebas: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n\n";
}

echo "=== FIN DE LAS PRUEBAS ===\n";
?>
