<?php
/**
 * Test script para verificar que todas las conexiones a bases de datos funcionan
 */

require_once 'config/Database.php';

try {
    $dbManager = new Database();
    
    echo "=== TEST DE CONEXIONES A BASES DE DATOS ===\n\n";
    
    // Test conexión a NexusUsers
    echo "1. Probando conexión a NexusUsers...\n";
    $nexusUsersConn = $dbManager->getConnection('NexusUsers');
    $query = "SELECT COUNT(*) as total FROM AspNetUsers";
    $stmt = $nexusUsersConn->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   ✅ Conexión exitosa - Usuarios registrados: " . $result['total'] . "\n\n";
    
    // Test conexión a nexus_stars
    echo "2. Probando conexión a nexus_stars...\n";
    $nexusStarsConn = $dbManager->getConnection('nexus_stars');
    
    // Verificar constelaciones
    $query = "SELECT COUNT(*) as total FROM constellations";
    $stmt = $nexusStarsConn->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   ✅ Constelaciones disponibles: " . $result['total'] . "\n";
    
    // Verificar estrellas
    $query = "SELECT COUNT(*) as total FROM stars";
    $stmt = $nexusStarsConn->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   ✅ Estrellas disponibles: " . $result['total'] . "\n";
    
    // Verificar relaciones constellation_stars
    $query = "SELECT COUNT(*) as total FROM constellation_stars";
    $stmt = $nexusStarsConn->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   ✅ Relaciones constellation-stars: " . $result['total'] . "\n\n";
    
    // Test de modelos
    echo "3. Probando modelos...\n";
    
    // Test modelo Constellation
    require_once 'models/Constellation.php';
    $constellationModel = new Constellation($nexusStarsConn);
    $constellations = $constellationModel->getAll();
    echo "   ✅ Modelo Constellation - Registros obtenidos: " . count($constellations) . "\n";
    
    // Test modelo Star
    require_once 'models/Star.php';
    $starModel = new Star($nexusStarsConn);
    $stars = $starModel->getAll();
    echo "   ✅ Modelo Star - Registros obtenidos: " . count($stars) . "\n";
    
    // Test modelo Comments
    require_once 'models/Comments.php';
    $commentsModel = new Comments($nexusUsersConn);
    $comments = $commentsModel->getRecentComments(5);
    echo "   ✅ Modelo Comments - Comentarios recientes: " . count($comments) . "\n";
    
    // Test modelo Favorites
    require_once 'models/Favorites.php';
    $favoritesModel = new Favorites($nexusUsersConn);
    echo "   ✅ Modelo Favorites - Inicializado correctamente\n\n";
    
    // Test específico - obtener estrellas de una constelación
    echo "4. Probando obtención de estrellas de constelación...\n";
    $constellationId = 1; // Andromeda
    $stars = $constellationModel->getStarsByConstellationId($constellationId);
    if ($stars !== false) {
        echo "   ✅ Estrellas de constelación ID {$constellationId}: " . count($stars) . "\n";
    } else {
        echo "   ⚠️  No se encontraron estrellas para la constelación ID {$constellationId}\n";
    }
    
    echo "\n=== TODOS LOS TESTS COMPLETADOS EXITOSAMENTE ===\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
}
?>
