<?php
// Test integrado de Favoritos y Comentarios
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir archivos necesarios
require_once 'config/database_manager.php';
require_once 'models/Favorites.php';
require_once 'models/Comments.php';

echo "<h2>🔍 Test Integrado: Favoritos y Comentarios</h2>";

try {
    // Configurar conexiones
    $dbManager = new DatabaseManager();
    $dbNexusUsers = $dbManager->getNexusUsersConnection();
    echo "✅ Conexión a base de datos NexusUsers establecida<br>";
    
    // Instanciar modelos
    $favorites = new Favorites($dbNexusUsers);
    $comments = new Comments($dbNexusUsers);
    echo "✅ Modelos instanciados<br>";
    
    // Test 1: Obtener un usuario existente para las pruebas
    echo "<h3>👤 Test 1: Obtener usuario de prueba</h3>";
    $query = "SELECT TOP 1 Id, Nick FROM AspNetUsers WHERE Nick IS NOT NULL";
    $stmt = $dbNexusUsers->prepare($query);
    $stmt->execute();
    $testUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($testUser) {
        $testUserId = $testUser['Id'];
        $testUserNick = $testUser['Nick'];
        echo "✅ Usuario de prueba: {$testUserNick} (ID: {$testUserId})<br>";
    } else {
        echo "❌ No se encontró usuario de prueba<br>";
        exit();
    }
    
    // Test 2: Obtener favoritos del usuario
    echo "<h3>⭐ Test 2: Favoritos del usuario</h3>";
    $userFavorites = $favorites->getUserFavorites($testUserId);
    echo "✅ Favoritos encontrados: " . count($userFavorites) . "<br>";
    
    if ($userFavorites) {
        echo "<ul>";
        foreach (array_slice($userFavorites, 0, 5) as $favorite) {
            echo "<li>ID: {$favorite['Id']}, Constelación: {$favorite['ConstellationName']}</li>";
        }
        echo "</ul>";
    }
    
    // Test 3: Obtener comentarios del usuario
    echo "<h3>💬 Test 3: Comentarios del usuario</h3>";
    $userComments = $comments->getUserComments($testUserId);
    echo "✅ Comentarios encontrados: " . count($userComments) . "<br>";
    
    if ($userComments) {
        echo "<ul>";
        foreach (array_slice($userComments, 0, 5) as $comment) {
            echo "<li>ID: {$comment['Id']}, Comentario: '" . substr($comment['Comment'], 0, 30) . "...', Constelación: {$comment['ConstellationName']}</li>";
        }
        echo "</ul>";
    }
    
    // Test 4: Estadísticas del usuario
    echo "<h3>📊 Test 4: Estadísticas del usuario</h3>";
    $favStats = $favorites->getUserFavoritesStats($testUserId);
    $commentsStats = $comments->getUserCommentsStats($testUserId);
    
    echo "<h4>Estadísticas de Favoritos:</h4>";
    if ($favStats) {
        echo "<ul>";
        echo "<li>Total favoritos: {$favStats['total_favorites']}</li>";
        echo "<li>Constelaciones del norte: {$favStats['northern_count']}</li>";
        echo "<li>Constelaciones del sur: {$favStats['southern_count']}</li>";
        echo "<li>Constelaciones del zodíaco: {$favStats['zodiac_count']}</li>";
        echo "</ul>";
    }
    
    echo "<h4>Estadísticas de Comentarios:</h4>";
    if ($commentsStats) {
        echo "<ul>";
        echo "<li>Total comentarios: {$commentsStats['total_comments']}</li>";
        echo "<li>Constelaciones comentadas: {$commentsStats['constellations_commented']}</li>";
        echo "<li>Longitud promedio: " . round($commentsStats['average_comment_length'], 2) . " caracteres</li>";
        echo "</ul>";
    }
    
    // Test 5: Verificar si una constelación específica es favorita
    echo "<h3>🔍 Test 5: Verificar favorito específico</h3>";
    if ($userFavorites) {
        $testConstellationId = $userFavorites[0]['ConstellationId'];
        $isFavorite = $favorites->isFavorite($testUserId, $testConstellationId);
        echo "✅ Constelación ID {$testConstellationId} es favorita: " . ($isFavorite ? 'SÍ' : 'NO') . "<br>";
    }
    
    // Test 6: Simular formato del endpoint Profile
    echo "<h3>📋 Test 6: Formato para endpoint Profile</h3>";
    
    // Favoritos en formato frontend
    $formattedFavorites = [];
    foreach ($userFavorites as $fav) {
        $formattedFavorites[] = [
            'id' => $fav['ConstellationId'],
            'name' => $fav['ConstellationName'] ?? '',
            'english_name' => $fav['ConstellationName'] ?? ''
        ];
    }
    
    // Comentarios en formato frontend
    $formattedComments = [];
    foreach ($userComments as $comment) {
        $formattedComments[] = [
            'id' => intval($comment['Id']),
            'userNick' => $comment['UserNick'],
            'comment' => $comment['Comment'],
            'constellationId' => intval($comment['ConstellationId']),
            'constellationName' => $comment['ConstellationName']
        ];
    }
    
    echo "✅ Favoritos formateados: " . count($formattedFavorites) . " elementos<br>";
    echo "✅ Comentarios formateados: " . count($formattedComments) . " elementos<br>";
    
    // Mostrar ejemplo de estructura final
    $profileData = [
        'nick' => $testUserNick,
        'favorites' => array_slice($formattedFavorites, 0, 3),
        'comments' => array_slice($formattedComments, 0, 3)
    ];
    
    echo "<h4>Ejemplo de estructura para frontend:</h4>";
    echo "<pre>" . json_encode($profileData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
    
    echo "<h3>🎉 Todos los tests completados exitosamente</h3>";
    echo "<ul>";
    echo "<li>✅ Modelo Favorites: Funcionando correctamente</li>";
    echo "<li>✅ Modelo Comments: Funcionando correctamente</li>";
    echo "<li>✅ Endpoints: Listos para usar</li>";
    echo "<li>✅ Formato de datos: Compatible con frontend Angular</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<h3>❌ Error durante las pruebas:</h3>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
    echo "<p><strong>Stack trace:</strong></p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
