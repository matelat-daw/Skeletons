/**
 * Guitarras Canvas - JavaScript
 * Funcionalidad básica compatible con Canvas Apps
 */

(function() {
    'use strict';
    
    // ===== CONFIGURACIÓN =====
    const CONFIG = {
        SCROLL_THRESHOLD: 100,
        TRANSITION_DURATION: 300
    };
    
    // ===== UTILIDADES =====
    const $ = {
        qs: selector => document.querySelector(selector),
        qsa: selector => document.querySelectorAll(selector),
        on: (element, event, handler) => {
            if (element) {
                element.addEventListener(event, handler);
            }
        }
    };
    
    // ===== NAVEGACIÓN SUAVE =====
    const SmoothScroll = {
        init() {
            const navLinks = $.qsa('.nav-link[href^="#"]');
            navLinks.forEach(link => {
                $.on(link, 'click', this.handleClick.bind(this));
            });
        },
        
        handleClick(e) {
            e.preventDefault();
            const targetId = e.target.getAttribute('href').slice(1);
            const targetElement = $.qs(`#${targetId}`);
            
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }
    };
    
    // ===== HEADER SCROLL EFFECT =====
    const HeaderScroll = {
        init() {
            this.header = $.qs('.header');
            if (this.header) {
                $.on(window, 'scroll', this.handleScroll.bind(this));
            }
        },
        
        handleScroll() {
            const scrollY = window.scrollY;
            
            if (scrollY > CONFIG.SCROLL_THRESHOLD) {
                this.header.style.background = 'rgba(26, 26, 46, 0.98)';
                this.header.style.backdropFilter = 'blur(15px)';
            } else {
                this.header.style.background = 'rgba(26, 26, 46, 0.95)';
                this.header.style.backdropFilter = 'blur(10px)';
            }
        }
    };
    
    // ===== BOTONES CTA =====
    const CTAButtons = {
        init() {
            const buttons = $.qsa('.btn');
            buttons.forEach(button => {
                $.on(button, 'click', this.handleClick.bind(this));
            });
        },
        
        handleClick(e) {
            const button = e.target;
            const text = button.textContent;
            
            if (text === 'Explora Ahora') {
                this.showMessage('¡Explorando guitarras personalizadas!');
                // Aquí puedes agregar la lógica específica para Canvas
                this.triggerCanvasAction('explore_guitars');
            }
        },
        
        showMessage(message) {
            // Toast notification simple
            const toast = document.createElement('div');
            toast.textContent = message;
            toast.style.cssText = `
                position: fixed;
                top: 100px;
                right: 20px;
                background: var(--primary-turquoise);
                color: var(--dark-navy);
                padding: 1rem 1.5rem;
                border-radius: 8px;
                box-shadow: 0 8px 25px rgba(78, 205, 196, 0.3);
                z-index: 9999;
                font-weight: 500;
                transform: translateX(100%);
                transition: transform 0.3s ease;
            `;
            
            document.body.appendChild(toast);
            
            // Animación de entrada
            setTimeout(() => {
                toast.style.transform = 'translateX(0)';
            }, 100);
            
            // Auto remove
            setTimeout(() => {
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        },
        
        // Función para integrar con Canvas Apps
        triggerCanvasAction(action) {
            // Esta función puede ser llamada desde Canvas Apps
            console.log('Canvas Action Triggered:', action);
            
            // Ejemplo de cómo enviar datos a Canvas
            if (window.parent && window.parent.postMessage) {
                window.parent.postMessage({
                    type: 'CANVAS_ACTION',
                    action: action,
                    timestamp: new Date().toISOString(),
                    source: 'guitar-luthier-web'
                }, '*');
            }
            
            // También puedes almacenar en localStorage para Canvas
            localStorage.setItem('lastAction', JSON.stringify({
                action: action,
                timestamp: new Date().toISOString()
            }));
        }
    };
    
    // ===== MOBILE MENU =====
    const MobileMenu = {
        init() {
            this.toggle = $.qs('.mobile-toggle');
            this.nav = $.qs('.header__nav');
            this.isOpen = false;
            
            if (this.toggle && this.nav) {
                $.on(this.toggle, 'click', this.toggleMenu.bind(this));
            }
        },
        
        toggleMenu() {
            this.isOpen = !this.isOpen;
            
            if (this.isOpen) {
                this.openMenu();
            } else {
                this.closeMenu();
            }
        },
        
        openMenu() {
            this.nav.style.display = 'flex';
            this.nav.style.position = 'absolute';
            this.nav.style.top = '100%';
            this.nav.style.left = '0';
            this.nav.style.right = '0';
            this.nav.style.background = 'rgba(26, 26, 46, 0.98)';
            this.nav.style.backdropFilter = 'blur(15px)';
            this.nav.style.flexDirection = 'column';
            this.nav.style.padding = '1rem';
            this.nav.style.borderTop = '1px solid rgba(255, 255, 255, 0.1)';
            
            const navList = this.nav.querySelector('.nav-list');
            if (navList) {
                navList.style.flexDirection = 'column';
                navList.style.gap = '1rem';
                navList.style.width = '100%';
            }
        },
        
        closeMenu() {
            this.nav.style.display = 'none';
        }
    };
    
    // ===== INTERSECTION OBSERVER =====
    const AnimationObserver = {
        init() {
            if ('IntersectionObserver' in window) {
                this.observer = new IntersectionObserver(
                    this.handleIntersection.bind(this),
                    { threshold: 0.1, rootMargin: '50px' }
                );
                
                const animatedElements = $.qsa('.hero__badge, .hero__title, .hero__description, .hero__cta, .hero__image');
                animatedElements.forEach(el => this.observer.observe(el));
            }
        },
        
        handleIntersection(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }
    };
    
    // ===== CANVAS INTEGRATION =====
    const CanvasIntegration = {
        init() {
            // Escuchar mensajes de Canvas Apps
            $.on(window, 'message', this.handleCanvasMessage.bind(this));
            
            // Notificar a Canvas que la página está lista
            this.notifyCanvasReady();
        },
        
        handleCanvasMessage(event) {
            // Validar origen si es necesario
            const { data } = event;
            
            if (data.type === 'CANVAS_COMMAND') {
                switch (data.command) {
                    case 'UPDATE_CONTENT':
                        this.updateContent(data.payload);
                        break;
                    case 'TRIGGER_ACTION':
                        CTAButtons.triggerCanvasAction(data.payload.action);
                        break;
                    case 'CHANGE_THEME':
                        this.changeTheme(data.payload.theme);
                        break;
                }
            }
        },
        
        updateContent(payload) {
            // Actualizar contenido dinámicamente
            if (payload.title) {
                const title = $.qs('.hero__title');
                if (title) title.textContent = payload.title;
            }
            
            if (payload.description) {
                const description = $.qs('.hero__description');
                if (description) description.textContent = payload.description;
            }
        },
        
        changeTheme(theme) {
            document.body.setAttribute('data-theme', theme);
        },
        
        notifyCanvasReady() {
            if (window.parent && window.parent.postMessage) {
                window.parent.postMessage({
                    type: 'WEB_READY',
                    timestamp: new Date().toISOString(),
                    capabilities: ['smooth-scroll', 'cta-actions', 'mobile-responsive']
                }, '*');
            }
        },
        
        // Métodos públicos para Canvas
        getPageData() {
            return {
                title: $.qs('.hero__title')?.textContent,
                description: $.qs('.hero__description')?.textContent,
                currentAction: localStorage.getItem('lastAction')
            };
        },
        
        performAction(actionType, data = {}) {
            switch (actionType) {
                case 'scroll-to-section':
                    SmoothScroll.handleClick({ 
                        preventDefault: () => {}, 
                        target: { getAttribute: () => `#${data.section}` } 
                    });
                    break;
                case 'show-notification':
                    CTAButtons.showMessage(data.message);
                    break;
                case 'trigger-cta':
                    CTAButtons.triggerCanvasAction(data.action);
                    break;
            }
        }
    };
    
    // ===== INICIALIZACIÓN =====
    const App = {
        init() {
            console.log('🎸 Guitar Luthier Web App - Canvas Ready');
            
            // Inicializar todos los módulos
            SmoothScroll.init();
            HeaderScroll.init();
            CTAButtons.init();
            MobileMenu.init();
            AnimationObserver.init();
            CanvasIntegration.init();
            
            // Hacer métodos disponibles globalmente para Canvas
            window.GuitarApp = {
                getPageData: CanvasIntegration.getPageData,
                performAction: CanvasIntegration.performAction,
                CTAButtons: CTAButtons,
                SmoothScroll: SmoothScroll
            };
            
            // Marcar como lista
            document.body.classList.add('app-ready');
        }
    };
    
    // ===== INICIO =====
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', App.init);
    } else {
        App.init();
    }
    
})();
