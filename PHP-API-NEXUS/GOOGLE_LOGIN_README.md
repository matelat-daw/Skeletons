# 🔐 Google Login Integration - Nexus API

## Resumen

Se ha implementado exitosamente la autenticación con Google en el backend PHP, equivalente al método que tienes en ASP.NET.

## ✅ Archivos Modificados/Creados

### Backend PHP (c:\Server\www\Skeletons\PHP-API-NEXUS\)

1. **controllers/AuthController.php** - ✅ MODIFICADO
   - Agregado método `googleLogin()` 
   - Agregado método auxiliar `verifyUser()` 
   - Compatibilidad completa con el formato ASP.NET

2. **index.php** - ✅ MODIFICADO  
   - Agregada ruta: `POST /api/Auth/GoogleLogin`

3. **services/GoogleAuthService.php** - ✅ EXISTÍA
   - Servicio de validación de tokens Google JWT
   - Utiliza Google OAuth2 API para validación

4. **models/ExternalLogin.php** - ✅ EXISTÍA
   - Modelo compatible con ASP.NET para login externo

5. **.env** - ✅ ACTUALIZADO
   - Variable: `GOOGLE_CLIENT_ID` configurada

6. **test-google-login.html** - ✅ NUEVO
   - Página de pruebas para el endpoint

## 🔧 Configuración Requerida

### 1. Variable de Entorno - Google Client ID

Configura tu Google Client ID en **una** de estas formas:

**Opción A: Variable de entorno del sistema**
```cmd
setx Google-Client-Id "tu_client_id_aqui.apps.googleusercontent.com"
```

**Opción B: Editar archivo .env**
```env
GOOGLE_CLIENT_ID=tu_client_id_aqui.apps.googleusercontent.com
```

### 2. Obtener Google Client ID

1. Ve a [Google Cloud Console](https://console.cloud.google.com/)
2. Crea un proyecto o selecciona uno existente
3. Habilita "Google+ API" 
4. Ve a "Credentials" > "Create Credentials" > "OAuth 2.0 Client ID"
5. Configura tu dominio autorizado: `http://localhost:8080` (para desarrollo)
6. Copia el Client ID generado

## 📋 Endpoint Implementado

### POST /api/Auth/GoogleLogin

**URL:** `http://localhost:8080/api/Auth/GoogleLogin`

**Headers:**
```json
{
  "Content-Type": "application/json"
}
```

**Body:**
```json
{
  "token": "eyJhbGciOiJSUzI1NiIsImtpZCI6IjdkYzEuLi4"
}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Inicio de Sesión Exitoso",
  "data": {
    "token": "jwt_token_local_generado",
    "nick": "usuario123",
    "email": "usuario@gmail.com", 
    "name": "Usuario Test",
    "profileImage": "https://lh3.googleusercontent.com/..."
  }
}
```

**Respuesta Error (400):**
```json
{
  "success": false,
  "message": "Token Inválido",
  "data": {
    "error": "Token de Google inválido o expirado"
  }
}
```

## 🔄 Flujo de Autenticación

### Equivalencia con ASP.NET

| ASP.NET | PHP | Descripción |
|---------|-----|-------------|
| `GoogleJsonWebSignature.ValidateAsync()` | `GoogleAuthService->validateGoogleToken()` | Validación del token JWT |
| `payload.Email`, `payload.Name`, `payload.Picture` | `$payload['email']`, `$payload['name']`, `$payload['picture']` | Extracción de datos del usuario |
| `VerifyUser()` | `verifyUser()` | Crear/actualizar usuario en BD |
| `GenerateToken()` | `AuthService::generateJwtPayload()` + `jwt->generateTokenFromPayload()` | Generar token local |

### 1. Cliente (Angular)
```typescript
// En tu servicio Angular
async googleLogin(token: string): Promise<void> {
  const response = await fetch(`${this.API_URL}Auth/GoogleLogin`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ token })
  });
  
  const data = await response.json();
  if (data.success) {
    sessionStorage.setItem('auth_token', data.data.token);
    this.token.set(data.data.token);
  }
}
```

### 2. Validación del Token

El backend:
1. ✅ Valida el formato JWT del token
2. ✅ Verifica con Google OAuth2 API
3. ✅ Valida que el `aud` (audience) coincida con tu Client ID
4. ✅ Extrae email, nombre y foto de perfil

### 3. Gestión de Usuario

El sistema:
1. ✅ Busca usuario existente por email
2. ✅ Si existe: actualiza información (nombre, foto, confirmación email)
3. ✅ Si no existe: crea nuevo usuario con nick único
4. ✅ Marca email como confirmado (Google implica verificación)
5. ✅ Genera token JWT local para sesión

## 🧪 Pruebas

### Método 1: Página de Prueba
```
http://localhost:8080/test-google-login.html
```

### Método 2: cURL
```bash
curl -X POST "http://localhost:8080/api/Auth/GoogleLogin" \
  -H "Content-Type: application/json" \
  -d '{"token":"tu_token_jwt_de_google"}'
```

### Método 3: PowerShell
```powershell
Invoke-WebRequest -Uri "http://localhost:8080/api/Auth/GoogleLogin" -Method POST -Headers @{"Content-Type"="application/json"} -Body '{"token":"tu_token_jwt_de_google"}'
```

## 🚀 Integración con Angular

El endpoint está listo para integrarse con tu código Angular existente. Solo necesitas:

1. ✅ URL ya configurada: `http://localhost:8080/api/Auth/GoogleLogin` 
2. ✅ Método ya implementado en `auth.service.ts` (`googleLogin()`)
3. ✅ Manejo de errores compatible
4. ✅ Formato de respuesta consistente

## ⚠️ Notas Importantes

1. **Seguridad**: En producción, usa HTTPS y configura dominios autorizados en Google Console
2. **Variables de entorno**: Nunca pongas el Client ID real en el código fuente
3. **Rate limiting**: Google tiene límites en las validaciones de token
4. **Expiración**: Los tokens de Google expiran, maneja los errores apropiadamente

## ✅ Estado Actual

- ✅ Backend PHP implementado y funcionando
- ✅ Validación con Google OAuth2 API
- ✅ Gestión automática de usuarios
- ✅ Generación de tokens JWT locales  
- ✅ Manejo de errores robusto
- ✅ Compatible con formato ASP.NET
- ✅ Rutas configuradas en router
- ✅ Página de pruebas disponible

🎉 **¡El Google Login está completamente implementado y listo para usar!**
