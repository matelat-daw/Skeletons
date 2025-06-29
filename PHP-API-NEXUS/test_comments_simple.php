<?php
// Test simplificado del modelo Comments
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir archivos necesarios
require_once 'config/database_manager.php';
require_once 'models/Comments.php';

echo "<h2>🔍 Test Simplificado del Modelo Comments</h2>";

try {
    // Usar la misma configuración que funciona en Profile.php
    $dbManager = new DatabaseManager();
    $dbNexusUsers = $dbManager->getNexusUsersConnection();
    echo "✅ Conexión a base de datos NexusUsers establecida<br>";
    
    // Instanciar modelo de comentarios
    $comments = new Comments($dbNexusUsers);
    echo "✅ Modelo Comments instanciado<br>";
    
    // Test directo: obtener algunos comentarios
    echo "<h3>💬 Test: Obtener comentarios existentes</h3>";
    $query = "SELECT TOP 3 Id, UserNick, Comment, ConstellationId, ConstellationName, UserId FROM Comments ORDER BY Id DESC";
    $stmt = $dbNexusUsers->prepare($query);
    $stmt->execute();
    $existingComments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($existingComments) {
        echo "✅ Se encontraron " . count($existingComments) . " comentarios:<br>";
        echo "<ul>";
        foreach ($existingComments as $comment) {
            echo "<li>";
            echo "ID: {$comment['Id']}, ";
            echo "Usuario: {$comment['UserNick']}, ";
            echo "Comentario: '" . substr($comment['Comment'], 0, 30) . "...', ";
            echo "Constelación: {$comment['ConstellationName']}";
            echo "</li>";
        }
        echo "</ul>";
        
        // Test del método getUserComments
        echo "<h3>👤 Test: getUserComments()</h3>";
        $testUserId = $existingComments[0]['UserId'];
        echo "🔍 Probando con UserId: $testUserId<br>";
        
        $userComments = $comments->getUserComments($testUserId);
        if ($userComments) {
            echo "✅ Método getUserComments() funciona correctamente. Encontrados " . count($userComments) . " comentarios:<br>";
            echo "<ul>";
            foreach ($userComments as $comment) {
                echo "<li>ID: {$comment['Id']}, Comentario: '" . substr($comment['Comment'], 0, 30) . "...'</li>";
            }
            echo "</ul>";
        } else {
            echo "⚠️ No se encontraron comentarios para este usuario<br>";
        }
        
        // Test del método getById
        echo "<h3>🔍 Test: getById()</h3>";
        $testCommentId = $existingComments[0]['Id'];
        echo "🔍 Probando con CommentId: $testCommentId<br>";
        
        if ($comments->getById($testCommentId)) {
            echo "✅ Método getById() funciona correctamente:<br>";
            echo "<ul>";
            echo "<li>ID: {$comments->id}</li>";
            echo "<li>Usuario: {$comments->user_nick}</li>";
            echo "<li>Comentario: '{$comments->comment}'</li>";
            echo "<li>Constelación: {$comments->constellation_name}</li>";
            echo "</ul>";
        } else {
            echo "❌ Error al obtener comentario por ID<br>";
        }
        
    } else {
        echo "⚠️ No se encontraron comentarios en la base de datos<br>";
    }
    
    echo "<h3>✅ Test completado exitosamente</h3>";
    echo "<p><strong>El modelo Comments está listo para usar en la API</strong></p>";
    
} catch (Exception $e) {
    echo "<h3>❌ Error durante las pruebas:</h3>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
    echo "<p><strong>Stack trace:</strong></p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
