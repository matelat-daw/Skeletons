<?php
/**
 * Script temporal para cargar variables de entorno desde .env
 * Útil para debugging y desarrollo
 */

function loadEnvironmentVariables($filePath) {
    if (!file_exists($filePath)) {
        echo "Archivo .env no encontrado: $filePath\n";
        return false;
    }
    
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        // Saltar comentarios
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Buscar formato VARIABLE=valor
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            // Remover comillas si existen
            $value = trim($value, '"\'');
            
            // Resolver variables de entorno ${VARIABLE}
            if (preg_match('/\$\{([^}]+)\}/', $value, $matches)) {
                $envVar = $matches[1];
                $envValue = getenv($envVar);
                if ($envValue !== false) {
                    $value = str_replace($matches[0], $envValue, $value);
                } else {
                    echo "Variable de entorno no encontrada: $envVar\n";
                    continue;
                }
            }
            
            // Establecer variable de entorno
            putenv("$name=$value");
            $_ENV[$name] = $value;
            
            echo "Cargada: $name = $value\n";
        }
    }
    
    return true;
}

// Cargar variables de entorno
echo "=== CARGANDO VARIABLES DE ENTORNO ===\n";
loadEnvironmentVariables(__DIR__ . '/.env');

echo "\n=== VERIFICANDO GOOGLE CLIENT ID ===\n";
echo "getenv('GOOGLE_CLIENT_ID'): " . (getenv('GOOGLE_CLIENT_ID') ?: 'NO_SET') . "\n";
echo "getenv('Google-Client-Id'): " . (getenv('Google-Client-Id') ?: 'NO_SET') . "\n";
echo "\$_ENV['GOOGLE_CLIENT_ID']: " . ($_ENV['GOOGLE_CLIENT_ID'] ?? 'NO_SET') . "\n";

echo "\n=== PROBANDO GOOGLE AUTH SERVICE ===\n";
try {
    require_once 'services/GoogleAuthService.php';
    $googleService = new GoogleAuthService();
    echo "✅ GoogleAuthService inicializado correctamente\n";
    echo "Client ID configurado: " . $googleService->getClientId() . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
