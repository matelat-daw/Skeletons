# Nexus Astralis - API REST PHP

API REST desarrollada en PHP para autenticación de usuarios compatible con ASP.NET Identity.

## 🚀 Características

- ✅ **Autenticación JWT** - Tokens seguros para autenticación
- ✅ **Compatible con ASP.NET Identity** - Verificación de contraseñas PBKDF2 con SHA512
- ✅ **Conexión SQL Server** - Soporte nativo con drivers sqlsrv
- ✅ **CORS configurado** - Listo para aplicaciones Angular/React
- ✅ **Seguridad optimizada** - Validaciones, sanitización y manejo de errores

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

# Entorno
ENVIRONMENT=production
DEBUG=false
```

## 🔐 API Endpoints

### POST `/api/Auth/Login.php`

**Request:**
```json
{
    "email": "test@example.com",
    "password": "Test123!"
}
```

**Response exitosa (200)**:
```json
{
    "message": "Login exitoso",
    "data": {
        "user": {
            "id": 1,
            "nick": "testuser",
            "email": "test@example.com",
            "name": "Usuario",
            "surname1": "Prueba"
        },
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
    }
}
```

**Errores**:
- `400`: Datos faltantes o inválidos
- `401`: Credenciales incorrectas
- `403`: Email no verificado
- `500`: Error interno

### Características

#### JWT Token
- **Algoritmo**: HS256
- **Duración**: 24 horas
- **Incluye**: user_id, email, nick, iat, exp

#### Cookie
- **Nombre**: `auth_token`
- **HttpOnly**: true (seguridad)
- **SameSite**: Lax (protección CSRF)
- **Duración**: 24 horas

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
