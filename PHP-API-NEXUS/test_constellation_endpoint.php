<?php
/**
 * Test simple con cURL del endpoint de constelaciones
 */

$baseUrl = 'http://localhost:8080/Skeletons/PHP-API-NEXUS';

echo "🌌 === TEST DEL ENDPOINT CONSTELLATIONS === 🌌\n\n";

// Test del endpoint /api/Constellations
echo "📡 Probando GET /api/Constellations...\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/api/Constellations');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FAILONERROR, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Origin: http://localhost:4200'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "📊 HTTP Code: $httpCode\n";

if ($error) {
    echo "❌ cURL Error: $error\n";
} else {
    echo "📄 Response: " . substr($response, 0, 200) . "...\n\n";
    
    $decoded = json_decode($response, true);
    if ($decoded) {
        echo "=== RESPUESTA DECODIFICADA ===\n";
        if ($decoded['success']) {
            echo "✅ SUCCESS: " . $decoded['message'] . "\n";
            echo "📈 Total constelaciones: " . $decoded['data']['total'] . "\n";
            
            if (!empty($decoded['data']['constellations'])) {
                $first = $decoded['data']['constellations'][0];
                echo "\n🌟 Primera constelación:\n";
                echo "- ID: " . $first['id'] . "\n";
                echo "- Código: " . $first['code'] . "\n";
                echo "- Nombre inglés: " . $first['english_name'] . "\n";
                echo "- Nombre español: " . ($first['spanish_name'] ?: 'No disponible') . "\n";
                
                // Test del endpoint específico
                echo "\n📡 Probando GET /api/Constellations/{$first['id']}...\n";
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $baseUrl . '/api/Constellations/' . $first['id']);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FAILONERROR, false);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Origin: http://localhost:4200'
                ]);
                
                $singleResponse = curl_exec($ch);
                $singleHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                echo "📊 HTTP Code: $singleHttpCode\n";
                
                $singleDecoded = json_decode($singleResponse, true);
                if ($singleDecoded && $singleDecoded['success']) {
                    echo "✅ Constelación individual obtenida exitosamente\n";
                } else {
                    echo "❌ Error obteniendo constelación individual\n";
                }
            }
        } else {
            echo "❌ ERROR: " . $decoded['message'] . "\n";
        }
    } else {
        echo "❌ Error decodificando JSON response\n";
    }
}

echo "\n🏁 === FIN DEL TEST === 🏁\n";

if ($httpCode == 200) {
    echo "🎉 ¡EL ENDPOINT ESTÁ FUNCIONANDO CORRECTAMENTE!\n";
    echo "📱 El frontend Angular debería poder acceder sin problemas\n";
} else {
    echo "🔧 Revisar configuración - HTTP Code: $httpCode\n";
}
?>
