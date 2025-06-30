<?php
/**
 * Test simple del endpoint de registro usando cURL
            $stmt = $conn->prepare("SELECT token FROM EmailConfirmationTokens WHERE email = ? AND used = 0 ORDER BY created_at DESC LIMIT 1");
            $stmt->execute([$testUser['email']]);
            $result = $stmt->fetch();

$baseUrl = 'http://localhost:8080/Skeletons/PHP-API-NEXUS';

// Datos de prueba
$userData = [
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

echo "=== PRUEBA DE REGISTRO CON cURL ===\n";
echo "Email: " . $userData['email'] . "\n";
echo "Nick: " . $userData['nick'] . "\n\n";

// Configurar cURL para registro
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/api/Auth/Register');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FAILONERROR, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "=== RESPUESTA DEL REGISTRO ===\n";
echo "HTTP Code: $httpCode\n";
echo "Response: $response\n";

// Formatear JSON para mejor legibilidad
$decoded = json_decode($response, true);
if ($decoded) {
    echo "\n=== JSON FORMATEADO ===\n";
    echo json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    
    if ($decoded['success']) {
        echo "\n✅ REGISTRO EXITOSO\n";
        
        // Buscar token en la base de datos para prueba de confirmación
        echo "\n=== BUSCANDO TOKEN EN BASE DE DATOS ===\n";
        
        require_once 'config/database_manager.php';
        
        try {
            $dbManager = new DatabaseManager();
            $conn = $dbManager->getNexusUsersConnection();
            
            $stmt = $conn->prepare("SELECT token FROM EmailConfirmationTokens WHERE email = ? AND used = 0 ORDER BY created_at DESC");
            $stmt->bind_param('s', $userData['email']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result['token']) {
                $token = $result['token'];
                echo "Token encontrado: " . substr($token, 0, 20) . "...\n";
                
                // Probar confirmación de email
                echo "\n=== PROBANDO CONFIRMACIÓN DE EMAIL ===\n";
                
                $confirmUrl = $baseUrl . '/api/Auth/ConfirmEmail?token=' . urlencode($token);
                echo "URL de confirmación: $confirmUrl\n\n";
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $confirmUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FAILONERROR, false);
                
                $confirmResponse = curl_exec($ch);
                $confirmHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                echo "HTTP Code: $confirmHttpCode\n";
                echo "Response: $confirmResponse\n";
                
                $confirmDecoded = json_decode($confirmResponse, true);
                if ($confirmDecoded) {
                    echo "\n=== CONFIRMACIÓN JSON FORMATEADO ===\n";
                    echo json_encode($confirmDecoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
                    
                    if ($confirmDecoded['success']) {
                        echo "\n✅ CONFIRMACIÓN EXITOSA\n";
                        
                        // Probar login
                        echo "\n=== PROBANDO LOGIN ===\n";
                        
                        $loginData = [
                            'email' => $userData['email'],
                            'password' => $userData['password'],
                            'remember_me' => false
                        ];
                        
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $baseUrl . '/api/Auth/Login');
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
                        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                            'Content-Type: application/json'
                        ]);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_FAILONERROR, false);
                        
                        $loginResponse = curl_exec($ch);
                        $loginHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        curl_close($ch);
                        
                        echo "HTTP Code: $loginHttpCode\n";
                        echo "Response: $loginResponse\n";
                        
                        $loginDecoded = json_decode($loginResponse, true);
                        if ($loginDecoded) {
                            echo "\n=== LOGIN JSON FORMATEADO ===\n";
                            echo json_encode($loginDecoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
                            
                            if ($loginDecoded['success']) {
                                echo "\n✅ LOGIN EXITOSO - FLUJO COMPLETO FUNCIONANDO\n";
                            } else {
                                echo "\n❌ Error en login: " . $loginDecoded['message'] . "\n";
                            }
                        }
                    } else {
                        echo "\n❌ Error en confirmación: " . $confirmDecoded['message'] . "\n";
                    }
                }
            } else {
                echo "❌ No se encontró token en la base de datos\n";
            }
            
            // Limpiar datos de prueba
            echo "\n=== LIMPIANDO DATOS DE PRUEBA ===\n";
            
            $stmt = $conn->prepare("DELETE FROM EmailConfirmationTokens WHERE email = ?");
            $stmt->execute([$userData['email']]);
            
            $stmt = $conn->prepare("DELETE FROM AspNetUsers WHERE Email = ?");
            $stmt->execute([$userData['email']]);
            
            echo "✅ Datos de prueba eliminados\n";
            
        } catch (Exception $e) {
            echo "❌ Error accediendo a la base de datos: " . $e->getMessage() . "\n";
        }
    } else {
        echo "\n❌ Error en registro: " . $decoded['message'] . "\n";
    }
} else {
    echo "\n❌ Error: No se pudo decodificar la respuesta JSON\n";
}

echo "\n=== FIN DE LAS PRUEBAS ===\n";
?>
