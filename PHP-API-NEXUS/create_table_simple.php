<?php
require_once 'config/env.php';
require_once 'config/database_manager.php';

$dbManager = new DatabaseManager();
$conn = $dbManager->getNexusUsersConnection();

try {
    $conn->exec("DROP TABLE IF EXISTS EmailConfirmationTokens");
    echo "Tabla eliminada (si existía)\n";
} catch(Exception $e) {
    echo "No se pudo eliminar: " . $e->getMessage() . "\n";
}

$createSQL = "CREATE TABLE EmailConfirmationTokens (
    id INT IDENTITY(1,1) PRIMARY KEY,
    user_id NVARCHAR(450) NOT NULL,
    email NVARCHAR(256) NOT NULL,
    token NVARCHAR(255) NOT NULL,
    created_at DATETIME2 DEFAULT GETDATE(),
    expires_at DATETIME2 NOT NULL,
    used TINYINT DEFAULT 0,
    used_at DATETIME2 NULL
)";

if ($conn->exec($createSQL) !== false) {
    echo "✅ Tabla creada exitosamente\n";
    
    $conn->exec("CREATE UNIQUE INDEX IX_EmailConfirmationTokens_Token ON EmailConfirmationTokens(token)");
    $conn->exec("CREATE INDEX IX_EmailConfirmationTokens_Email ON EmailConfirmationTokens(email)");
    echo "✅ Índices creados\n";
} else {
    echo "❌ Error creando tabla\n";
}
?>
