<?php
/**
 * Test script para verificar todas las tablas en la base de datos Nexus
 */

require_once 'config/Database.php';

try {
    $dbManager = new Database();
    $conn = $dbManager->getConnection('Nexus');
    
    echo "Listando todas las tablas en la base de datos Nexus...\n\n";
    
    // Obtener todas las tablas
    $query = "SELECT TABLE_NAME 
              FROM INFORMATION_SCHEMA.TABLES 
              WHERE TABLE_CATALOG = 'Nexus' 
              AND TABLE_TYPE = 'BASE TABLE'
              ORDER BY TABLE_NAME";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Tablas encontradas:\n";
    foreach ($tables as $table) {
        echo "- " . $table['TABLE_NAME'] . "\n";
    }
    
    // Si hay tabla stars, mostrar algunos registros
    if (in_array('stars', array_column($tables, 'TABLE_NAME'))) {
        echo "\n--- Estructura de la tabla 'stars' ---\n";
        $starsQuery = "SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE 
                       FROM INFORMATION_SCHEMA.COLUMNS 
                       WHERE TABLE_CATALOG = 'Nexus' 
                       AND TABLE_NAME = 'stars'
                       ORDER BY ORDINAL_POSITION";
        
        $starsStmt = $conn->prepare($starsQuery);
        $starsStmt->execute();
        $starsColumns = $starsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($starsColumns as $column) {
            echo "- {$column['COLUMN_NAME']} ({$column['DATA_TYPE']}) - Nullable: {$column['IS_NULLABLE']}\n";
        }
        
        // Mostrar algunos registros de muestra
        echo "\n--- Primeros 5 registros de 'stars' ---\n";
        $sampleQuery = "SELECT TOP 5 * FROM stars";
        $sampleStmt = $conn->prepare($sampleQuery);
        $sampleStmt->execute();
        $sampleStars = $sampleStmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($sampleStars as $star) {
            echo "ID: {$star['id']}, Nombre: " . ($star['proper'] ?? $star['name'] ?? 'N/A') . "\n";
        }
    }
    
    // Si hay tabla constellations, mostrar algunos registros
    if (in_array('constellations', array_column($tables, 'TABLE_NAME'))) {
        echo "\n--- Primeros 5 registros de 'constellations' ---\n";
        $constQuery = "SELECT TOP 5 id, code, english_name FROM constellations ORDER BY english_name";
        $constStmt = $conn->prepare($constQuery);
        $constStmt->execute();
        $sampleConstellations = $constStmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($sampleConstellations as $const) {
            echo "ID: {$const['id']}, Code: {$const['code']}, Name: {$const['english_name']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
