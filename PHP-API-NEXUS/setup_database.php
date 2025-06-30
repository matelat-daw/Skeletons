<?php
/**
 * Script para verificar y crear tablas necesarias
 */

echo "=== VERIFICACIÓN Y CREACIÓN DE TABLAS ===\n\n";

try {
    require_once 'config/env.php';
    require_once 'config/database_manager.php';
    
    $dbManager = new DatabaseManager();
    $conn = $dbManager->getNexusUsersConnection();
    
    echo "✅ Conectado a la base de datos\n\n";
    
    // Listar todas las tablas disponibles
    echo "1. TABLAS EXISTENTES:\n";
    $result = $conn->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE'");
    
    $existingTables = [];
    while ($row = $result->fetch()) {
        $tableName = $row['TABLE_NAME'];
        $existingTables[] = $tableName;
        echo "- $tableName\n";
    }
    echo "\n";
    
    // Verificar si existe tabla de usuarios (podría tener otro nombre)
    $userTableExists = false;
    $userTableName = '';
    $possibleUserTables = ['users', 'Users', 'AspNetUsers', 'User'];
    
    foreach ($possibleUserTables as $tableName) {
        if (in_array($tableName, $existingTables)) {
            $userTableExists = true;
            $userTableName = $tableName;
            break;
        }
    }
    
    if ($userTableExists) {
        echo "2. ✅ Tabla de usuarios encontrada: $userTableName\n";
        
        // Verificar estructura de la tabla de usuarios
        echo "Columnas de $userTableName:\n";
        $result = $conn->query("SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$userTableName'");
        while ($row = $result->fetch()) {
            echo "- " . $row['COLUMN_NAME'] . " (" . $row['DATA_TYPE'] . ") " . ($row['IS_NULLABLE'] == 'YES' ? 'NULL' : 'NOT NULL') . "\n";
        }
        echo "\n";
        
        // Contar registros
        $result = $conn->query("SELECT COUNT(*) as count FROM $userTableName");
        $row = $result->fetch();
        echo "Total registros: " . $row['count'] . "\n\n";
    } else {
        echo "2. ❌ No se encontró tabla de usuarios\n";
        echo "Creando tabla 'users'...\n";
        
        $createUserTable = "
        CREATE TABLE users (
            id NVARCHAR(450) NOT NULL PRIMARY KEY,
            nick NVARCHAR(50) NOT NULL UNIQUE,
            name NVARCHAR(100) NOT NULL,
            surname1 NVARCHAR(100) NOT NULL,
            surname2 NVARCHAR(100) NULL,
            email NVARCHAR(256) NOT NULL UNIQUE,
            email_confirmed TINYINT DEFAULT 0,
            password_hash NVARCHAR(255) NULL,
            phone_number NVARCHAR(20) NULL,
            bday DATE NULL,
            profile_image_file NVARCHAR(500) NULL,
            about NVARCHAR(1000) NULL,
            user_location NVARCHAR(200) NULL,
            public_profile TINYINT DEFAULT 1,
            gender NVARCHAR(1) NULL,
            country NVARCHAR(2) NULL,
            city NVARCHAR(100) NULL,
            postal_code NVARCHAR(10) NULL,
            created_at DATETIME2 DEFAULT GETDATE(),
            updated_at DATETIME2 DEFAULT GETDATE()
        );";
        
        if ($conn->exec($createUserTable)) {
            echo "✅ Tabla 'users' creada exitosamente\n\n";
        } else {
            echo "❌ Error creando tabla 'users'\n\n";
        }
    }
    
    // Verificar tabla EmailConfirmationTokens
    if (in_array('EmailConfirmationTokens', $existingTables)) {
        echo "3. ✅ Tabla EmailConfirmationTokens existe\n";
        
        $result = $conn->query("SELECT COUNT(*) as count FROM EmailConfirmationTokens");
        $row = $result->fetch();
        echo "Total tokens: " . $row['count'] . "\n\n";
    } else {
        echo "3. ❌ Tabla EmailConfirmationTokens no existe\n";
        echo "Creando tabla EmailConfirmationTokens...\n";
        
        $createTokenTable = "
        CREATE TABLE EmailConfirmationTokens (
            id INT IDENTITY(1,1) PRIMARY KEY,
            user_id NVARCHAR(450) NOT NULL,
            email NVARCHAR(256) NOT NULL,
            token NVARCHAR(255) NOT NULL UNIQUE,
            created_at DATETIME2 DEFAULT GETDATE(),
            expires_at DATETIME2 NOT NULL,
            used TINYINT DEFAULT 0,
            used_at DATETIME2 NULL
        );";
        
        if ($conn->exec($createTokenTable)) {
            echo "✅ Tabla EmailConfirmationTokens creada exitosamente\n";
            
            // Crear índices por separado
            $conn->exec("CREATE INDEX IX_EmailConfirmationTokens_Token ON EmailConfirmationTokens(token)");
            $conn->exec("CREATE INDEX IX_EmailConfirmationTokens_Email ON EmailConfirmationTokens(email)");
            $conn->exec("CREATE INDEX IX_EmailConfirmationTokens_UserId ON EmailConfirmationTokens(user_id)");
            echo "✅ Índices creados exitosamente\n\n";
        } else {
            echo "❌ Error creando tabla EmailConfirmationTokens\n\n";
        }
    }
    
    echo "4. PROBANDO INSERCIÓN DE USUARIO DE PRUEBA:\n";
    
    // Crear usuario de prueba
    $testUserId = 'test-' . uniqid();
    $testEmail = 'test@nexusastralis.com';
    $testNick = 'testuser';
    
    // Verificar si ya existe
    $tableName = $userTableExists ? $userTableName : 'users';
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM $tableName WHERE email = ?");
    $stmt->execute([$testEmail]);
    $row = $stmt->fetch();
    
    if ($row['count'] == 0) {
        $stmt = $conn->prepare("INSERT INTO $tableName (id, nick, name, surname1, email, email_confirmed) VALUES (?, ?, ?, ?, ?, ?)");
        $name = 'Test';
        $surname1 = 'User';
        $emailConfirmed = 0;
        
        if ($stmt->execute([$testUserId, $testNick, $name, $surname1, $testEmail, $emailConfirmed])) {
            echo "✅ Usuario de prueba creado exitosamente\n";
            echo "ID: $testUserId\n";
            echo "Email: $testEmail\n";
            echo "Nick: $testNick\n\n";
        } else {
            echo "❌ Error creando usuario de prueba\n\n";
        }
    } else {
        echo "Usuario de prueba ya existe\n\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "=== FIN DE LA VERIFICACIÓN ===\n";
?>
