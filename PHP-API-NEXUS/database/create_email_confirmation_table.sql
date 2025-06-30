-- Tabla para almacenar tokens de confirmación de email
-- Compatible con SQL Server

CREATE TABLE EmailConfirmationTokens (
    Id UNIQUEIDENTIFIER DEFAULT NEWID() PRIMARY KEY,
    UserId NVARCHAR(450) NOT NULL,
    Email NVARCHAR(256) NOT NULL,
    Token NVARCHAR(128) NOT NULL UNIQUE,
    ExpiresAt DATETIME2 NOT NULL,
    CreatedAt DATETIME2 NOT NULL DEFAULT GETDATE(),
    ConfirmedAt DATETIME2 NULL,
    
    -- Índices para optimizar consultas
    INDEX IX_EmailConfirmationTokens_Token (Token),
    INDEX IX_EmailConfirmationTokens_UserId (UserId),
    INDEX IX_EmailConfirmationTokens_ExpiresAt (ExpiresAt),
    
    -- Foreign key si existe la tabla de usuarios
    -- FOREIGN KEY (UserId) REFERENCES AspNetUsers(Id) ON DELETE CASCADE
);

-- Procedimiento para limpiar tokens expirados (opcional)
-- Se puede ejecutar como tarea programada
/*
CREATE PROCEDURE CleanupExpiredEmailTokens
AS
BEGIN
    DELETE FROM EmailConfirmationTokens 
    WHERE ExpiresAt < GETDATE();
    
    PRINT 'Tokens expirados eliminados: ' + CAST(@@ROWCOUNT AS VARCHAR(10));
END;
*/
