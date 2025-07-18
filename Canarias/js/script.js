// Clase para manejar el menú hamburguesa móvil
class MobileMenuManager {
    constructor() {
        this.hamburgerMenu = document.getElementById('hamburgerMenu');
        this.mobileAuthMenu = document.getElementById('mobileAuthMenu');
        this.mobileLoginBtn = document.getElementById('mobileLoginBtn');
        this.mobileRegisterBtn = document.getElementById('mobileRegisterBtn');
        this.isOpen = false;
        
        this.init();
    }
    
    init() {
        if (!this.hamburgerMenu || !this.mobileAuthMenu) return;
        
        // Event listeners
        this.hamburgerMenu.addEventListener('click', () => this.toggleMenu());
        this.mobileLoginBtn.addEventListener('click', () => this.handleLogin());
        this.mobileRegisterBtn.addEventListener('click', () => this.handleRegister());
        
        // Cerrar menú al hacer click fuera
        document.addEventListener('click', (e) => this.handleOutsideClick(e));
        
        // Cerrar menú al cambiar orientación o redimensionar
        window.addEventListener('resize', () => this.closeMenu());
        window.addEventListener('orientationchange', () => {
            setTimeout(() => this.closeMenu(), 100);
        });
    }
    
    toggleMenu() {
        if (this.isOpen) {
            this.closeMenu();
        } else {
            this.openMenu();
        }
    }
    
    openMenu() {
        this.isOpen = true;
        this.hamburgerMenu.classList.add('active');
        this.mobileAuthMenu.classList.add('active');
        
        // Añadir vibración en dispositivos compatibles
        if ('vibrate' in navigator) {
            navigator.vibrate(50);
        }
    }
    
    closeMenu() {
        this.isOpen = false;
        this.hamburgerMenu.classList.remove('active');
        this.mobileAuthMenu.classList.remove('active');
    }
    
    handleOutsideClick(e) {
        if (this.isOpen && 
            !this.hamburgerMenu.contains(e.target) && 
            !this.mobileAuthMenu.contains(e.target)) {
            this.closeMenu();
        }
    }
    
    handleLogin() {
        this.closeMenu();
        // Usar la misma lógica que el botón desktop
        if (window.canariasApp) {
            window.canariasApp.handleAuthAction('login');
        }
    }
    
    handleRegister() {
        this.closeMenu();
        // Usar la misma lógica que el botón desktop
        if (window.canariasApp) {
            window.canariasApp.handleAuthAction('register');
        }
    }
}

class ThemeManager {
    constructor() {
        this.themeToggle = document.getElementById('themeToggle');
        this.html = document.documentElement;
        this.currentTheme = localStorage.getItem('canarias-theme') || 'light';
        
        this.init();
    }
    
    init() {
        // Aplicar tema guardado
        this.applyTheme(this.currentTheme);
        
        // Event listener para el toggle
        this.themeToggle.addEventListener('click', () => this.toggleTheme());
        
        // Detectar preferencia del sistema
        this.detectSystemTheme();
    }
    
    toggleTheme() {
        const newTheme = this.currentTheme === 'light' ? 'dark' : 'light';
        this.applyTheme(newTheme);
        this.showToast(newTheme);
    }
    
    applyTheme(theme) {
        this.currentTheme = theme;
        this.html.setAttribute('data-theme', theme);
        this.themeToggle.setAttribute('data-theme', theme);
        
        // Actualizar botón - MOSTRAR EL PRÓXIMO MODO DISPONIBLE
        if (theme === 'dark') {
            // Si estamos en modo oscuro, el botón debe mostrar "Modo Claro" para cambiar
            this.themeToggle.querySelector('.theme-toggle__icon').textContent = '☀️';
            this.themeToggle.querySelector('.theme-toggle__text').textContent = 'Modo Claro';
        } else {
            // Si estamos en modo claro, el botón debe mostrar "Modo Oscuro" para cambiar
            this.themeToggle.querySelector('.theme-toggle__icon').textContent = '🌙';
            this.themeToggle.querySelector('.theme-toggle__text').textContent = 'Modo Oscuro';
        }
        
        // Guardar preferencia
        localStorage.setItem('canarias-theme', theme);
        
        // Dispatch evento personalizado
        window.dispatchEvent(new CustomEvent('themeChanged', { 
            detail: { theme: theme } 
        }));
    }
    
