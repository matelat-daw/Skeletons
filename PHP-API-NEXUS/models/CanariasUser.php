<?php
/**
 * Modelo User para Economía Circular Canarias
 * Compatible con la estructura de base de datos MySQL existente
 */

require_once __DIR__ . '/../config/database.php';

class CanariasUser {
    // Propiedades basadas en la estructura de la tabla 'user'
    public $id;              // int(11) - AUTO_INCREMENT
    public $name;            // varchar(20) - Nombre del usuario
    public $surname;         // varchar(20) - Apellido del usuario
    public $surname2;        // varchar(20) - Segundo apellido (opcional)
    public $dni;             // varchar(10) - DNI del usuario
    public $phone;           // varchar(12) - Teléfono del usuario
    public $email;           // varchar(32) - Email del usuario (único)
    public $pass;            // varchar(180) - Contraseña hasheada SHA512
    public $bday;            // date - Fecha de nacimiento
    public $gender;          // tinyint(1) - Género (0=Mujer, 1=Hombre, 2=Otro)
    public $path;            // varchar(256) - Ruta de imagen de perfil
    public $hash;            // varchar(16) - Hash de confirmación de email
    public $active;          // tinyint(1) - Estado activo (0=Inactivo, 1=Activo)

    private $db;

    /**
     * Constructor
     */
    public function __construct() {
        $this->db = getDatabase()->getConnection();
    }

    /**
     * Registrar nuevo usuario
     * @param array $userData
     * @return array
     */
    public function register($userData) {
        try {
            // Validar datos requeridos
            $validation = $this->validateRegistrationData($userData);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => 'Datos de registro inválidos',
                    'errors' => $validation['errors']
                ];
            }

            // Verificar si el email ya existe
            if ($this->emailExists($userData['email'])) {
                return [
                    'success' => false,
                    'message' => 'El email ya está registrado'
                ];
            }

            // Verificar si el DNI ya existe (si se proporciona)
            if (!empty($userData['dni']) && $this->dniExists($userData['dni'])) {
                return [
                    'success' => false,
                    'message' => 'El DNI ya está registrado'
                ];
            }

            // Hashear la contraseña con SHA512
            $hashedPassword = hash('sha512', $userData['password']);

            // Generar hash de confirmación
            $confirmationHash = substr(md5(uniqid(rand(), true)), 0, 16);

            // Preparar datos para inserción
            $insertData = [
                'name' => $userData['name'],
                'surname' => $userData['surname'] ?? '',
                'surname2' => $userData['surname2'] ?? '',
                'dni' => $userData['dni'] ?? '',
                'phone' => $userData['phone'] ?? '',
                'email' => $userData['email'],
                'pass' => $hashedPassword,
                'bday' => $userData['bday'] ?? null,
                'gender' => $userData['gender'] ?? 0,
                'path' => $userData['path'] ?? '',
                'hash' => $confirmationHash,
                'active' => 0  // Usuario inactivo hasta confirmación de email
            ];

            // Insertar usuario en la base de datos
            $sql = "INSERT INTO user (name, surname, surname2, dni, phone, email, pass, bday, gender, path, hash, active) 
                    VALUES (:name, :surname, :surname2, :dni, :phone, :email, :pass, :bday, :gender, :path, :hash, :active)";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($insertData);

