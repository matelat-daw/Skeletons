<?php
// Script para probar el endpoint de Favorites
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== PRUEBA DEL ENDPOINT FAVORITES ===\n\n";

// Función para hacer peticiones cURL
function makeRequest($url, $method = 'GET', $data = null, $headers = []) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $defaultHeaders = [
        'Content-Type: application/json',
        'Accept: application/json'
    ];
    
    $allHeaders = array_merge($defaultHeaders, $headers);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $allHeaders);
    
    if ($data && ($method === 'POST' || $method === 'PUT')) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    
    // Simular cookie JWT (en producción esto vendría del navegador)
    curl_setopt($ch, CURLOPT_COOKIE, 'jwt_token=fake_token_for_testing');
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'response' => $response,
        'http_code' => $httpCode,
        'error' => $error
    ];
}

// Función para probar el modelo Favorites directamente
function testFavoritesModel() {
    echo "1. PROBANDO MODELO FAVORITES DIRECTAMENTE\n";
    
    try {
        include_once 'config/database_manager.php';
        include_once 'models/Favorites.php';
        
        $dbManager = new DatabaseManager();
        $dbNexusUsers = $dbManager->getNexusUsersConnection();
        
        $favorites = new Favorites($dbNexusUsers);
        
        // Usar un userId de prueba (debería existir en la tabla AspNetUsers)
        $testUserId = "0826617d-c68b-4d32-be75-bc7f703b98e4"; // Del debug anterior
        $testConstellationId = 1; // Assuming constellation ID 1 exists
        
        echo "Probando con userId: $testUserId\n";
        echo "Probando con constellationId: $testConstellationId\n\n";
        
        // Verificar si es favorito
        $isFavorite = $favorites->isFavorite($testUserId, $testConstellationId);
        echo "¿Es favorito? " . ($isFavorite ? "Sí" : "No") . "\n";
        
        // Obtener favoritos del usuario
        $userFavorites = $favorites->getUserFavorites($testUserId);
        echo "Favoritos encontrados: " . count($userFavorites) . "\n";
        
        if (!empty($userFavorites)) {
            echo "Primeros favoritos:\n";
            foreach (array_slice($userFavorites, 0, 3) as $fav) {
                echo "  - ID: {$fav['ConstellationId']}, Nombre: {$fav['ConstellationName']}\n";
            }
        }
        
        // Obtener estadísticas
        $stats = $favorites->getUserFavoritesStats($testUserId);
        echo "\nEstadísticas:\n";
        echo "  Total favoritos: {$stats['total_favorites']}\n";
        echo "  Hemisferio norte: {$stats['northern_count']}\n";
        echo "  Hemisferio sur: {$stats['southern_count']}\n";
        echo "  Zodíaco: {$stats['zodiac_count']}\n";
        
    } catch (Exception $e) {
        echo "Error probando modelo: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

// Función para verificar la estructura de la tabla Favorites
function checkFavoritesTable() {
    echo "2. VERIFICANDO ESTRUCTURA DE LA TABLA FAVORITES\n";
    
    try {
        include_once 'config/database_manager.php';
        
        $dbManager = new DatabaseManager();
        $dbNexusUsers = $dbManager->getNexusUsersConnection();
        
        // Verificar si la tabla existe
        $query = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES 
                  WHERE TABLE_NAME = 'Favorites'";
        $stmt = $dbNexusUsers->prepare($query);
        $stmt->execute();
        $tableExists = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($tableExists) {
            echo "✓ Tabla Favorites existe\n";
            
            // Obtener estructura
            $query = "SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE 
                      FROM INFORMATION_SCHEMA.COLUMNS 
                      WHERE TABLE_NAME = 'Favorites' 
                      ORDER BY ORDINAL_POSITION";
            $stmt = $dbNexusUsers->prepare($query);
            $stmt->execute();
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "Columnas en tabla Favorites:\n";
            foreach ($columns as $column) {
                echo "  {$column['COLUMN_NAME']}: {$column['DATA_TYPE']} - Nullable: {$column['IS_NULLABLE']}\n";
            }
            
            // Contar registros
            $countQuery = "SELECT COUNT(*) as total FROM Favorites";
            $countStmt = $dbNexusUsers->prepare($countQuery);
            $countStmt->execute();
            $count = $countStmt->fetch(PDO::FETCH_ASSOC);
            echo "\nTotal de registros en Favorites: {$count['total']}\n";
            
        } else {
            echo "✗ Tabla Favorites NO existe\n";
            echo "Es necesario crear la tabla Favorites en la base de datos NexusUsers\n";
        }
        
    } catch (Exception $e) {
        echo "Error verificando tabla: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

// Ejecutar pruebas
checkFavoritesTable();
testFavoritesModel();

echo "3. PROBANDO ENDPOINTS (simulado)\n";
echo "Nota: Los endpoints requieren autenticación JWT real\n";
echo "URL base: http://localhost:8080/Skeletons/PHP-API-NEXUS/api/Account/Favorites\n";
echo "Métodos disponibles:\n";
echo "  GET /api/Account/Favorites - Obtener todos los favoritos\n";
echo "  GET /api/Account/Favorites/{id} - Verificar si es favorito\n";
echo "  POST /api/Account/Favorites/{id} - Agregar a favoritos\n";
echo "  DELETE /api/Account/Favorites/{id} - Eliminar de favoritos\n";

echo "\n=== FIN DE LA PRUEBA ===\n";
?>
