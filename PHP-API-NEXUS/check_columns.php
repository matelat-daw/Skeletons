<?php
// Script para verificar todas las columnas de la tabla AspNetUsers
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== VERIFICANDO TODAS LAS COLUMNAS DE AspNetUsers ===\n\n";

try {
    include_once 'config/database.php';
    
    $database = new Database();
    $db = $database->getConnection();
    
    // Obtener todas las columnas de la tabla
    $query = "SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, CHARACTER_MAXIMUM_LENGTH, COLUMN_DEFAULT
              FROM INFORMATION_SCHEMA.COLUMNS 
              WHERE TABLE_NAME = 'AspNetUsers' 
              ORDER BY ORDINAL_POSITION";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($columns)) {
        echo "Todas las columnas en AspNetUsers:\n";
        foreach ($columns as $column) {
            $length = $column['CHARACTER_MAXIMUM_LENGTH'] ? "({$column['CHARACTER_MAXIMUM_LENGTH']})" : "";
            $default = $column['COLUMN_DEFAULT'] ? " DEFAULT: {$column['COLUMN_DEFAULT']}" : "";
            echo "  {$column['COLUMN_NAME']}: {$column['DATA_TYPE']}{$length} - Nullable: {$column['IS_NULLABLE']}{$default}\n";
        }
    } else {
        echo "No se pudieron obtener las columnas\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== FIN DE LA VERIFICACIÓN ===\n";
?>
