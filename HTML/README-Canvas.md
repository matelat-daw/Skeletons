# 🎸 Guitar Luthier - Canvas Web Component

Una página web de guitarras personalizadas diseñada específicamente para ser integrada en **Microsoft Power Apps Canvas**.

## 📋 Características

### ✨ Diseño
- **Réplica exacta** del diseño de guitarluthier.com
- **Responsive design** - Mobile, Tablet y Desktop
- **Animaciones suaves** y transiciones profesionales
- **Colores modernos** - Navy oscuro con acentos turquesa
- **Tipografía optimizada** para legibilidad

### 🔗 Integración Canvas
- **Comunicación bidireccional** vía postMessage
- **API JavaScript** para control desde Canvas
- **Eventos personalizados** para tracking de acciones
- **Datos dinámicos** actualizables desde Canvas
- **Responsive** dentro de Canvas Apps

## 🚀 Uso en Canvas Apps

### 1. Como HTML Text Control
```javascript
// En una Canvas App, usar HTML Text Control con:
"<iframe src='./guitarras-canvas.html' width='100%' height='600px' frameborder='0'></iframe>"
```

### 2. Como Web Page Control
```javascript
// URL: ./guitarras-canvas.html
// Habilitar "Allow popups" si es necesario
```

### 3. Integración Avanzada
```javascript
// Escuchar eventos desde la página web
App.OnMessage = If(
    Message.type = "CANVAS_ACTION",
    Set(LastUserAction, Message.action);
    Set(ActionTimestamp, Message.timestamp)
)

// Enviar comandos a la página web
HTMLText1.HtmlText = "<iframe id='guitarFrame' src='./guitarras-canvas.html'></iframe>
<script>
document.getElementById('guitarFrame').contentWindow.postMessage({
    type: 'CANVAS_COMMAND',
    command: 'UPDATE_CONTENT',
    payload: { title: '" & App.DynamicTitle & "', description: '" & App.DynamicDescription & "' }
}, '*');
</script>"
```

## 📊 API JavaScript

### Métodos Disponibles
```javascript
// Obtener datos de la página
window.GuitarApp.getPageData()
// Retorna: { title: "...", description: "...", currentAction: "..." }

// Ejecutar acciones
window.GuitarApp.performAction("scroll-to-section", { section: "acerca" })
window.GuitarApp.performAction("show-notification", { message: "¡Hola desde Canvas!" })
window.GuitarApp.performAction("trigger-cta", { action: "custom-action" })
```

### Eventos Emitidos
```javascript
// Cuando el usuario hace clic en "Explora Ahora"
{
    type: "CANVAS_ACTION",
    action: "explore_guitars",
    timestamp: "2025-07-12T10:30:00.000Z",
    source: "guitar-luthier-web"
}

// Cuando la página está lista
{
    type: "WEB_READY",
    timestamp: "2025-07-12T10:30:00.000Z",
    capabilities: ["smooth-scroll", "cta-actions", "mobile-responsive"]
}
```

## 🎨 Personalización

### Colores
```css
:root {
    --primary-turquoise: #4ecdc4;
    --primary-turquoise-hover: #45b7b8;
    --dark-navy: #1a1a2e;
    --dark-blue: #16213e;
    --text-white: #ffffff;
    --text-gray: #b8b8b8;
}
```

### Contenido Dinámico
```javascript
// Desde Canvas, enviar:
{
    type: "CANVAS_COMMAND",
    command: "UPDATE_CONTENT",
    payload: {
        title: "Tu Nuevo Título",
        description: "Tu Nueva Descripción"
    }
}
```

## 📱 Responsive Breakpoints

- **Mobile**: ≤ 480px
- **Tablet**: 481px - 768px  
- **Desktop**: ≥ 769px

## 🔧 Instalación en Canvas

### Opción 1: Archivos Locales
1. Subir `guitarras-canvas.html`, `css/guitarras-canvas.css`, y `js/guitarras-canvas.js` a tu entorno
2. Usar HTML Text Control o Web Page Control con la ruta local

### Opción 2: Hosting Externo
1. Hospedar los archivos en un servidor web
2. Configurar CORS si es necesario
3. Usar la URL completa en Canvas

### Opción 3: Embebido Directo
```javascript
// En HTML Text Control, pegar todo el CSS y JS inline
// Ver guitarras-canvas-inline.html para versión de un solo archivo
```

## 🌟 Características Especiales

### Animaciones
- **Fade in up** para elementos hero
- **Hover effects** en botones y navegación
- **Smooth scroll** para navegación
- **Scale transform** en imagen principal

### Performance
- **CSS optimizado** con variables
- **JavaScript modular** y eficiente
- **Lazy loading** preparado
- **Reduced motion** respetado

### Accesibilidad
- **ARIA labels** apropiados
- **Contraste WCAG AA** compliant
- **Navegación por teclado** soportada
- **Screen reader** friendly

## 🚀 Testing

### Test Básico
1. Abrir `guitarras-canvas.html` en navegador
2. Verificar responsividad redimensionando ventana
3. Probar navegación y botones
4. Verificar console para logs de integración

### Test Canvas Integration
1. Abrir herramientas de desarrollador
2. En Console, ejecutar:
```javascript
// Simular mensaje de Canvas
window.postMessage({
    type: "CANVAS_COMMAND",
    command: "UPDATE_CONTENT",
    payload: { title: "Test desde Console" }
}, "*");

// Verificar API disponible
console.log(window.GuitarApp);
```

## 📞 Soporte

- **Archivos principales**: `guitarras-canvas.html`, `css/guitarras-canvas.css`, `js/guitarras-canvas.js`
- **Configuración**: `canvas-config.json`
- **Documentación**: Este README

---

### 🎯 Lista de Verificación Canvas

- ✅ **HTML semántico** y limpio
- ✅ **CSS responsive** sin dependencias externas
- ✅ **JavaScript modular** con API pública
- ✅ **Comunicación postMessage** implementada
- ✅ **Eventos tracking** configurados
- ✅ **Mobile responsive** verificado
- ✅ **Accesibilidad** implementada
- ✅ **Performance optimizado**

¡Tu página web está lista para Canvas Apps! 🚀
