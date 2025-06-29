# 🌟 Nexus Astralis - API REST PHP

API REST desarrollada en PHP para la gestión de usuarios, autenticación y funcionalidades de astronomía. Compatible con ASP.NET Identity y optimizada para producción con SQL Server.

## ✨ Características Principales

- **Autenticación JWT**: Login/Logout seguro con cookies HTTP-only
- **Gestión de Perfiles**: CRUD completo de perfiles de usuario
- **Sistema de Favoritos**: Gestión de constelaciones favoritas por usuario
- **Sistema de Comentarios**: Comentarios de usuarios sobre constelaciones
- **Múltiples Bases de Datos**: Soporte para NexusUsers y nexus_stars
- **Compatible ASP.NET**: Hash de contraseñas PBKDF2-HMACSHA512
- **CORS Configurado**: Listo para frontend Angular/React

## 🗄️ Estructura de la Base de Datos

### Base de Datos: NexusUsers
- `AspNetUsers`: Usuarios del sistema (compatible con ASP.NET Identity)
- `Favorites`: Favoritos de usuarios (Id, UserId, ConstellationId)
- `Comments`: Comentarios de usuarios (Id, UserId, UserNick, Comment, ConstellationId, ConstellationName)

### Base de Datos: nexus_stars
- `constellations`: Información de constelaciones
- `stars`: Información de estrellas
- `constellation_stars`: Relación estrellas-constelaciones

## 🚀 Endpoints Disponibles

### Autenticación
- `POST /api/Auth/Login` - Iniciar sesión
- `POST /api/Account/Logout` - Cerrar sesión

### Perfil de Usuario
- `GET /api/Account/Profile` - Obtener perfil completo (incluye favoritos y comentarios)
- `PATCH /api/Account/Update` - Actualizar perfil
- `DELETE /api/Account/Delete` - Eliminar cuenta

### Favoritos
- `GET /api/Account/Favorites` - Obtener todos los favoritos del usuario
- `GET /api/Account/Favorites/{id}` - Verificar si una constelación es favorita
- `POST /api/Account/Favorites/{id}` - Agregar constelación a favoritos
- `DELETE /api/Account/Favorites/{id}` - Eliminar constelación de favoritos

### Comentarios
- `GET /api/Account/Comments` - Obtener todos los comentarios del usuario
- `GET /api/Account/Comments/{id}` - Obtener comentario específico
- `POST /api/Account/Comments` - Agregar nuevo comentario
- `PUT /api/Account/Comments/{id}` - Actualizar comentario
- `DELETE /api/Account/Comments/{id}` - Eliminar comentario

## 📁 Estructura del Proyecto

```
PHP-API-NEXUS/
├── api/
│   ├── Auth/
│   │   └── Login.php
│   └── Account/
│       ├── Profile.php
│       ├── Logout.php
│       ├── Favorites.php
│       └── Comments.php
├── config/
│   ├── database_manager.php
│   ├── jwt.php
│   └── env.php
├── models/
│   ├── User.php
│   ├── Favorites.php
│   ├── Comments.php
│   ├── Star.php
│   └── Constellation.php
└── .htaccess
```

## 🔧 Configuración

### Variables de Entorno (.env)
```
SQLSERVER_HOST=tu_servidor
SQLSERVER_PORT=1433
SQLSERVER_USER=tu_usuario
SQLSERVER_PASSWORD=tu_contraseña
SQLSERVER_DATABASE_USERS=NexusUsers
SQLSERVER_DATABASE_STARS=nexus_stars
JWT_SECRET=tu_clave_secreta_jwt
```

### Requisitos del Servidor
- PHP 7.4+ con extensiones:
  - `pdo_sqlsrv`
  - `sqlsrv`
  - `openssl`
- SQL Server 2016+
- Servidor web con soporte para `.htaccess` (Apache/IIS)

## 🛡️ Seguridad

- **Autenticación JWT**: Tokens seguros con expiración
- **Cookies HTTP-only**: Previene ataques XSS
- **CORS Configurado**: Permite solo orígenes autorizados
- **Validación de Entrada**: Sanitización de todos los datos
- **Hash de Contraseñas**: Compatible con ASP.NET Identity

## 💻 Uso desde Frontend (Angular/React)

### Ejemplo: Obtener Perfil
```typescript
async getProfile() {
  const response = await fetch('/api/Account/Profile', {
    method: 'GET',
    credentials: 'include'
  });
  const data = await response.json();
  return data.data; // Datos del perfil con favoritos y comentarios
}
```

### Ejemplo: Agregar Favorito
```typescript
async addFavorite(constellationId: number) {
  const response = await fetch(`/api/Account/Favorites/${constellationId}`, {
    method: 'POST',
    credentials: 'include'
  });
  const data = await response.json();
  return data.success;
}
```

### Ejemplo: Agregar Comentario
```typescript
async addComment(comment: string, constellationId: number) {
  const response = await fetch('/api/Account/Comments', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ comment, constellationId }),
    credentials: 'include'
  });
  const data = await response.json();
  return data.success;
}
```

## 🏗️ Arquitectura

### DatabaseManager
Gestiona conexiones a múltiples bases de datos:
- Conexión persistente y reutilizable
- Manejo de errores robusto
- Configuración centralizada

### Modelos
- **User**: Gestión completa de usuarios con métodos de autenticación
- **Favorites**: CRUD de favoritos con validaciones
- **Comments**: CRUD de comentarios con permisos de usuario
- **Constellation/Star**: Modelos de datos astronómicos

### JWT Handler
- Generación y validación de tokens
- Gestión de cookies seguras
- Expiración automática

## 🚦 Estados de Respuesta

### Códigos HTTP
- `200`: Operación exitosa
- `201`: Recurso creado
- `400`: Error de validación
- `401`: No autenticado
- `403`: Sin permisos
- `404`: Recurso no encontrado
- `409`: Conflicto (ej: favorito duplicado)
- `500`: Error interno

### Formato de Respuesta
```json
{
  "message": "Descripción del resultado",
  "success": true/false,
  "data": { ... } // Datos opcional
}
```

## 🔄 Migración desde ASP.NET

Esta API es completamente compatible con datos existentes de ASP.NET:
- Misma estructura de base de datos
- Mismo sistema de hash de contraseñas
- Mismos IDs de usuario (GUID)
- Compatibilidad total de datos

## 📝 Changelog

### v2.0.0 (Actual)
- ✅ Sistema de favoritos completamente implementado
- ✅ Sistema de comentarios con CRUD completo
- ✅ Modelos optimizados para SQL Server
- ✅ Endpoints robustos con validación completa
- ✅ Integración total con frontend Angular
- ✅ Documentación completa

### v1.0.0
- ✅ Autenticación básica (Login/Logout)
- ✅ Gestión de perfiles de usuario
- ✅ Conexión dual a bases de datos
- ✅ Compatibilidad con ASP.NET Identity

---

**🌟 Nexus Astralis - Explorando el cosmos, conectando usuarios 🌟**
