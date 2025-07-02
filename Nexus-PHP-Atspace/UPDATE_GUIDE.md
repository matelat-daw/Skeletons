# 📋 Guía de Actualización de Dependencias - Nexus PHP

## ✅ **Dependencias ya actualizadas:**
- ✅ `typescript`: 5.7.3 → 5.8.3
- ✅ `@types/three`: 0.176.0 → 0.177.0  
- ✅ `three`: 0.176.0 → 0.177.0
- ✅ `jasmine-core`: 5.6.0 → 5.8.0
- ✅ `@angular/cli`: 19.2.14 → 19.2.15
- ✅ `@angular/cdk`: 19.2.18 → 19.2.19
- ✅ `@angular/material`: 19.2.18 → 19.2.19

## 🔄 **Pendientes de actualizar:**

### **Angular (Actualización Mayor: v19 → v20)**
```bash
# 1. Commit o stash cambios pendientes
git stash

# 2. Actualizar Angular Core (v19 → v20)
ng update @angular/core

# 3. Actualizar Angular CLI
ng update @angular/cli

# 4. Actualizar Angular Material
ng update @angular/material

# 5. Restaurar cambios si es necesario
git stash pop
```

### **Vite (Actualización Mayor: v6 → v7)**
```bash
# Actualizar Vite (puede requerir cambios en configuración)
npm install vite@latest
```

## ⚠️ **Consideraciones importantes:**

### **Actualización Angular v19 → v20:**
- **Breaking changes**: Puede haber cambios incompatibles
- **Migración automática**: `ng update` aplicará migraciones automáticas
- **Revisar changelog**: https://angular.dev/update-guide
- **Probar exhaustivamente** después de la actualización

### **Actualización Vite v6 → v7:**
- **Configuración**: Puede requerir cambios en `vite.config.ts`
- **Plugins**: Verificar compatibilidad de plugins
- **Build**: Probar que el build funcione correctamente

## 🚀 **Proceso recomendado:**

### **Opción 1: Actualización conservadora (Recomendado)**
```bash
# Solo actualizar patches y minor versions
npm update

# Mantener Angular v19 estable
# Actualizar a v20 en una rama separada para testing
```

### **Opción 2: Actualización completa (Solo si es necesario)**
```bash
# 1. Crear branch de backup
git checkout -b backup-before-angular-20

# 2. Volver a main y actualizar
git checkout main
git stash  # Si hay cambios pendientes

# 3. Actualizar Angular
ng update @angular/core @angular/cli @angular/material

# 4. Actualizar Vite
npm install vite@latest

# 5. Probar aplicación
ng serve
ng build

# 6. Si algo falla, volver al backup
git checkout backup-before-angular-20
```

## 🔍 **Comandos útiles:**

```bash
# Ver dependencias desactualizadas
npm outdated

# Ver información de paquetes disponibles
ng update

# Verificar versión de Angular
ng version

# Auditar vulnerabilidades
npm audit

# Limpiar cache de npm
npm cache clean --force
```

## 🚨 **Solución para error "JavaScript heap out of memory":**

### **Error común:**
```
FATAL ERROR: Reached heap limit Allocation failed - JavaScript heap out of memory
```

### **Soluciones:**

#### **Solución temporal (por sesión):**
```bash
# PowerShell
$env:NODE_OPTIONS="--max-old-space-size=8192"

# CMD
set NODE_OPTIONS=--max-old-space-size=8192

# Verificar
echo $env:NODE_OPTIONS
```

#### **Solución permanente:**
```bash
# Agregar a variables de entorno del sistema
NODE_OPTIONS=--max-old-space-size=8192
```

#### **Comandos de limpieza:**
```bash
# Limpiar cache de npm
npm cache clean --force

# Limpiar node_modules y reinstalar
rmdir /s node_modules
npm install
```

#### **Script automatizado:**
```bash
# Ejecutar el script fix-memory-issue.bat
./fix-memory-issue.bat
```

## 💡 **Explicación del problema:**
- **Angular** y **npm** pueden usar mucha memoria durante compilaciones
- **Node.js** por defecto limita la memoria a ~1.7GB
- **Proyectos grandes** requieren más memoria para las operaciones
- **La solución** aumenta el límite a 8GB

## 📝 **Estado actual del proyecto:**

- **Angular**: v19.2.14/19.2.15 (Estable)
- **Node.js**: v20.19.1 (LTS - Perfecto)
- **npm**: v11.4.1 (Actualizado)
- **TypeScript**: v5.8.3 (Actualizado ✅)

## 🎯 **Recomendación final:**

**Para un proyecto en desarrollo activo:**
- Mantener Angular v19 (muy estable)
- Solo actualizar cuando v20 esté más maduro
- Actualizar las dependencias menores regularmente

**El proyecto está en muy buen estado técnicamente** 👍
