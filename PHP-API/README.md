# API REST - Clientes

Esta es una API REST completa para gestionar clientes con operaciones CRUD.

## Configuración

1. **Base de datos**: Ejecutar el script `database/clients.sql` en MySQL
2. **Variables de entorno**: Configurar la variable `MySQL` con la contraseña de la base de datos
3. **Servidor**: Usar `php -S localhost:8080` en la carpeta PHP-API

### Configuración de variables de entorno

**En Windows (CMD):**
```cmd
set MySQL=tu_contraseña_mysql
php -S localhost:8080
```

**En Windows (PowerShell):**
```powershell
$env:MySQL = "tu_contraseña_mysql"
php -S localhost:8080
```

**En Linux/Mac:**
```bash
export MySQL=tu_contraseña_mysql
php -S localhost:8080
```

### Archivo .env (opcional)
Puedes copiar `.env.example` como `.env` y configurar tus variables ahí, aunque necesitarás una librería como `vlucas/phpdotenv` para cargarlas automáticamente.

## Endpoints

### GET - Obtener todos los clientes
```
GET /clients
GET /clients?search=juan
```

**Respuesta exitosa (200):**
```json
{
    "message": "Clientes encontrados.",
    "data": [
        {
            "id": "1",
            "name": "Juan Pérez",
            "email": "juan.perez@email.com",
            "phone": "+34 666 123 456",
            "address": "Calle Mayor 123, Madrid",
            "created_at": "2025-01-01 12:00:00",
            "updated_at": "2025-01-01 12:00:00"
        }
    ]
}
```

### GET - Obtener un cliente específico
```
GET /clients/1
```

**Respuesta exitosa (200):**
```json
{
    "message": "Cliente encontrado.",
    "data": {
        "id": "1",
        "name": "Juan Pérez",
        "email": "juan.perez@email.com",
        "phone": "+34 666 123 456",
        "address": "Calle Mayor 123, Madrid",
        "created_at": "2025-01-01 12:00:00",
        "updated_at": "2025-01-01 12:00:00"
    }
}
```

### POST - Crear nuevo cliente
```
POST /clients
Content-Type: application/json

{
    "name": "Nuevo Cliente",
    "email": "nuevo@email.com",
    "phone": "+34 666 555 444",
    "address": "Nueva dirección"
}
```

**Respuesta exitosa (201):**
```json
{
    "message": "Cliente creado exitosamente.",
    "data": {
        "id": "5",
        "name": "Nuevo Cliente",
        "email": "nuevo@email.com",
        "phone": "+34 666 555 444",
        "address": "Nueva dirección"
    }
}
```

### PUT - Actualizar cliente
```
PUT /clients/1
Content-Type: application/json

{
    "name": "Juan Pérez Actualizado",
    "email": "juan.actualizado@email.com",
    "phone": "+34 666 999 888",
    "address": "Nueva dirección actualizada"
}
```

**Respuesta exitosa (200):**
```json
{
    "message": "Cliente actualizado exitosamente."
}
```

### DELETE - Eliminar cliente
```
DELETE /clients/1
```

**Respuesta exitosa (200):**
```json
{
    "message": "Cliente eliminado exitosamente."
}
```

## Códigos de respuesta

- **200**: OK - Operación exitosa
- **201**: Created - Cliente creado
- **400**: Bad Request - Datos incompletos
- **404**: Not Found - Cliente o endpoint no encontrado
- **405**: Method Not Allowed - Método HTTP no permitido
- **503**: Service Unavailable - Error en la base de datos

## Ejemplos con cURL

### Listar todos los clientes
```bash
curl -X GET http://localhost:8080/clients
```

### Buscar clientes
```bash
curl -X GET "http://localhost:8080/clients?search=juan"
```

### Obtener un cliente
```bash
curl -X GET http://localhost:8080/clients/1
```

### Crear cliente
```bash
curl -X POST http://localhost:8080/clients \
  -H "Content-Type: application/json" \
  -d '{"name":"Test User","email":"test@email.com","phone":"+34 666 111 222","address":"Test Address"}'
```

### Actualizar cliente
```bash
curl -X PUT http://localhost:8080/clients/1 \
  -H "Content-Type: application/json" \
  -d '{"name":"Updated Name","email":"updated@email.com","phone":"+34 666 333 444","address":"Updated Address"}'
```

### Eliminar cliente
```bash
curl -X DELETE http://localhost:8080/clients/1
```
