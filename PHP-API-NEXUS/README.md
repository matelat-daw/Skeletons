# Nexus Astralis - API REST PHP

API REST desarrollada en PHP para autenticación de usuarios compatible con ASP.NET Identity, con sistema completo de confirmación de email.

## 🚀 Características

- ✅ **Autenticación JWT** - Tokens seguros para autenticación
- ✅ **Compatible con ASP.NET Identity** - Verificación de contraseñas PBKDF2 con SHA512
- ✅ **Confirmación de Email** - Sistema completo de verificación por email
- ✅ **Envío de Emails** - Integrado con sendmail/SMTP para confirmaciones
- ✅ **Conexión SQL Server** - Soporte nativo con drivers sqlsrv
- ✅ **CORS configurado** - Listo para aplicaciones Angular/React
- ✅ **Seguridad optimizada** - Validaciones, sanitización y manejo de errores
- ✅ **Arquitectura MVC** - Controladores, modelos y servicios separados
- ✅ **Enrutamiento centralizado** - Todo el tráfico pasa por index.php

## ⚙️ Configuración

### 1. Requisitos del Sistema
- PHP 8.2+ con extensiones: `sqlsrv`, `pdo_sqlsrv`
- SQL Server con tabla `AspNetUsers`
- Servidor web (Apache/Nginx)

### 2. Variables de Entorno (.env)

```bash
# Base de datos SQL Server  
SQLSERVER_HOST=88.24.26.59
SQLSERVER_PORT=1433
SQLSERVER_DATABASE=NexusUsers
SQLSERVER_USER=tu_usuario
SQLSERVER_PASSWORD=tu_contraseña

# JWT (cambiar en producción)
JWT_SECRET=clave_super_secreta_unique_para_jwt
JWT_ALGORITHM=HS256
JWT_EXPIRATION=86400

# Email Configuration
SMTP_FROM_EMAIL=noreply@nexusastralis.com
SMTP_FROM_NAME=Nexus Astralis
APP_BASE_URL=http://localhost:8080/Skeletons/PHP-API-NEXUS

# Entorno
ENVIRONMENT=production
DEBUG=false
```

## 🏗️ Arquitectura

### Estructura de Carpetas
```
PHP-API-NEXUS/
├── index.php              # Punto de entrada único (enrutador)
├── .htaccess              # Configuración Apache (redirección)
├── .env                   # Variables de entorno
├── config/                # Configuración
│   ├── database.php       # Conexiones BD
│   ├── Router.php         # Sistema de rutas
│   └── jwt.php           # Configuración JWT
├── controllers/           # Controladores MVC
│   ├── AuthController.php # Autenticación
│   ├── AccountController.php # Perfil de usuario
│   └── BaseController.php # Controlador base
├── models/               # Modelos y repositorios
│   ├── User.php          # Modelo de usuario (solo propiedades)
│   ├── Login.php         # Modelo de login con validaciones
│   ├── UserRepository.php # Acceso a datos de usuario
│   ├── Favorites.php     # Modelo de favoritos
│   └── Comments.php      # Modelo de comentarios
└── services/             # Servicios de negocio
    └── AuthService.php   # Lógica de autenticación
```

### Patrón de Arquitectura
- **Modelo**: Solo propiedades y métodos de utilidad
- **Repository**: Acceso a datos y consultas SQL
- **Service**: Lógica de negocio y validaciones
- **Controller**: Manejo de HTTP y coordinación

## 🔐 API Endpoints

### Autenticación

#### POST `/api/Auth/Register`

**Request:**
```json
{
    "email": "test@example.com",
    "password": "Test123!",
    "confirmPassword": "Test123!",
    "nick": "testuser",
    "name": "Usuario",
    "surname1": "Apellido",
    "surname2": "Segundo",
    "birthdate": "1990-01-01",
    "gender": "M",
    "country": "ES",
    "city": "Madrid",
    "postal_code": "28001",
    "phone": "+34600000000"
}
```

**Response exitosa (201)**:
```json
{
    "message": "Usuario registrado exitosamente. Se ha enviado un email de confirmación a test@example.com",
    "success": true,
    "data": {
        "user": {
            "id": "user-guid-here",
            "nick": "testuser",
            "email": "test@example.com",
            "name": "Usuario",
            "surname1": "Apellido",
            "emailConfirmed": false
        },
        "emailSent": true
    }
}
```

**Errores**:
- `400`: Datos faltantes o inválidos (con detalles de validación)
- `409`: Email o username ya existe
- `500`: Error interno

#### GET `/api/Auth/ConfirmEmail?token=TOKEN`

**Response exitosa (200)**:
```json
{
    "message": "Email confirmado exitosamente. ¡Bienvenido a Nexus Astralis!",
    "success": true,
    "data": {
        "user": {
            "id": "user-guid-here",
            "nick": "testuser",
            "email": "test@example.com",
            "name": "Usuario",
            "emailConfirmed": true
        },
        "welcomeEmailSent": true
    }
}
```

