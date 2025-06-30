<?php
/**
 * Script para crear la tabla EmailConfirmationTokens específicamente
 */

echo "=== CREACIÓN DE TABLA EmailConfirmationTokens ===\n\n";

try {
    require_once 'config/env.php';
    require_once 'config/database_manager.php';
    
    $dbManager = new DatabaseManager();
    $conn = $dbManager->getNexusUsersConnection();
    
    echo "✅ Conectado a la base de datos\n\n";
    
    // Verificar si la tabla ya existe
    $result = $conn->query("SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'EmailConfirmationTokens'");
    $row = $result->fetch();
    
    if ($row['count'] > 0) {
        echo "⚠️ La tabla EmailConfirmationTokens ya existe\n";
        echo "¿Deseas eliminarla y recrearla? (Esto eliminará todos los tokens existentes)\n";
        echo "Escribe 'si' para continuar: ";
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        fclose($handle);
        
        if (trim(strtolower($line)) !== 'si') {
            echo "Operación cancelada\n";
            exit();
        }
        
        echo "Eliminando tabla existente...\n";
        $conn->exec("DROP TABLE EmailConfirmationTokens");
        echo "✅ Tabla eliminada\n\n";
    }
    
    echo "Creando tabla EmailConfirmationTokens...\n";
    
    $createTableSQL = "
    CREATE TABLE EmailConfirmationTokens (
        id INT IDENTITY(1,1) PRIMARY KEY,
        user_id NVARCHAR(450) NOT NULL,
        email NVARCHAR(256) NOT NULL,
        token NVARCHAR(255) NOT NULL,
        created_at DATETIME2 DEFAULT GETDATE(),
        expires_at DATETIME2 NOT NULL,
        used TINYINT DEFAULT 0,
        used_at DATETIME2 NULL
    )";
    
    if ($conn->exec($createTableSQL) !== false) {
        echo "✅ Tabla EmailConfirmationTokens creada exitosamente\n\n";
        
        echo "Creando índices...\n";
        
        // Crear índices
        $indices = [
            "CREATE UNIQUE INDEX IX_EmailConfirmationTokens_Token ON EmailConfirmationTokens(token)",
            "CREATE INDEX IX_EmailConfirmationTokens_Email ON EmailConfirmationTokens(email)",
            "CREATE INDEX IX_EmailConfirmationTokens_UserId ON EmailConfirmationTokens(user_id)",
            "CREATE INDEX IX_EmailConfirmationTokens_Expires ON EmailConfirmationTokens(expires_at)"
        ];
        
        foreach ($indices as $indexSQL) {
            try {
                $conn->exec($indexSQL);
                echo "✅ Índice creado\n";
            } catch (Exception $e) {
                echo "⚠️ Error creando índice: " . $e->getMessage() . "\n";
            }
        }
        
        echo "\n✅ Tabla e índices creados exitosamente\n\n";
        
        // Probar inserción de token de prueba
        echo "Probando inserción de token de prueba...\n";
        
        $testUserId = 'test-user-id';
        $testEmail = 'test@example.com';
        $testToken = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        $stmt = $conn->prepare("INSERT INTO EmailConfirmationTokens (user_id, email, token, expires_at) VALUES (?, ?, ?, ?)");
        
        if ($stmt->execute([$testUserId, $testEmail, $testToken, $expiresAt])) {
            echo "✅ Token de prueba insertado exitosamente\n";
            echo "Token: " . substr($testToken, 0, 20) . "...\n";
            echo "Expira: $expiresAt\n\n";
            
            // Verificar que se puede consultar
            $stmt = $conn->prepare("SELECT * FROM EmailConfirmationTokens WHERE email = ?");
            $stmt->execute([$testEmail]);
            $result = $stmt->fetch();
            
            if ($result) {
                echo "✅ Token consultado exitosamente\n";
                echo "ID: " . $result['id'] . "\n";
                echo "User ID: " . $result['user_id'] . "\n";
                echo "Email: " . $result['email'] . "\n";
                echo "Usado: " . ($result['used'] ? 'Sí' : 'No') . "\n\n";
                
                // Limpiar token de prueba
                $conn->prepare("DELETE FROM EmailConfirmationTokens WHERE email = ?")->execute([$testEmail]);
                echo "✅ Token de prueba eliminado\n";
            } else {
                echo "❌ Error consultando token\n";
            }
        } else {
            echo "❌ Error insertando token de prueba\n";
        }
        
    } else {
        echo "❌ Error creando tabla EmailConfirmationTokens\n";
        $errorInfo = $conn->errorInfo();
        echo "Error: " . implode(' - ', $errorInfo) . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . " (línea " . $e->getLine() . ")\n";
}

echo "\n=== FIN DE LA CREACIÓN ===\n";
?>
