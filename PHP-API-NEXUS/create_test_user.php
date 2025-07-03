<?php
/**
 * Script temporal para crear un usuario de prueba en la base de datos
 */

// Incluir dependencias
require_once 'config/database_manager.php';
require_once 'models/User.php';
require_once 'repositories/UserRepository.php';
require_once 'services/AuthService.php';

try {
    // Conectar a la base de datos
    $dbManager = new DatabaseManager();
    $connection = $dbManager->getConnection('NexusUsers');
    $userRepository = new UserRepository($connection);

    // Datos del usuario de prueba
    $email = 'cesarmatelat@gmail.com';
    $password = 'test123';
    $nick = 'cesarmatelat';
    $name = 'César';
    $surname1 = 'Matelat';

    // Verificar si el usuario ya existe
    if ($userRepository->emailExists($email)) {
        echo "El usuario con email $email ya existe.\n";
        
        // Obtener el usuario existente para mostrar información
        $existingUser = $userRepository->findByEmail($email);
        if ($existingUser) {
            echo "ID: " . $existingUser->id . "\n";
            echo "Nick: " . $existingUser->nick . "\n";
            echo "Email confirmado: " . ($existingUser->emailConfirmed ? 'Sí' : 'No') . "\n";
        }
    } else {
        // Crear nuevo usuario
        echo "Creando usuario de prueba...\n";

        // Generar hash de la contraseña (compatible con ASP.NET Identity)
        $passwordHash = password_hash($password, PASSWORD_ARGON2ID);

        // Crear usuario
        $userId = $userRepository->create([
            'UserName' => $email,
            'Email' => $email,
            'EmailConfirmed' => true,
            'PasswordHash' => $passwordHash,
            'Nick' => $nick,
            'Name' => $name,
            'Surname1' => $surname1,
            'Bday' => '1990-01-01',
            'PublicProfile' => true
        ]);

        if ($userId) {
            echo "Usuario creado exitosamente!\n";
            echo "ID: $userId\n";
            echo "Email: $email\n";
            echo "Password: $password\n";
            echo "Nick: $nick\n";
        } else {
            echo "Error al crear el usuario.\n";
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    error_log("Error en create_test_user.php: " . $e->getMessage());
}
?>
