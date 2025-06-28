<?php
// Script para probar el endpoint de login
$url = 'http://localhost:8080/Skeletons/PHP-API-NEXUS/api/Auth/Login.php';

// Datos de prueba (ajusta estos valores por un usuario real de tu base de datos)
$data = [
    'email' => 'test@example.com',  // Cambia por un email real de tu BD
    'password' => 'testpassword'    // Cambia por una contraseña real
];

$options = [
    'http' => [
        'header' => "Content-type: application/json\r\n",
        'method' => 'POST',
        'content' => json_encode($data)
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);

echo "Respuesta del servidor:\n";
echo $result . "\n";

if ($http_response_header) {
    echo "\nHeaders de respuesta:\n";
    foreach($http_response_header as $header) {
        echo $header . "\n";
    }
}
?>
