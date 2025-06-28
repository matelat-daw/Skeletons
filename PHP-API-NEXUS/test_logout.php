<?php
// Script para probar el endpoint de logout
$url = 'http://localhost:8080/Skeletons/PHP-API-NEXUS/api/Account/Logout.php';

$options = [
    'http' => [
        'header' => "Content-type: application/json\r\n",
        'method' => 'POST',
        'content' => json_encode([])
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