**Errores**:
- `400`: Token inválido o expirado
- `404`: Usuario no encontrado
- `500`: Error interno

#### POST `/api/Auth/ResendConfirmation`

**Request:**
```json
{
    "email": "test@example.com"
}
```

**Response exitosa (200)**:
```json
{
    "message": "Nuevo email de confirmación enviado",
    "success": true
}
```

**Errores**:
- `400`: Email inválido o ya confirmado
- `500`: Error interno

#### POST `/api/Auth/Login`

**Request:**
```json
{
    "email": "test@example.com",
    "password": "Test123!",
    "rememberMe": false
}
```

**Response exitosa (200)**:
```json
{
    "message": "Login exitoso",
    "success": true,
    "data": {
        "user": {
            "id": "user-guid-here",
            "nick": "testuser",
            "email": "test@example.com",
            "name": "Usuario"
        },
        "rememberMe": false
    }
}
```

**Errores**:
- `400`: Datos faltantes o inválidos
- `401`: Credenciales incorrectas o email no confirmado
- `500`: Error interno

### Perfil de Usuario

#### GET `/api/Account/Profile`

**Headers requeridos:**
```
Cookie: auth_token=your_jwt_token_here
```

**Response exitosa (200)**:
```json
{
    "message": "Perfil obtenido exitosamente",
    "data": {
        "id": "user-guid-here",
        "nick": "testuser",
        "email": "test@example.com",
        "name": "Usuario",
        "surname1": "Apellido1",
        "surname2": "Apellido2",
        "phoneNumber": "+34123456789",
        "profileImage": "path/to/image.jpg",
        "bday": "1990-01-01",
        "about": "Descripción del usuario",
        "userLocation": "Madrid, España",
        "publicProfile": true,
        "emailConfirmed": true,
        "favorites": [...],
        "comments": [...]
    }
}
```

### POST `/api/Account/Logout`

**Response exitosa (200)**:
```json
{
    "message": "Sesión cerrada exitosamente"
}
```

#### CORS
- **Origin**: `http://localhost:4200`
- **Credentials**: true
- **Methods**: GET, POST, PUT, DELETE, OPTIONS

## Testing con cURL

```bash
# Login
curl -X POST http://localhost:8080/api/Auth/Login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"Test123!"}'

# Con cookies
curl -X POST http://localhost:8080/api/Auth/Login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"Test123!"}' \
  -c cookies.txt
```

## Integración con Angular

Tu AuthService ya está configurado correctamente:

```typescript
const responseText = await this.fetchAndHandle(
  `${this.API_URL}/Login`,
  { 
    method: 'POST', 
    headers: { 'Content-Type': 'application/json' }, 
    body: JSON.stringify({ email, password }),
    credentials: 'include' // Importante para cookies
  }
);
```

## Estructura de archivos

```
PHP-API-NEXUS/
├── api/
│   └── Auth/
│       └── Login.php          # Endpoint de login
├── config/
│   ├── database.php           # Conexión SQL Server
│   └── jwt.php               # Manejo JWT
├── models/
│   └── User.php              # Modelo de usuario
├── database/
│   └── create_users_table.sql # Script de BD
├── .htaccess                 # Configuración Apache
└── .env.example             # Variables de entorno
```

## Seguridad implementada

- ✅ **Hash de contraseñas** con `password_hash()`
- ✅ **JWT seguro** con clave secreta
- ✅ **HttpOnly cookies** (no accesibles desde JS)
- ✅ **CORS configurado** para dominio específico
- ✅ **Validación de datos** de entrada
- ✅ **Manejo de errores** sin exposición de información sensible
- ✅ **Variables de entorno** para credenciales

## Próximos endpoints

1. `POST /api/Auth/Register` - Registro de usuarios
2. `POST /api/Auth/Logout` - Cerrar sesión
3. `GET /api/Auth/Me` - Obtener usuario actual
4. `POST /api/Auth/GoogleLogin` - Login con Google

## 📧 Flujo de Confirmación de Email

### 1. Registro del Usuario
1. Usuario envía datos de registro a `POST /api/Auth/Register`
2. Sistema valida datos y crea usuario con `email_confirmed = false`
3. Se genera token único de confirmación (válido por 24 horas)
4. Se envía email HTML con enlace de confirmación
5. Usuario recibe email con link: `GET /api/Auth/ConfirmEmail?token=TOKEN`

### 2. Confirmación de Email
1. Usuario hace clic en el enlace del email
2. Sistema valida token (no expirado, no usado, email coincide)
3. Se actualiza `email_confirmed = true` en la base de datos
4. Se marca token como usado
5. Se envía email de bienvenida
6. Usuario puede hacer login normalmente