    detectSystemTheme() {
        // Solo aplicar si no hay preferencia guardada
        if (!localStorage.getItem('canarias-theme')) {
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            this.applyTheme(prefersDark ? 'dark' : 'light');
        }
        
        // Escuchar cambios en la preferencia del sistema
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (!localStorage.getItem('canarias-theme')) {
                this.applyTheme(e.matches ? 'dark' : 'light');
            }
        });
    }
    
    showToast(theme) {
        // Remover toast anterior si existe
        const existingToast = document.querySelector('.toast');
        if (existingToast) {
            existingToast.remove();
        }
        
        // Crear nuevo toast
        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.innerHTML = `
            <div class="toast__content">
                <span class="toast__icon">${theme === 'dark' ? '🌙' : '☀️'}</span>
                <span class="toast__text">Modo ${theme === 'dark' ? 'Oscuro' : 'Claro'} activado</span>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        // Mostrar toast
        setTimeout(() => toast.classList.add('show'), 100);
        
        // Ocultar toast después de 3 segundos
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
    
    // Métodos públicos para uso externo
    getCurrentTheme() {
        return this.currentTheme;
    }
    
    setTheme(theme) {
        if (theme === 'light' || theme === 'dark') {
            this.applyTheme(theme);
        }
    }
}

// Funcionalidad adicional para botones interactivos
class CanariasApp {
    constructor() {
        this.init();
    }
    
    init() {
        this.initButtons();
        this.initAnimations();
        this.initAuthButtons();
    }
    
    initAuthButtons() {
        // Botones de autenticación
        const loginBtn = document.getElementById('loginBtn');
        const registerBtn = document.getElementById('registerBtn');
        
        if (loginBtn) {
            loginBtn.addEventListener('click', () => this.handleAuthAction('login'));
        }
        
        if (registerBtn) {
            registerBtn.addEventListener('click', () => this.handleAuthAction('register'));
        }
    }
    
    handleAuthAction(action) {
        let message = '';
        let icon = '';
        
        if (action === 'login') {
            message = '👤 Abriendo formulario de inicio de sesión...';
            icon = '🔑';
            // Aquí puedes agregar la lógica real de login
            console.log('🔑 Acción de Login iniciada');
        } else if (action === 'register') {
            message = '✨ Abriendo formulario de registro...';
            icon = '📝';
            // Aquí puedes agregar la lógica real de registro
            console.log('📝 Acción de Registro iniciada');
        }
        
        this.showAuthToast(message, icon);
        
        // Simular navegación (reemplazar con lógica real)
        setTimeout(() => {
            if (action === 'login') {
                this.showAuthToast('🔐 Redirigiendo al portal de usuarios...', '🔄');
            } else {
                this.showAuthToast('🎉 Preparando formulario de registro...', '📋');
            }
        }, 1500);
    }
    
    showAuthToast(message, icon = '✨') {
        const existingToast = document.querySelector('.toast--auth');
        if (existingToast) existingToast.remove();
        
        const toast = document.createElement('div');
        toast.className = 'toast toast--auth';
        toast.style.bottom = '140px'; // Para no chocar con otros toasts
        toast.style.background = 'linear-gradient(135deg, var(--canarias-blue) 0%, var(--eco-ocean) 100%)';
        toast.style.color = 'var(--canarias-white)';
        toast.innerHTML = `
            <div class="toast__content">
                <span class="toast__icon">${icon}</span>
                <span class="toast__text">${message}</span>
            </div>
        `;
        
        document.body.appendChild(toast);
        setTimeout(() => toast.classList.add('show'), 100);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
    
    initButtons() {
        // Agregar eventos a todos los botones
        document.querySelectorAll('.btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.handleButtonClick(e));
        });
        
        // Agregar hover effects a las cards
        document.querySelectorAll('.card').forEach(card => {
            card.addEventListener('mouseenter', () => this.cardHover(card, true));
            card.addEventListener('mouseleave', () => this.cardHover(card, false));
        });
    }
    
    handleButtonClick(e) {
        const btn = e.currentTarget;
        const btnText = btn.textContent.trim();
        
        // Efecto de click
        btn.style.transform = 'translateY(0) scale(0.95)';
        setTimeout(() => {
            btn.style.transform = '';
        }, 150);
        
        // Mensajes específicos según el botón
        let message = '';
        switch(true) {
            case btnText.includes('Comprar'):
                message = '🛒 Redirigiendo a la tienda local...';
                break;
            case btnText.includes('Conocer'):
                message = '🌱 Cargando información sobre sostenibilidad...';
                break;
            case btnText.includes('Economía'):
                message = '♻️ Descubriendo la economía circular...';
                break;
            case btnText.includes('Ver Producto'):
                message = '👀 Mostrando detalles del producto...';
                break;
            case btnText.includes('Únete'):
                message = '🏝️ ¡Bienvenido al movimiento canario!';
                break;
            default:
                message = '✨ Acción realizada con éxito';
        }
        
        this.showActionToast(message);
    }
    
    cardHover(card, isHover) {
        if (isHover) {
            card.style.transform = 'translateY(-8px) scale(1.02)';
        } else {
            card.style.transform = 'translateY(0) scale(1)';
        }
    }
    
    showActionToast(message) {
        const existingToast = document.querySelector('.toast--action');
        if (existingToast) existingToast.remove();
        
        const toast = document.createElement('div');
        toast.className = 'toast toast--action';
        toast.style.bottom = '80px'; // Para no chocar con el toast del tema
        toast.innerHTML = `
            <div class="toast__content">
                <span class="toast__text">${message}</span>
            </div>
        `;
        
        document.body.appendChild(toast);
        setTimeout(() => toast.classList.add('show'), 100);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 2500);
    }
    
    initAnimations() {
        // Intersection Observer para animaciones al scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);
        
        // Observar elementos para animación
        document.querySelectorAll('.card, .badge, h2, h3').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(el);
        });
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    console.log('🏝️ Iniciando App Economía Circular Canarias...');
    
    // Inicializar gestión de temas
    window.themeManager = new ThemeManager();
    
    // Inicializar funcionalidades de la app
    window.canariasApp = new CanariasApp();
    
    // Inicializar menú móvil
    window.mobileMenuManager = new MobileMenuManager();
    
    // API pública para desarrollo
    window.CanariasAPI = {
        toggleTheme: () => window.themeManager.toggleTheme(),
        setTheme: (theme) => window.themeManager.setTheme(theme),
        getCurrentTheme: () => window.themeManager.getCurrentTheme(),
        triggerLogin: () => window.canariasApp.handleAuthAction('login'),
        triggerRegister: () => window.canariasApp.handleAuthAction('register'),
        showAuthToast: (message, icon) => window.canariasApp.showAuthToast(message, icon)
    };
    
    console.log('✅ App inicializada correctamente');
    console.log('💡 Usa CanariasAPI para controlar temas programáticamente');
});

// Escuchar cambios de tema para efectos adicionales
window.addEventListener('themeChanged', (e) => {
    console.log(`🎨 Tema cambiado a: ${e.detail.theme}`);
    
    // Aquí puedes agregar efectos adicionales al cambiar tema
    document.body.style.transform = 'scale(0.98)';
    setTimeout(() => {
        document.body.style.transform = 'scale(1)';
    }, 200);
});