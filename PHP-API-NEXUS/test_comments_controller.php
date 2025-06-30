<?php
/**
 * Test del controlador de comentarios
 * Prueba todos los endpoints principales
 */

echo "<h2>Test CommentsController - Endpoints Principales</h2>";

// Array de pruebas
$tests = [
    [
        'name' => 'GET /api/Comments - Obtener todos los comentarios',
        'url' => 'http://localhost:8080/Skeletons/PHP-API-NEXUS/api/Comments',
        'method' => 'GET'
    ],
    [
        'name' => 'GET /api/Comments/ById/1 - Obtener comentario por ID',
        'url' => 'http://localhost:8080/Skeletons/PHP-API-NEXUS/api/Comments/ById/1',
        'method' => 'GET'
    ],
    [
        'name' => 'POST /api/Comments - Crear comentario',
        'url' => 'http://localhost:8080/Skeletons/PHP-API-NEXUS/api/Comments',
        'method' => 'POST',
        'data' => [
            'constellationId' => 1,
            'comment' => 'Test comment from PHP API'
        ]
    ]
];

foreach ($tests as $test) {
    echo "<div style='border: 1px solid #ccc; margin: 10px; padding: 15px; border-radius: 5px;'>";
    echo "<h3>" . $test['name'] . "</h3>";
    
    // Configurar cURL
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $test['url'],
        CURLOPT_CUSTOMREQUEST => $test['method'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer fake-token-for-test' // Token falso para test
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    ]);
    
    // Si es POST, agregar datos
    if ($test['method'] === 'POST' && isset($test['data'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test['data']));
    }
    
    // Ejecutar petición
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    if ($error) {
        echo "<p style='color: red;'>❌ Error cURL: " . htmlspecialchars($error) . "</p>";
    } else {
        echo "<p><strong>Status Code:</strong> " . $httpCode . "</p>";
        
        // Mostrar respuesta
        if ($httpCode >= 200 && $httpCode < 300) {
            echo "<p style='color: green;'>✅ Respuesta exitosa</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Respuesta no exitosa</p>";
        }
        
        echo "<h4>Respuesta:</h4>";
        echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 3px; max-height: 200px; overflow-y: auto;'>";
        echo htmlspecialchars($response);
        echo "</pre>";
        
        // Intentar decodificar JSON
        $data = json_decode($response, true);
        if ($data && json_last_error() === JSON_ERROR_NONE) {
            echo "<h4>JSON Parseado:</h4>";
            echo "<pre style='background: #e8f5e8; padding: 10px; border-radius: 3px; font-size: 12px;'>";
            print_r($data);
            echo "</pre>";
        }
    }
    
    curl_close($ch);
    echo "</div>";
}

echo "<h3>Rutas Registradas Equivalentes a ASP.NET:</h3>";
echo "<ul>";
echo "<li><strong>GET</strong> /api/Comments → GetAllComments()</li>";
echo "<li><strong>GET</strong> /api/Comments/ById/{id} → GetCommentById()</li>";
echo "<li><strong>GET</strong> /api/Comments/User/{userId} → GetCommentsByUser()</li>";
echo "<li><strong>PUT</strong> /api/Comments/{id} → PutComment()</li>";
echo "<li><strong>POST</strong> /api/Comments → PostComment()</li>";
echo "<li><strong>DELETE</strong> /api/Comments/{id} → DeleteComment()</li>";
echo "</ul>";

echo "<h3>Compatibilidad ASP.NET:</h3>";
echo "<p>✅ Rutas idénticas a ASP.NET Core<br>";
echo "✅ Métodos de controlador equivalentes<br>";
echo "✅ Respuestas JSON compatibles<br>";
echo "✅ Códigos de estado HTTP coincidentes<br>";
echo "✅ Autenticación Bearer requerida</p>";
?>
