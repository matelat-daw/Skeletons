# 🏝️ Sistema de Autenticación - Economía Circular Canarias

Sistema completo de autenticación con JWT, SHA-512 y confirmación por email para la plataforma de Economía Circular de Canarias.

## 📋 Características

✅ **Backend PHP Completo**
- Conexión a MySQL con PDO
- Autenticación JWT con cookies seguras
- Encriptación SHA-512 para contraseñas
- Confirmación de email por hash
- API REST con CORS configurado

✅ **Frontend JavaScript**
- Compatibilidad con componentes Angular-style
- Integración automática con cookies JWT
- Notificaciones modernas
- Validación en tiempo real

✅ **Seguridad Avanzada**
- Tokens JWT con expiración
- Cookies HttpOnly y Secure
- Validación de datos robusta
- Protección contra ataques CSRF

## 🚀 Configuración Rápida

### 1. Configurar Base de Datos

1. **Crear base de datos MySQL:**
```sql
CREATE DATABASE users CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. **Importar estructura de tabla** (según imagen proporcionada):
```sql
USE users;

CREATE TABLE user (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(20) NOT NULL,
    surname VARCHAR(20) NOT NULL,
    surname2 VARCHAR(20) NULL,
    dni VARCHAR(10) NULL,
    phone VARCHAR(12) NULL,
    email VARCHAR(32) NOT NULL UNIQUE,
    pass VARCHAR(180) NOT NULL,
    bday DATE NULL,
    gender TINYINT(1) DEFAULT 0,
    path VARCHAR(256) NULL,
    hash VARCHAR(16) NULL,
    active TINYINT(1) DEFAULT 0
);
```

### 2. Configurar Credenciales

Editar `config/database.php`:
```php
private $host = 'localhost';
private $db_name = 'users';
private $username = 'tu_usuario_mysql';
private $password = 'tu_contraseña_mysql';
```

### 3. Verificar Instalación

1. **Abrir en navegador:**
   - `http://localhost/Skeletons/PHP-API-NEXUS/test-canarias.php`
   - Verificar que todas las extensiones PHP estén OK
   - Confirmar conexión a base de datos

2. **Probar API:**
   - `http://localhost/Skeletons/PHP-API-NEXUS/test-frontend.html`
   - Usar el panel de pruebas interactivo

## 📡 Endpoints del API

### Autenticación

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| `POST` | `/api/auth/register` | Registrar nuevo usuario |
| `POST` | `/api/auth/login` | Iniciar sesión |
| `POST` | `/api/auth/logout` | Cerrar sesión |
| `GET`  | `/api/auth/validate` | Validar token JWT |
| `GET`  | `/api/auth/confirm` | Confirmar email |

### Utilidades

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| `GET` | `/api/status` | Estado del API |
| `GET` | `/api/test/database` | Probar base de datos |

## 📝 Ejemplos de Uso

### Registro de Usuario

```javascript
const userData = {
    name: "Juan Pérez",
    email: "juan@example.com",
    password: "contraseña123",
    confirmPassword: "contraseña123",
    acceptTerms: true
};

const response = await fetch('http://localhost/Skeletons/PHP-API-NEXUS/api/auth/register', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    credentials: 'include',
    body: JSON.stringify(userData)
});

const result = await response.json();
console.log(result);
```

### Login de Usuario

```javascript
const credentials = {
    email: "juan@example.com",
    password: "contraseña123",
    rememberMe: true
};

const response = await fetch('http://localhost/Skeletons/PHP-API-NEXUS/api/auth/login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    credentials: 'include',
    body: JSON.stringify(credentials)
});

const result = await response.json();
// Token JWT se almacena automáticamente en cookie 'canarias_auth_token'
```

### Validar Token

```javascript
const response = await fetch('http://localhost/Skeletons/PHP-API-NEXUS/api/auth/validate', {
    method: 'GET',
    credentials: 'include'  // Envía automáticamente la cookie
});

const result = await response.json();
if (result.success && result.valid) {
    console.log('Usuario autenticado:', result.user);
}
```

## 🛠️ Integración con Frontend

### AuthService Actualizado

El `AuthService` en `Canarias-Ang/src/app/services/auth.service.js` está configurado para:

- ✅ Conectar automáticamente con el backend PHP
- ✅ Manejar cookies JWT de forma transparente
- ✅ Sincronizar estado de autenticación
- ✅ Disparar eventos para componentes

### Uso en Componentes

```javascript
// Registrar usuario
const result = await window.authService.register(userData);

// Iniciar sesión
const result = await window.authService.login(credentials);

// Verificar autenticación
if (window.authService.isAuthenticated()) {
    const user = window.authService.getCurrentUser();
}

// Cerrar sesión
await window.authService.logout();
```

## 🔧 Configuración Avanzada

### Variables de Entorno

Crear archivo `.env` en la raíz:
```env
ENVIRONMENT=development
JWT_SECRET=tu_clave_secreta_jwt_super_segura
DB_HOST=localhost
DB_NAME=users
DB_USER=root
DB_PASS=
```

### CORS Personalizado

Editar `config/CorsHandler.php` para configurar dominios permitidos.

### SSL/HTTPS

Para producción, configurar:
- Cookies con flag `Secure`
- Certificados SSL
- Headers de seguridad adicionales

## 📚 Estructura de Archivos

```
PHP-API-NEXUS/
├── config/
│   ├── database.php          # Conexión MySQL
│   ├── jwt.php              # Manejo JWT
│   └── CorsHandler.php      # Configuración CORS
├── models/
│   └── CanariasUser.php     # Modelo de usuario
├── controllers/
│   └── CanariasAuthController.php  # Controlador auth
├── logs/                    # Logs del sistema
├── canarias-api.php         # Router principal
├── test-canarias.php        # Verificación sistema
├── test-frontend.html       # Panel de pruebas
└── .htaccess-canarias       # Configuración Apache
```

## 🚨 Solución de Problemas

### Error de Conexión a Base de Datos
1. Verificar credenciales en `config/database.php`
2. Asegurar que MySQL esté ejecutándose
3. Comprobar que la base de datos `users` existe

### Error CORS
1. Copiar `.htaccess-canarias` a `.htaccess`
2. Verificar que mod_rewrite esté habilitado
3. Ajustar dominios en `CorsHandler.php`

### JWT No Funciona
1. Verificar que las cookies estén habilitadas
2. Comprobar configuración `JWT_SECRET`
3. Asegurar que el tiempo del servidor sea correcto

### Problemas de Permisos
```bash
# En Linux/Mac, ajustar permisos
chmod 755 logs/
chmod 644 logs/*.log
```

## 📞 Soporte

Para reportar problemas o sugerencias:
1. Revisar logs en `logs/php_errors.log`
2. Usar el panel de pruebas en `test-frontend.html`
3. Verificar configuración con `test-canarias.php`

## 🎯 Próximos Pasos

Una vez configurado el sistema:

1. **Integrar con Frontend Angular-style**: El `AuthService` ya está listo
2. **Configurar Email Real**: Implementar SMTP para confirmaciones
3. **Añadir Funcionalidades**: Perfil de usuario, recuperación de contraseña
4. **Deploy a Producción**: Configurar SSL, dominio real, base de datos remota

---

**🏝️ ¡El sistema está listo para usar! La base para Economía Circular Canarias está preparada.**
