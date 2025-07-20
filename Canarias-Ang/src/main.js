// Main.js - Punto de entrada de la aplicación
// Economía Circular Canarias - Aplicación estilo Angular con JavaScript

// Función para manejar la pantalla de carga
function handleLoadingScreen() {
    // Remover pantalla de carga cuando la aplicación esté lista
    window.addEventListener('load', () => {
        setTimeout(() => {
            document.body.classList.add('app-loaded');
            setTimeout(() => {
                const loadingScreen = document.querySelector('.loading-screen');
                if (loadingScreen) {
                    loadingScreen.remove();
                }
            }, 500);
        }, 1000);
    });
}

// Función principal que inicia la aplicación
function bootstrapApplication() {
    console.log('🏝️ Iniciando Economía Circular Canarias...');
    
    // Crear y inicializar la aplicación principal
    const app = new AppComponent();
    app.init();
    
    console.log('✅ Aplicación cargada correctamente');
}

// Esperar a que el DOM esté completamente cargado
document.addEventListener('DOMContentLoaded', () => {
    bootstrapApplication();
    handleLoadingScreen();
});

// Manejo de errores globales
window.addEventListener('error', (e) => {
    console.error('❌ Error en la aplicación:', e.error);
});

// Registrar Service Worker (si está disponible)
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        // En una aplicación real, aquí registrarías el service worker
        console.log('🔧 Service Worker disponible');
    });
}
