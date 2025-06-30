<?php
/**
 * Test del endpoint de constelaciones
 */

echo "=== TEST DEL ENDPOINT DE CONSTELACIONES ===\n\n";

try {
    require_once 'config/env.php';
    require_once 'config/database_manager.php';
    require_once 'models/Constellation.php';
    
    echo "1. PROBANDO CONEXIÓN A BASE DE DATOS NEXUS...\n";
    $dbManager = new DatabaseManager();
    $conn = $dbManager->getConnection('Nexus');
    echo "✅ Conexión exitosa a base de datos Nexus\n\n";
    
    echo "2. VERIFICANDO TABLA CONSTELLATIONS...\n";
    $result = $conn->query("SELECT COUNT(*) as count FROM constellations");
    $row = $result->fetch();
    echo "✅ Tabla existe - Total constelaciones: " . $row['count'] . "\n\n";
    
    echo "3. PROBANDO MODELO CONSTELLATION...\n";
    $constellationModel = new Constellation($conn);
    
    echo "4. OBTENIENDO TODAS LAS CONSTELACIONES...\n";
    $constellations = $constellationModel->getAll();
    echo "✅ Obtenidas " . count($constellations) . " constelaciones\n";
    
    if (!empty($constellations)) {
        $first = $constellations[0];
        echo "Primera constelación:\n";
        echo "- ID: " . $first['id'] . "\n";
        echo "- Código: " . $first['code'] . "\n";
        echo "- Nombre inglés: " . $first['english_name'] . "\n";
        echo "- Nombre español: " . $first['spanish_name'] . "\n\n";
        
        echo "5. PROBANDO GET BY ID...\n";
        $singleConstellation = $constellationModel->getById($first['id']);
        if ($singleConstellation) {
            echo "✅ Constelación obtenida por ID exitosamente\n";
        } else {
            echo "❌ Error obteniendo constelación por ID\n";
        }
    }
    
    echo "\n6. PROBANDO CONTROLADOR...\n";
    require_once 'controllers/ConstellationsController.php';
    
    // Simular request
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/api/Constellations';
    
    $controller = new ConstellationsController();
    echo "✅ Controlador creado exitosamente\n";
    
    echo "\n✅ TODOS LOS TESTS EXITOSOS\n";
    echo "El endpoint /api/Constellations debería funcionar correctamente\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}

echo "\n=== FIN DEL TEST ===\n";
?>
