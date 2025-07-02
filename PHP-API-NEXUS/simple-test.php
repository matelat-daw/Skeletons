<?php
// Test ultra-simple para diagnosticar headers CORS
error_log("=== SIMPLE TEST START ===");

// No usar nada de output buffering
// No incluir nada más
// Solo headers mínimos

$origin = $_SERVER['HTTP_ORIGIN'] ?? 'NO_ORIGIN';
error_log("SIMPLE TEST - Origin: " . $origin);

// Verificar headers que Apache puede haber establecido ANTES de que hagamos nada
$existing_headers = headers_list();
error_log("SIMPLE TEST - Headers BEFORE we do anything: " . json_encode($existing_headers));

// Limpiar headers CORS
header_remove('Access-Control-Allow-Origin');
header_remove('Access-Control-Allow-Credentials');
header_remove('Access-Control-Allow-Methods');
header_remove('Access-Control-Allow-Headers');

// Establecer headers CORS manualmente
header("Access-Control-Allow-Origin: https://localhost:4200", true);
header("Access-Control-Allow-Credentials: true", true);
header("Content-Type: application/json");

$final_headers = headers_list();
error_log("SIMPLE TEST - Headers AFTER our setup: " . json_encode($final_headers));

// Respuesta simple
echo json_encode([
    'message' => 'Simple test successful',
    'origin' => $origin,
    'headers_before' => $existing_headers,
    'headers_after' => $final_headers,
    'server_vars' => [
        'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
        'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? 'UNKNOWN'
    ]
]);

error_log("=== SIMPLE TEST END ===");
?>
