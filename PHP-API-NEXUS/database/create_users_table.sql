-- Script para crear la base de datos y tabla de usuarios en SQL Server

-- Usar la base de datos NexusUsers
USE NexusUsers;

-- Crear tabla de usuarios si no existe
IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='Users' AND xtype='U')
BEGIN
    CREATE TABLE Users (
        id INT IDENTITY(1,1) PRIMARY KEY,
        nick NVARCHAR(50) NOT NULL UNIQUE,
        email NVARCHAR(100) NOT NULL UNIQUE,
        password NVARCHAR(255) NOT NULL,
        name NVARCHAR(100) NOT NULL,
        surname1 NVARCHAR(100) NOT NULL,
        surname2 NVARCHAR(100) NULL,
        created_at DATETIME2 DEFAULT GETDATE(),
        updated_at DATETIME2 DEFAULT GETDATE(),
        is_verified BIT DEFAULT 0,
        last_login DATETIME2 NULL,
        reset_token NVARCHAR(255) NULL,
        reset_token_expires DATETIME2 NULL
    );
END

-- Crear índices para optimizar búsquedas
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = 'IX_Users_Email')
BEGIN
    CREATE INDEX IX_Users_Email ON Users (email);
END

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = 'IX_Users_Nick')
BEGIN
    CREATE INDEX IX_Users_Nick ON Users (nick);
END

-- Insertar usuario de prueba (opcional)
-- Contraseña: Test123!
IF NOT EXISTS (SELECT * FROM Users WHERE email = 'test@example.com')
BEGIN
    INSERT INTO Users (nick, email, password, name, surname1, is_verified)
    VALUES (
        'testuser',
        'test@example.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- Test123!
        'Usuario',
        'Prueba',
        1
    );
END

-- Mostrar los usuarios existentes
SELECT id, nick, email, name, surname1, created_at, is_verified FROM Users;
