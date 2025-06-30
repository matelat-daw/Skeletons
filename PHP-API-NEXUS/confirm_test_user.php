<?php
/**
 * Script para confirmar el email del usuario de prueba
 */

require_once 'config/database_manager.php';
require_once 'models/UserRepository.php';

try {
    $dbManager = new DatabaseManager();
    $userRepo = new UserRepository($dbManager->getConnection());
    
    // Buscar el usuario por email
    $user = $userRepo->findByEmail('cesarmatelat@gmail.com');
    
    if ($user) {
        echo "<h2>Usuario encontrado:</h2>";
        echo "ID: " . $user->id . "<br>";
        echo "Email: " . $user->email . "<br>";
        echo "Nick: " . $user->nick . "<br>";
        echo "Email confirmado antes: " . ($user->emailConfirmed ? 'Sí' : 'No') . "<br>";
        echo "Birthday actual: " . ($user->bday ?: 'NULL') . "<br><br>";
        
        // Asegurar que tenga una fecha de cumpleaños válida
        if (empty($user->bday)) {
            $user->bday = '1900-01-01';
            echo "⚠️ Asignando fecha de cumpleaños por defecto: 1900-01-01<br>";
        }
        
        // Asegurar que tenga un valor para PublicProfile
        if ($user->publicProfile === null) {
            $user->publicProfile = true;
            echo "⚠️ Asignando PublicProfile por defecto: true<br>";
        }
        
        // Confirmar el email
        $user->emailConfirmed = true;
        
        if ($userRepo->update($user)) {
            echo "<h3>✅ Email confirmado exitosamente</h3>";
            
            // Verificar la actualización
            $updatedUser = $userRepo->findByEmail('cesarmatelat@gmail.com');
            echo "Email confirmado después: " . ($updatedUser->emailConfirmed ? 'Sí' : 'No') . "<br>";
        } else {
            echo "<h3>❌ Error al confirmar el email</h3>";
        }
    } else {
        echo "<h3>❌ Usuario no encontrado</h3>";
    }
    
} catch (Exception $e) {
    echo "<h3>❌ Error:</h3>";
    echo $e->getMessage();
}
?>
