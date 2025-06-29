<?php
// Script para probar las conexiones a ambas bases de datos
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== PRUEBA DE CONEXIONES DUALES ===\n";

try {
    echo "1. Cargando DatabaseManager...\n";
    include_once 'config/database_manager.php';
    
    $dbManager = new DatabaseManager();
    echo "✓ DatabaseManager instanciado\n";
    
    echo "\n2. Probando conexión a NexusUsers...\n";
    $dbNexusUsers = $dbManager->getNexusUsersConnection();
    echo "✓ Conexión a NexusUsers exitosa\n";
    
    echo "\n3. Probando conexión a nexus_stars...\n";
    $dbNexusStars = $dbManager->getNexusStarsConnection();
    echo "✓ Conexión a nexus_stars exitosa\n";
    
    echo "\n4. Probando consulta a NexusUsers...\n";
    $userQuery = "SELECT COUNT(*) as total FROM AspNetUsers";
    $userStmt = $dbNexusUsers->prepare($userQuery);
    $userStmt->execute();
    $userResult = $userStmt->fetch(PDO::FETCH_ASSOC);
    echo "✓ Total usuarios en NexusUsers: " . $userResult['total'] . "\n";
    
    echo "\n5. Probando consulta a nexus_stars...\n";
    try {
        $constellationQuery = "SELECT COUNT(*) as total FROM constellations";
        $constellationStmt = $dbNexusStars->prepare($constellationQuery);
        $constellationStmt->execute();
        $constellationResult = $constellationStmt->fetch(PDO::FETCH_ASSOC);
        echo "✓ Total constelaciones en nexus_stars: " . $constellationResult['total'] . "\n";
    } catch (Exception $e) {
        echo "⚠ Tabla constellations no encontrada o vacía: " . $e->getMessage() . "\n";
    }
    
    try {
        $starsQuery = "SELECT COUNT(*) as total FROM stars";
        $starsStmt = $dbNexusStars->prepare($starsQuery);
        $starsStmt->execute();
        $starsResult = $starsStmt->fetch(PDO::FETCH_ASSOC);
        echo "✓ Total estrellas en nexus_stars: " . $starsResult['total'] . "\n";
    } catch (Exception $e) {
        echo "⚠ Tabla stars no encontrada o vacía: " . $e->getMessage() . "\n";
    }
    
    echo "\n6. Probando modelos...\n";
    include_once 'models/Constellation.php';
    include_once 'models/Star.php';
    
    $constellation = new Constellation($dbNexusStars);
    $star = new Star($dbNexusStars);
    echo "✓ Modelos Constellation y Star instanciados\n";
    
    echo "\n=== TODAS LAS CONEXIONES FUNCIONAN CORRECTAMENTE ===\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR ENCONTRADO:\n";
    echo "Mensaje: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