### 3. Reenvío de Confirmación
1. Si usuario no recibió email, puede solicitar reenvío
2. `POST /api/Auth/ResendConfirmation` con email
3. Sistema genera nuevo token y envía nuevo email
4. Tokens anteriores siguen siendo válidos hasta expiración

### 4. Características de Seguridad
- ✅ **Tokens únicos**: Cada token es criptográficamente seguro
- ✅ **Expiración**: Tokens válidos por 24 horas
- ✅ **Un solo uso**: Token se marca como usado tras confirmación
- ✅ **Validación de email**: Token solo válido para email específico
- ✅ **Limpieza automática**: Posibilidad de limpiar tokens expirados

### 5. Templates de Email
- **Email de confirmación**: HTML responsive con botón de acción
- **Email de bienvenida**: Mensaje personalizado tras confirmación
- **Texto plano**: Alternativa para clientes que no soportan HTML

## 📝 Base de Datos

### Tabla: EmailConfirmationTokens
```sql
CREATE TABLE EmailConfirmationTokens (
    id INT IDENTITY(1,1) PRIMARY KEY,
    user_id NVARCHAR(450) NOT NULL,
    email NVARCHAR(256) NOT NULL,
    token NVARCHAR(255) NOT NULL UNIQUE,
    created_at DATETIME2 DEFAULT GETDATE(),
    expires_at DATETIME2 NOT NULL,
    used TINYINT DEFAULT 0,
    used_at DATETIME2 NULL,
    
    INDEX IX_EmailConfirmationTokens_Token (token),
    INDEX IX_EmailConfirmationTokens_Email (email),
    INDEX IX_EmailConfirmationTokens_UserId (user_id)
);
```

### Campo adicional en tabla users:
```sql
ALTER TABLE users ADD email_confirmed TINYINT DEFAULT 0;
```

## 🧪 Pruebas

### Ejecutar Pruebas Completas
```bash
php test_complete_flow.php
```

Este script prueba:
1. ✅ Registro de usuario
2. ✅ Generación de token de confirmación  
3. ✅ Confirmación de email
4. ✅ Login tras confirmación
5. ✅ Reenvío de confirmación (debe fallar)
6. ✅ Limpieza de datos de prueba

### Pruebas Manuales con cURL

**Registro:**
```bash
curl -X POST http://localhost:8080/Skeletons/PHP-API-NEXUS/api/Auth/Register \
-H "Content-Type: application/json" \
-d '{
    "email": "test@example.com",
    "password": "Test123!",
    "confirmPassword": "Test123!",
    "nick": "testuser",
    "name": "Test",
    "surname1": "User"
}'
```

**Confirmación:**
```bash
curl -X GET "http://localhost:8080/Skeletons/PHP-API-NEXUS/api/Auth/ConfirmEmail?token=YOUR_TOKEN_HERE"
```

**Login:**
```bash
curl -X POST http://localhost:8080/Skeletons/PHP-API-NEXUS/api/Auth/Login \
-H "Content-Type: application/json" \
-d '{
    "email": "test@example.com",
    "password": "Test123!"
}'
```

## 🔧 Configuración de Email

### Usando sendmail (Linux/macOS)
```bash
# Instalar sendmail
sudo apt-get install sendmail

# Configurar en .env
SMTP_FROM_EMAIL=noreply@yourdomain.com
SMTP_FROM_NAME=Your App Name
APP_BASE_URL=https://yourdomain.com/path/to/api
```

### Usando SMTP externo (Gmail, etc.)
Para usar Gmail u otros proveedores SMTP, modifica `EmailService.php` para usar bibliotecas como PHPMailer.

## 📋 Pendientes y Mejoras

### Implementadas ✅
- [x] Registro de usuarios con validación completa
- [x] Confirmación de email con tokens seguros
- [x] Envío de emails HTML responsive
- [x] Login con verificación de email confirmado
- [x] Reenvío de confirmación
- [x] Arquitectura MVC desacoplada
- [x] Pruebas automatizadas completas

### Por Implementar 🔄
- [ ] Integración con frontend Angular
- [ ] Reset de contraseña por email
- [ ] Limpieza automática de tokens expirados (cron job)
- [ ] Rate limiting para endpoints de email
- [ ] Dashboard de administración
- [ ] Logs centralizados
- [ ] Métricas de confirmación de emails

## 🚀 Integración con Frontend

### Angular Service Example
```typescript
export class AuthService {
  register(userData: RegisterData): Observable<any> {
    return this.http.post('/api/Auth/Register', userData);
  }
  
  confirmEmail(token: string): Observable<any> {
    return this.http.get(`/api/Auth/ConfirmEmail?token=${token}`);
  }
  
  resendConfirmation(email: string): Observable<any> {
    return this.http.post('/api/Auth/ResendConfirmation', { email });
  }
}
```

### React Hook Example
```javascript
const useAuth = () => {
  const register = async (userData) => {
    const response = await fetch('/api/Auth/Register', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(userData)
    });
    return response.json();
  };
  
  return { register };
};
```