            if ($result) {
                $userId = $this->db->lastInsertId();
                
                // Log de registro exitoso
                error_log("✅ Usuario registrado exitosamente: ID={$userId}, Email={$userData['email']}");

                return [
                    'success' => true,
                    'message' => 'Usuario registrado exitosamente. Por favor, confirma tu email.',
                    'user_id' => $userId,
                    'confirmation_hash' => $confirmationHash,
                    'email' => $userData['email']
                ];
            } else {
                throw new Exception('Error al insertar usuario en la base de datos');
            }

        } catch (PDOException $e) {
            error_log("❌ Error en registro de usuario: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error interno del servidor'
            ];
        } catch (Exception $e) {
            error_log("❌ Error en registro: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Iniciar sesión de usuario
     * @param string $email
     * @param string $password
     * @return array
     */
    public function login($email, $password) {
        try {
            // Validar parámetros
            if (empty($email) || empty($password)) {
                return [
                    'success' => false,
                    'message' => 'Email y contraseña son requeridos'
                ];
            }

            // Buscar usuario por email
            $sql = "SELECT * FROM user WHERE email = :email LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Credenciales inválidas'
                ];
            }

            // Verificar si el usuario está activo
            if ($user['active'] != 1) {
                return [
                    'success' => false,
                    'message' => 'Cuenta no activada. Por favor, confirma tu email.'
                ];
            }

            // Verificar contraseña
            $hashedPassword = hash('sha512', $password);
            if ($hashedPassword !== $user['pass']) {
                return [
                    'success' => false,
                    'message' => 'Credenciales inválidas'
                ];
            }

            // Login exitoso - preparar datos del usuario
            $userData = [
                'id' => $user['id'],
                'name' => $user['name'],
                'surname' => $user['surname'],
                'email' => $user['email'],
                'phone' => $user['phone'],
                'gender' => $user['gender'],
                'path' => $user['path']
            ];

            // Log de login exitoso
            error_log("✅ Login exitoso: ID={$user['id']}, Email={$email}");

            return [
                'success' => true,
                'message' => 'Login exitoso',
                'user' => $userData
            ];

        } catch (PDOException $e) {
            error_log("❌ Error en login: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error interno del servidor'
            ];
        }
    }

    /**
     * Confirmar email del usuario
     * @param string $email
     * @param string $hash
     * @return array
     */
    public function confirmEmail($email, $hash) {
        try {
            $sql = "UPDATE user SET active = 1, hash = NULL WHERE email = :email AND hash = :hash AND active = 0";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute(['email' => $email, 'hash' => $hash]);

            if ($stmt->rowCount() > 0) {
                error_log("✅ Email confirmado: {$email}");
                return [
                    'success' => true,
                    'message' => 'Email confirmado exitosamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Hash de confirmación inválido o ya utilizado'
                ];
            }

        } catch (PDOException $e) {
            error_log("❌ Error confirmando email: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error interno del servidor'
            ];
        }
    }

    /**
     * Obtener usuario por ID
     * @param int $userId
     * @return array|null
     */
    public function getUserById($userId) {
        try {
            $sql = "SELECT id, name, surname, surname2, email, phone, bday, gender, path, active 
                    FROM user WHERE id = :id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $userId]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("❌ Error obteniendo usuario: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Validar datos de registro
     * @param array $data
     * @return array
     */
    private function validateRegistrationData($data) {
        $errors = [];

        // Validar nombre
        if (empty($data['name'])) {
            $errors[] = ['field' => 'name', 'message' => 'El nombre es requerido'];
        } elseif (strlen($data['name']) > 20) {
            $errors[] = ['field' => 'name', 'message' => 'El nombre no puede tener más de 20 caracteres'];
        }

        // Validar email
        if (empty($data['email'])) {
            $errors[] = ['field' => 'email', 'message' => 'El email es requerido'];
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = ['field' => 'email', 'message' => 'Email inválido'];
        } elseif (strlen($data['email']) > 32) {
            $errors[] = ['field' => 'email', 'message' => 'El email no puede tener más de 32 caracteres'];
        }

        // Validar contraseña
        if (empty($data['password'])) {
            $errors[] = ['field' => 'password', 'message' => 'La contraseña es requerida'];
        } elseif (strlen($data['password']) < 8) {
            $errors[] = ['field' => 'password', 'message' => 'La contraseña debe tener al menos 8 caracteres'];
        }

        // Validar DNI (si se proporciona)
        if (!empty($data['dni']) && !preg_match('/^[0-9]{8}[A-Za-z]$/', $data['dni'])) {
            $errors[] = ['field' => 'dni', 'message' => 'DNI inválido (formato: 12345678A)'];
        }

        // Validar teléfono (si se proporciona)
        if (!empty($data['phone']) && !preg_match('/^[0-9+\-\s]{9,12}$/', $data['phone'])) {
            $errors[] = ['field' => 'phone', 'message' => 'Teléfono inválido'];
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Verificar si el email ya existe
     * @param string $email
     * @return bool
     */
    private function emailExists($email) {
        try {
            $sql = "SELECT COUNT(*) FROM user WHERE email = :email";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['email' => $email]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("❌ Error verificando email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar si el DNI ya existe
     * @param string $dni
     * @return bool
     */
    private function dniExists($dni) {
        try {
            $sql = "SELECT COUNT(*) FROM user WHERE dni = :dni";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['dni' => $dni]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("❌ Error verificando DNI: " . $e->getMessage());
            return false;
        }
    }
}

?>
