<?php
/**
 * Test script para verificar si existe la tabla constellation_stars
 */

require_once 'config/Database.php';

try {
    $dbManager = new Database();
    $conn = $dbManager->getConnection('Nexus');
    
    echo "Verificando estructura de la base de datos Nexus...\n\n";
    
    // Verificar si existe la tabla constellation_stars
    $query = "SELECT TABLE_NAME 
              FROM INFORMATION_SCHEMA.TABLES 
              WHERE TABLE_CATALOG = 'Nexus' 
              AND TABLE_NAME IN ('constellation_stars', 'constellations', 'stars')";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Tablas existentes:\n";
    foreach ($tables as $table) {
        echo "- " . $table['TABLE_NAME'] . "\n";
    }
    
    // Verificar si existe constellation_stars
    $hasConstellationStars = false;
    foreach ($tables as $table) {
        if ($table['TABLE_NAME'] === 'constellation_stars') {
            $hasConstellationStars = true;
            break;
        }
    }
    
    if (!$hasConstellationStars) {
        echo "\n⚠️  La tabla 'constellation_stars' NO existe.\n";
        echo "Esta tabla es necesaria para relacionar constelaciones con estrellas.\n\n";
        
        // Mostrar estructura de la tabla stars para ver si tiene constellation_id
        echo "Verificando estructura de la tabla 'stars':\n";
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
        
        // Verificar si stars tiene constellation_id
        $hasConstellationId = false;
        foreach ($starsColumns as $column) {
            if (strtolower($column['COLUMN_NAME']) === 'constellation_id') {
                $hasConstellationId = true;
                break;
            }
        }
        
        if ($hasConstellationId) {
            echo "\n✅ La tabla 'stars' tiene columna 'constellation_id'.\n";
            echo "Se puede usar consulta directa sin tabla intermedia.\n";
        } else {
            echo "\n❌ La tabla 'stars' NO tiene columna 'constellation_id'.\n";
            echo "Se necesita crear la tabla 'constellation_stars' o agregar 'constellation_id' a 'stars'.\n";
        }
        
    } else {
        echo "\n✅ La tabla 'constellation_stars' existe.\n";
        
        // Mostrar estructura de constellation_stars
        echo "\nEstructura de 'constellation_stars':\n";
        $csQuery = "SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE 
                    FROM INFORMATION_SCHEMA.COLUMNS 
                    WHERE TABLE_CATALOG = 'Nexus' 
                    AND TABLE_NAME = 'constellation_stars'
                    ORDER BY ORDINAL_POSITION";
        
        $csStmt = $conn->prepare($csQuery);
        $csStmt->execute();
        $csColumns = $csStmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($csColumns as $column) {
            echo "- {$column['COLUMN_NAME']} ({$column['DATA_TYPE']}) - Nullable: {$column['IS_NULLABLE']}\n";
        }
        
        // Contar registros
        $countQuery = "SELECT COUNT(*) as total FROM constellation_stars";
        $countStmt = $conn->prepare($countQuery);
        $countStmt->execute();
        $count = $countStmt->fetch(PDO::FETCH_ASSOC);
        
        echo "\nTotal de relaciones constellation_stars: " . $count['total'] . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
