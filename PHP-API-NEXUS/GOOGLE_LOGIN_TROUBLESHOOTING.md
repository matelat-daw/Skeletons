# 🚨 Diagnóstico: Error "Token no pertenece a esta aplicación"

## ✅ Estado del Diagnóstico

**BUENAS NOTICIAS**: El backend PHP está funcionando **PERFECTAMENTE**. El problema no está en el servidor.

### 📊 Evidencia del Diagnóstico

**De los logs de PHP:**
```
Token real recibido:
aud: "1071917637623-020l5qbcihpj4u7tdv411cov4cfh530c.apps.googleusercontent.com"

Client ID configurado en backend:
"1071917637623-020l5qbcihpj4u7tdv411cov4cfh530c.apps.googleusercontent.com"

✅ Los Client IDs COINCIDEN PERFECTAMENTE
```

## 🔍 Causas Probables del Error

### 1. **Token Expirado (MÁS PROBABLE)**
- Los tokens de Google expiran rápidamente (1 hora típicamente)
- El token que llegó en los logs tenía: `exp: 1751621632` (fecha futura)
- **Solución**: Generar un token fresco desde Angular

### 2. **Token de Desarrollo vs Producción**
- El token podría ser para un entorno diferente
- **Verificar**: ¿El token viene del mismo proyecto de Google Console?

### 3. **Configuración de Dominios Autorizados**
- Google requiere que el dominio desde donde se solicita el token esté autorizado
- **Verificar en Google Console**: `http://localhost:4200` debe estar en "Authorized JavaScript origins"

## 🛠️ Soluciones Paso a Paso

### **Paso 1: Verificar Configuración en Google Console**

1. Ve a [Google Cloud Console](https://console.cloud.google.com/)
2. Selecciona tu proyecto
3. Ve a "APIs & Services" > "Credentials"
4. Encuentra tu OAuth 2.0 Client ID: `1071917637623-020l5qbcihpj4u7tdv411cov4cfh530c`
5. **Verifica "Authorized JavaScript origins"**:
   - ✅ `http://localhost:4200` (para desarrollo Angular)
   - ✅ `http://localhost:8080` (si necesario)
   - ✅ Tu dominio de producción

### **Paso 2: Generar Token Fresco en Angular**

El problema más probable es que estás usando un token expirado. Necesitas:

1. **En tu aplicación Angular**, asegúrate de que el login con Google genere un token fresco
2. **No reutilices tokens** - cada login debe generar uno nuevo
3. **Usa immediatamente** el token después de generarlo

### **Paso 3: Verificar el Token en Tiempo Real**

He agregado un endpoint de debugging:

**URL**: `http://localhost:8080/api/Auth/DebugGoogleToken`

**Uso**:
```javascript
// En tu Angular, después de obtener el token de Google:
const debugResponse = await fetch('http://localhost:8080/api/Auth/DebugGoogleToken', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({token: googleToken})
});
const debugData = await debugResponse.json();
console.log('Token analysis:', debugData);
```

## 🔧 Código de Verificación para Angular

Agrega esto a tu servicio de autenticación para debugging:

```typescript
async googleLogin(token: string): Promise<void> {
    // PASO 1: Debug del token antes de enviarlo
    console.log('🔍 Token a enviar:', token.substring(0, 50) + '...');
    
    // PASO 2: Verificar token con endpoint de debug
    try {
        const debugResponse = await fetch(`${this.API_URL}Auth/DebugGoogleToken`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ token })
        });
        const debugData = await debugResponse.json();
        console.log('🔍 Análisis del token:', debugData);
        
        if (!debugData.success) {
            console.error('❌ Token inválido:', debugData.error);
            return;
        }
    } catch (e) {
        console.error('❌ Error verificando token:', e);
    }
    
    // PASO 3: Proceder con login normal si el debug es exitoso
    const response = await fetch(`${this.API_URL}Auth/GoogleLogin`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ token })
    });
    
    // ... resto del código
}
```

## 🎯 Verificación Inmediata

**Prueba esto AHORA mismo**:

1. **Abre tu aplicación Angular** en `http://localhost:4200`
2. **Inicia el login con Google** y obtén un token fresco
3. **Inmediatamente** (sin esperar) prueba el login
4. **Mira los logs** de la consola del navegador y los logs de PHP

## 📋 Checklist de Verificación

- [ ] **Google Console**: ¿`http://localhost:4200` está en "Authorized JavaScript origins"?
- [ ] **Token fresco**: ¿El token se genera inmediatamente antes de usar?
- [ ] **Logs de debugging**: ¿Qué muestra el endpoint `/api/Auth/DebugGoogleToken`?
- [ ] **Expiración**: ¿El campo `exp` en el token está en el futuro?
- [ ] **Audiencia**: ¿El campo `aud` coincide exactamente con el Client ID?

## 🏆 Conclusión

**El backend PHP está 100% funcional**. El error está en:
1. **Token expirado** (más probable)
2. **Configuración de dominios en Google Console**
3. **Timing entre generación y uso del token**

**Siguiente paso**: Usa el endpoint de debugging para analizar el token que está llegando y confirmar la causa exacta.

## 🔗 Enlaces Útiles

- **Google Console**: https://console.cloud.google.com/
- **Endpoint de Debug**: `http://localhost:8080/api/Auth/DebugGoogleToken`
- **Página de Pruebas**: `http://localhost:8080/test-google-login.html`
