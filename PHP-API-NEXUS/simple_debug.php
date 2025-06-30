<?php
/**
 * Simple debug script para probar regex patterns
 */

require_once 'config/Router.php';

echo "=== REGEX PATTERN DEBUG ===\n";

// Crear router y acceder a método privado
$router = new Router();
$reflection = new ReflectionClass($router);

// Test del método pathToRegex
$pathToRegexMethod = $reflection->getMethod('pathToRegex');
$pathToRegexMethod->setAccessible(true);

$testPath = '/api/Constellations/{id}';
$pattern = $pathToRegexMethod->invoke($router, $testPath);

echo "Path original: $testPath\n";
echo "Pattern generado: $pattern\n";

// Test con diferentes rutas
$testPaths = [
    '/api/Constellations/6',
    '/api/Constellations',
    '/api/Constellations/GetStars/6',
    '/api/Constellations/ConstelationLines'
];

foreach ($testPaths as $testRoute) {
    $match = preg_match($pattern, $testRoute);
    echo "Testing '$testRoute' against pattern: " . ($match ? 'MATCH' : 'NO MATCH') . "\n";
}

echo "\n=== SPECIFIC TESTS ===\n";

// Test específico para verificar el patrón
$targetPath = '/api/Constellations/6';
$pattern1 = '/^\/api\/Constellations\/([^\/]+)$/';
$pattern2 = '/^\/api\/Constellations\/{([^}]+)}$/';

echo "Target path: $targetPath\n";
echo "Manual pattern 1: $pattern1 - " . (preg_match($pattern1, $targetPath) ? 'MATCH' : 'NO MATCH') . "\n";
echo "Manual pattern 2: $pattern2 - " . (preg_match($pattern2, $targetPath) ? 'MATCH' : 'NO MATCH') . "\n";

// Verificar el pathToRegex step by step
echo "\nStep by step pathToRegex:\n";
$step1 = preg_quote('/api/Constellations/{id}', '/');
echo "Step 1 (preg_quote): $step1\n";
$step2 = preg_replace('/\\\{([^}]+)\\\}/', '([^/]+)', $step1);
echo "Step 2 (replace): $step2\n";
$final = '/^' . $step2 . '$/';
echo "Final pattern: $final\n";

$match = preg_match($final, $targetPath);
echo "Final test: " . ($match ? 'MATCH' : 'NO MATCH') . "\n";

?>
