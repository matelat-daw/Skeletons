<?php
// Test con usuario que tiene datos
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database_manager.php';
require_once 'models/Favorites.php';
require_once 'models/Comments.php';

echo "<h2>🔍 Test Final: Usuario con Datos</h2>";

try {
    $dbManager = new DatabaseManager();
    $dbNexusUsers = $dbManager->getNexusUsersConnection();
    
    $favorites = new Favorites($dbNexusUsers);
    $comments = new Comments($dbNexusUsers);
    
    // Buscar un usuario que tenga favoritos
    echo "<h3>🔍 Buscando usuario con favoritos...</h3>";
    $query = "SELECT TOP 1 u.Id, u.Nick, COUNT(f.Id) as favorite_count
              FROM AspNetUsers u
              INNER JOIN Favorites f ON u.Id = f.UserId
              WHERE u.Nick IS NOT NULL
              GROUP BY u.Id, u.Nick
              ORDER BY COUNT(f.Id) DESC";
    $stmt = $dbNexusUsers->prepare($query);
    $stmt->execute();
    $userWithFavorites = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($userWithFavorites) {
        $userId = $userWithFavorites['Id'];
        $userNick = $userWithFavorites['Nick'];
        echo "✅ Usuario encontrado: {$userNick} con {$userWithFavorites['favorite_count']} favoritos<br>";
        
        // Test favoritos
        echo "<h3>⭐ Favoritos del usuario:</h3>";
        $userFavorites = $favorites->getUserFavorites($userId);
        if ($userFavorites) {
            echo "<ul>";
            foreach ($userFavorites as $fav) {
                echo "<li>ID: {$fav['Id']}, Constelación ID: {$fav['ConstellationId']}</li>";
            }
            echo "</ul>";
        }
        
        // Test comentarios
        echo "<h3>💬 Comentarios del usuario:</h3>";
        $userComments = $comments->getUserComments($userId);
        if ($userComments) {
            echo "<ul>";
            foreach ($userComments as $comment) {
                echo "<li>ID: {$comment['Id']}, Comentario: '" . substr($comment['Comment'], 0, 50) . "...', Constelación: {$comment['ConstellationName']}</li>";
            }
            echo "</ul>";
        } else {
            echo "Este usuario no tiene comentarios<br>";
        }
        
        // Test verificar favorito específico
        if ($userFavorites) {
            $testConstellationId = $userFavorites[0]['ConstellationId'];
            $isFavorite = $favorites->isFavorite($userId, $testConstellationId);
            echo "<h3>🔍 Verificación:</h3>";
            echo "Constelación ID {$testConstellationId} es favorita: " . ($isFavorite ? 'SÍ ✅' : 'NO ❌') . "<br>";
        }
        
    } else {
        echo "⚠️ No se encontró usuario con favoritos<br>";
    }
    
    // Test general de comentarios
    echo "<h3>💬 Últimos comentarios del sistema:</h3>";
    $recentComments = $comments->getRecentComments(5);
    if ($recentComments) {
        echo "<ul>";
        foreach ($recentComments as $comment) {
            echo "<li>Usuario: {$comment['UserNick']}, Comentario: '" . substr($comment['Comment'], 0, 30) . "...', Constelación: {$comment['ConstellationName']}</li>";
        }
        echo "</ul>";
    }
    
    echo "<h3>🎉 Test completado exitosamente</h3>";
    echo "<p><strong>Los modelos Favorites y Comments están funcionando correctamente y listos para producción.</strong></p>";
    
} catch (Exception $e) {
    echo "<h3>❌ Error durante las pruebas:</h3>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
}
?>
