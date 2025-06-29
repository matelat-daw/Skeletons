<?php
// Test del modelo Comments
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir archivos necesarios
require_once 'config/database_manager.php';
require_once 'models/Comments.php';

echo "<h2>🔍 Test del Modelo Comments</h2>";

try {
    // Obtener conexión a la base de datos de usuarios
    $databaseManager = new DatabaseManager();
    $database = $databaseManager->getConnection('users');
    echo "✅ Conexión a base de datos establecida<br>";
    
    // Instanciar modelo de comentarios
    $comments = new Comments($database);
    echo "✅ Modelo Comments instanciado<br>";
    
    // Test 1: Verificar estructura de la tabla Comments
    echo "<h3>📋 Test 1: Verificar estructura de tabla Comments</h3>";
    $query = "SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE 
              FROM INFORMATION_SCHEMA.COLUMNS 
              WHERE TABLE_NAME = 'Comments' 
              ORDER BY ORDINAL_POSITION";
    $stmt = $database->prepare($query);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($columns) {
        echo "✅ Tabla Comments encontrada con las siguientes columnas:<br>";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Columna</th><th>Tipo</th><th>Nullable</th></tr>";
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>" . $column['COLUMN_NAME'] . "</td>";
            echo "<td>" . $column['DATA_TYPE'] . "</td>";
            echo "<td>" . $column['IS_NULLABLE'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "❌ No se encontró la tabla Comments<br>";
    }
    
    // Test 2: Obtener comentarios existentes
    echo "<h3>💬 Test 2: Obtener comentarios existentes</h3>";
    $query = "SELECT TOP 5 Id, UserNick, Comment, ConstellationId, ConstellationName FROM Comments ORDER BY Id DESC";
    $stmt = $database->prepare($query);
    $stmt->execute();
    $existingComments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($existingComments) {
        echo "✅ Se encontraron " . count($existingComments) . " comentarios:<br>";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>Usuario</th><th>Comentario</th><th>Constelación ID</th><th>Constelación</th></tr>";
        foreach ($existingComments as $comment) {
            echo "<tr>";
            echo "<td>" . $comment['Id'] . "</td>";
            echo "<td>" . $comment['UserNick'] . "</td>";
            echo "<td>" . substr($comment['Comment'], 0, 50) . "...</td>";
            echo "<td>" . $comment['ConstellationId'] . "</td>";
            echo "<td>" . $comment['ConstellationName'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "⚠️ No se encontraron comentarios en la base de datos<br>";
    }
    
    // Test 3: Probar método getUserComments con un usuario existente
    echo "<h3>👤 Test 3: Obtener comentarios de un usuario específico</h3>";
    if ($existingComments) {
        $testUserId = $existingComments[0]['UserId'] ?? null;
        if ($testUserId) {
            echo "🔍 Probando con UserId: $testUserId<br>";
            $userComments = $comments->getUserComments($testUserId);
            
            if ($userComments) {
                echo "✅ Se encontraron " . count($userComments) . " comentarios para este usuario:<br>";
                foreach ($userComments as $comment) {
                    echo "- ID: {$comment['Id']}, Comentario: " . substr($comment['Comment'], 0, 30) . "...<br>";
                }
            } else {
                echo "⚠️ No se encontraron comentarios para este usuario<br>";
            }
        } else {
            echo "⚠️ No se pudo obtener un UserId para probar<br>";
        }
    }
    
    // Test 4: Probar método getConstellationComments
    echo "<h3>🌟 Test 4: Obtener comentarios de una constelación específica</h3>";
    if ($existingComments) {
        $testConstellationId = $existingComments[0]['ConstellationId'] ?? null;
        if ($testConstellationId) {
            echo "🔍 Probando con ConstellationId: $testConstellationId<br>";
            $constellationComments = $comments->getConstellationComments($testConstellationId, 5);
            
            if ($constellationComments) {
                echo "✅ Se encontraron " . count($constellationComments) . " comentarios para esta constelación:<br>";
                foreach ($constellationComments as $comment) {
                    echo "- Usuario: {$comment['UserNick']}, Comentario: " . substr($comment['Comment'], 0, 30) . "...<br>";
                }
            } else {
                echo "⚠️ No se encontraron comentarios para esta constelación<br>";
            }
        }
    }
    
    // Test 5: Probar método getById
    echo "<h3>🔍 Test 5: Obtener comentario por ID</h3>";
    if ($existingComments) {
        $testCommentId = $existingComments[0]['Id'] ?? null;
        if ($testCommentId) {
            echo "🔍 Probando con CommentId: $testCommentId<br>";
            if ($comments->getById($testCommentId)) {
                echo "✅ Comentario encontrado:<br>";
                echo "- ID: {$comments->id}<br>";
                echo "- Usuario: {$comments->user_nick}<br>";
                echo "- Comentario: {$comments->comment}<br>";
                echo "- Constelación: {$comments->constellation_name}<br>";
            } else {
                echo "❌ No se pudo obtener el comentario por ID<br>";
            }
        }
    }
    
    // Test 6: Estadísticas generales
    echo "<h3>📊 Test 6: Estadísticas de comentarios</h3>";
    $query = "SELECT 
                COUNT(*) as total_comments,
                COUNT(DISTINCT UserId) as unique_users,
                COUNT(DISTINCT ConstellationId) as unique_constellations
              FROM Comments";
    $stmt = $database->prepare($query);
    $stmt->execute();
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($stats) {
        echo "✅ Estadísticas generales:<br>";
        echo "- Total de comentarios: {$stats['total_comments']}<br>";
        echo "- Usuarios únicos: {$stats['unique_users']}<br>";
        echo "- Constelaciones únicas: {$stats['unique_constellations']}<br>";
    }
    
    echo "<h3>✅ Todos los tests completados exitosamente</h3>";
    
} catch (Exception $e) {
    echo "<h3>❌ Error durante las pruebas:</h3>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
    echo "<p><strong>Stack trace:</strong></p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
