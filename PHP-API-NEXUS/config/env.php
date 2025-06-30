<?php
// Función simple para cargar variables de entorno desde archivo .env
function loadEnv($file = '.env') {
    $envFile = __DIR__ . '/../' . $file;
    
    if (!file_exists($envFile)) {
        throw new Exception("Archivo .env no encontrado en: $envFile");
    }
    
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue; // Saltar comentarios
        }
        
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remover comillas si las hay
            if (strlen($value) > 1 && $value[0] === '"' && $value[-1] === '"') {
                $value = substr($value, 1, -1);
            }
            
            // Asignar a $_ENV y putenv
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

// Cargar variables de entorno
try {
    loadEnv();
} catch (Exception $e) {
    // En caso de error, usar valores por defecto o mostrar error
    error_log("Error cargando .env: " . $e->getMessage());
}
?>