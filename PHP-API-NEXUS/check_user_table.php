<?php
require_once 'config/Database.php';

echo "Verificando estructura de la tabla AspNetUsers...\n";
echo str_repeat("=", 50) . "\n";

try {
    $db = new Database();
    $conn = $db->getConnection('NexusUsers');
    
    // SQL Server syntax para obtener información de columnas
    $stmt = $conn->query("
        SELECT 
            COLUMN_NAME as Field,
            DATA_TYPE + 
            CASE 
                WHEN CHARACTER_MAXIMUM_LENGTH IS NOT NULL 
                THEN '(' + CAST(CHARACTER_MAXIMUM_LENGTH AS VARCHAR) + ')' 
                ELSE '' 
            END as Type,
            CASE WHEN IS_NULLABLE = 'YES' THEN 'YES' ELSE 'NO' END as [Null],
            COLUMN_DEFAULT as [Default]
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_NAME = 'AspNetUsers'
        ORDER BY ORDINAL_POSITION
    ");
    
    echo sprintf("%-25s %-25s %-10s %-15s\n", "Campo", "Tipo", "Null", "Default");
    echo str_repeat("-", 75) . "\n";
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo sprintf("%-25s %-25s %-10s %-15s\n", 
            $row['Field'], 
            $row['Type'], 
            $row['Null'], 
            $row['Default'] ?? 'NULL'
        );
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "Verificación completada.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
