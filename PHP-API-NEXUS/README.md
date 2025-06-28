# API Nexus - Autenticación

API REST en PHP para servir datos a Nexus-PHP, conectando con SQL Server.

## Configuración

### 1. Variables de entorno
```cmd
# Windows
set SQLSERVER_USER=tu_usuario
set SQLSERVER_PASSWORD=tu_contraseña
set JWT_SECRET=tu_clave_secreta_jwt
```

### 2. Base de datos
- Servidor: `88.24.26.59:1433`
- Base de datos: `NexusUsers`
- Ejecutar: `database/create_users_table.sql`

### 3. Extensiones PHP requeridas
```ini
extension=pdo_sqlsrv
extension=sqlsrv
```

## Endpoints

### POST /api/Auth/Login

**URL**: `http://localhost:8080/api/Auth/Login`

**Request**:
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
