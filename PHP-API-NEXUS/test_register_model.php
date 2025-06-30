<?php
/**
 * Test de validación simple del modelo Register
 */

require_once 'models/Register.php';

echo "=== PRUEBA DE VALIDACIÓN DEL MODELO REGISTER ===\n\n";

// Datos de prueba
$userData = [
    'email' => 'test@example.com',
    'password' => 'TestPassword123!',
    'confirmPassword' => 'TestPassword123!',
    'nick' => 'testuser',
    'name' => 'Test',
    'surname1' => 'User',
    'surname2' => 'Example',
    'birthdate' => '1990-01-01',
    'gender' => 'M',
    'country' => 'ES',
    'city' => 'Madrid',
    'postal_code' => '28001',
    'phone' => '+34600000000'
];

echo "Datos enviados:\n";
echo json_encode($userData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// Crear modelo Register
$registerModel = new Register($userData);

echo "=== PROPIEDADES DEL MODELO ===\n";
echo "Email: " . ($registerModel->email ?? 'NULL') . "\n";
echo "Password: " . ($registerModel->password ?? 'NULL') . "\n";
echo "Password2: " . ($registerModel->password2 ?? 'NULL') . "\n";
echo "Nick: " . ($registerModel->nick ?? 'NULL') . "\n";
echo "Name: " . ($registerModel->name ?? 'NULL') . "\n";
echo "Surname1: " . ($registerModel->surname1 ?? 'NULL') . "\n";
echo "Birthdate: " . ($registerModel->bday ?? 'NULL') . "\n";
echo "Gender: " . ($registerModel->gender ?? 'NULL') . "\n";
echo "Country: " . ($registerModel->country ?? 'NULL') . "\n";
echo "City: " . ($registerModel->city ?? 'NULL') . "\n";
echo "Phone: " . ($registerModel->phone_number ?? 'NULL') . "\n\n";

// Validar modelo
echo "=== VALIDACIÓN ===\n";
$isValid = $registerModel->isValid();
echo "¿Es válido?: " . ($isValid ? 'SÍ' : 'NO') . "\n";

if (!$isValid) {
    $errors = $registerModel->getValidationErrors();
    echo "Errores encontrados:\n";
    foreach ($errors as $error) {
        echo "- $error\n";
    }
} else {
    echo "✅ Modelo válido\n";
}

echo "\n=== CONVERSIÓN A USER ===\n";
try {
    $user = $registerModel->toUser();
    echo "✅ Conversión exitosa\n";
    echo "User ID: " . ($user->id ?? 'NULL') . "\n";
    echo "User Email: " . ($user->email ?? 'NULL') . "\n";
    echo "User Nick: " . ($user->nick ?? 'NULL') . "\n";
} catch (Exception $e) {
    echo "❌ Error en conversión: " . $e->getMessage() . "\n";
}

echo "\n=== FIN DEL TEST ===\n";
?>
