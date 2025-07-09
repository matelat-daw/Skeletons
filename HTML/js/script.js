/**
 * JavaScript Ultra Optimizado - Mi Web Personal
 * Versión: 4.0 - Performance Focused
 * Tamaño: ~3KB minificado
 */

(function() {
    'use strict';
    
    // ===== CONFIGURACIÓN =====
    const CONFIG = {
        THROTTLE: 16, // 60fps
        TRANSITION: 400,
        SCROLL_BEHAVIOR: 'smooth'
    };
    
    // ===== CACHE DOM =====
    let DOM = {};
    
    // ===== UTILIDADES =====
    const $ = {
        // Throttle optimizado
        throttle: (fn, delay) => {
            let lastCall = 0;
            return (...args) => {
                const now = Date.now();
                if (now - lastCall >= delay) {
                    lastCall = now;
                    fn(...args);
                }
            };
        },
        
        // Query selector optimizado
        qs: sel => document.querySelector(sel),
        qsa: sel => document.querySelectorAll(sel),
        
        // Event listener optimizado
        on: (el, evt, fn, opts = {}) => {
            if (el) el.addEventListener(evt, fn, { passive: true, ...opts });
        },
        
        // Toggle class optimizado
        toggle: (el, cls) => el?.classList.toggle(cls)
    };
    
    // ===== MENÚ HAMBURGUESA =====
    const Menu = {
        isOpen: false,
        
        init() {
            const toggle = DOM.toggle;
            const menu = DOM.menu;
            
            if (!toggle || !menu) return;
            
            $.on(toggle, 'click', this.toggle.bind(this), { passive: false });
            
            // Cerrar con ESC
            $.on(document, 'keydown', e => {
                if (e.key === 'Escape' && this.isOpen) {
                    this.close();
                }
            });
            
            // Cerrar al hacer clic fuera
            $.on(document, 'click', e => {
                if (this.isOpen && !menu.contains(e.target) && !toggle.contains(e.target)) {
                    this.close();
                }
            });
        },
        
        toggle(e) {
            e?.preventDefault();
            this.isOpen ? this.close() : this.open();
        },
        
        open() {
            const { menu, toggle } = DOM;
            
            menu.classList.add('nav__menu--open');
            toggle.setAttribute('aria-expanded', 'true');
            this.isOpen = true;
            
            // Prevenir scroll del body
            document.body.style.overflow = 'hidden';
        },
        
        close() {
            const { menu, toggle } = DOM;
            
            menu.classList.remove('nav__menu--open');
            toggle.setAttribute('aria-expanded', 'false');
            this.isOpen = false;
            
            // Restaurar scroll del body
            document.body.style.overflow = '';
        }
    };
    
    // ===== NAVEGACIÓN SUAVE =====
    const Navigation = {
        init() {
            const links = DOM.links;
            if (!links.length) return;
            
            links.forEach(link => {
                $.on(link, 'click', this.handleClick.bind(this), { passive: false });
            });
            
            // Scroll spy básico
            $.on(window, 'scroll', $.throttle(this.updateActiveLink.bind(this), CONFIG.THROTTLE));
        },
        
        handleClick(e) {
            const href = e.target.getAttribute('href');
            
            if (!href?.startsWith('#')) return;
            
            e.preventDefault();
            Menu.close();
            
            const targetId = href.slice(1);
            const section = $.qs(`#${targetId}`);
            
            if (!section) return;
            
            // Navegación suave optimizada - incluir header y nav
            const header = $.qs('.header');
            const navHeight = DOM.nav?.offsetHeight || 0;
            const headerHeight = header?.offsetHeight || 0;
            const totalOffset = headerHeight + navHeight;
            const top = section.offsetTop - totalOffset;
            
            window.scrollTo({
                top: Math.max(0, top),
                behavior: CONFIG.SCROLL_BEHAVIOR
            });
            
            // Actualizar URL sin trigger scroll
            history.replaceState(null, '', href);
        },
        
        updateActiveLink() {
            const sections = $.qsa('section[id]');
            const header = $.qs('.header');
            const navHeight = DOM.nav?.offsetHeight || 0;
            const headerHeight = header?.offsetHeight || 0;
            const totalOffset = headerHeight + navHeight;
            const scrollPos = window.scrollY + totalOffset + 50;
            
            let current = '';
            
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.offsetHeight;
                
                // Determinar si estamos en esta sección
                if (scrollPos >= sectionTop && scrollPos < (sectionTop + sectionHeight)) {
                    current = section.id;
                }
            });
            
            // Si no hay sección activa, usar la primera
            if (!current && sections.length > 0) {
                current = sections[0].id;
            }
            
            // Actualizar link activo
            DOM.links.forEach(link => {
                const href = link.getAttribute('href')?.slice(1);
                link.classList.toggle('nav__link--active', href === current);
            });
        }
    };
    
    // ===== AUTENTICACIÓN =====
    const Auth = {
        init() {
            // Registro
            $.qsa('[aria-label*="Registr"]').forEach(btn => {
                $.on(btn, 'click', () => {
                    Menu.close();
                    this.showMessage('Función de registro - En desarrollo');
                });
            });
            
            // Login
            $.qsa('[aria-label*="sesión"], [aria-label*="Login"]').forEach(btn => {
                $.on(btn, 'click', () => {
                    Menu.close();
                    this.showMessage('Función de login - En desarrollo');
                });
            });
        },
        
        showMessage(msg) {
            // Toast notification simple
            const toast = document.createElement('div');
            toast.textContent = msg;
            toast.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: var(--primary);
                color: white;
                padding: 1rem 1.5rem;
                border-radius: var(--radius);
                box-shadow: var(--shadow-lg);
                z-index: 9999;
                font-weight: 500;
                transform: translateX(100%);
                transition: transform 0.3s ease;
            `;
            
            document.body.appendChild(toast);
            
            // Animación de entrada
            requestAnimationFrame(() => {
                toast.style.transform = 'translateX(0)';
            });
            
            // Auto remove
            setTimeout(() => {
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    };
    
    // ===== PERFORMANCE =====
    const Performance = {
        init() {
            // Lazy loading para imágenes
            this.lazyLoadImages();
            
            // Preload critical resources
            this.preloadCritical();
            
            // Optimize animations
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                document.documentElement.style.setProperty('--transition', '0.01ms');
                document.documentElement.style.setProperty('--transition-slow', '0.01ms');
            }
        },
        
        lazyLoadImages() {
            const images = $.qsa('img[data-src]');
            
            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            img.src = img.dataset.src;
                            img.removeAttribute('data-src');
                            observer.unobserve(img);
                        }
                    });
                }, { rootMargin: '50px' });
                
                images.forEach(img => observer.observe(img));
            } else {
                // Fallback
                images.forEach(img => {
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                });
            }
        },
        
        preloadCritical() {
            // Preload next sections
            const prefetch = document.createElement('link');
            prefetch.rel = 'prefetch';
            prefetch.href = '#about';
            document.head.appendChild(prefetch);
        }
    };
    
    // ===== THEME TOGGLE =====
    const ThemeToggle = {
        init() {
            this.toggleButtons = $.qsa('#theme-toggle, .theme-toggle-mobile');
            this.currentTheme = this.getStoredTheme() || this.getSystemTheme();
            
            if (!this.toggleButtons.length) return;
            
            // Aplicar tema inicial
            this.applyTheme(this.currentTheme);
            this.updateToggleIcons();
            
            // Event listeners
            this.toggleButtons.forEach(btn => {
                $.on(btn, 'click', this.toggle.bind(this), { passive: false });
            });
            
            // Escuchar cambios en preferencia del sistema
            const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
            mediaQuery.addEventListener('change', () => {
                if (!this.getStoredTheme()) {
                    this.currentTheme = this.getSystemTheme();
                    this.applyTheme(this.currentTheme);
                    this.updateToggleIcons();
                }
            });
        },
        
        getSystemTheme() {
            return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        },
        
        getStoredTheme() {
            return localStorage.getItem('theme');
        },
        
        storeTheme(theme) {
            localStorage.setItem('theme', theme);
        },
        
        toggle() {
            console.log('Theme toggle clicked! Current theme:', this.currentTheme);
            this.currentTheme = this.currentTheme === 'dark' ? 'light' : 'dark';
            this.applyTheme(this.currentTheme);
            this.storeTheme(this.currentTheme);
            this.updateToggleIcons();
            console.log('Theme changed to:', this.currentTheme);
            
            // Haptic feedback en móviles
            if (navigator.vibrate) {
                navigator.vibrate(50);
            }
        },
        
        toggle() {
            this.currentTheme = this.currentTheme === 'dark' ? 'light' : 'dark';
            this.applyTheme(this.currentTheme);
            this.storeTheme(this.currentTheme);
            this.updateToggleIcons();
            
            // Haptic feedback en móviles
            if (navigator.vibrate) {
                navigator.vibrate(50);
            }
        },
        
        applyTheme(theme) {
            const body = document.body;
            body.classList.remove('theme-light', 'theme-dark');
            body.classList.add(`theme-${theme}`);
            
            // Actualizar meta theme-color
            const themeColorMeta = document.querySelector('meta[name="theme-color"]');
            if (themeColorMeta) {
                themeColorMeta.content = theme === 'dark' ? '#0d0d0d' : '#0078d7';
            }
        },
        
        updateToggleIcons() {
            this.toggleButtons.forEach(btn => {
                const lightIcon = btn.querySelector('.theme-icon--light');
                const darkIcon = btn.querySelector('.theme-icon--dark');
                
                if (lightIcon && darkIcon) {
                    // Lógica corregida: mostrar el icono del tema AL QUE puedes cambiar
                    if (this.currentTheme === 'dark') {
                        // Estamos en modo oscuro, mostrar sol para cambiar a claro
                        darkIcon.classList.remove('active');
                        lightIcon.classList.add('active');
                        btn.setAttribute('aria-label', 'Cambiar a modo claro');
                    } else {
                        // Estamos en modo claro, mostrar luna para cambiar a oscuro
                        lightIcon.classList.remove('active');
                        darkIcon.classList.add('active');
                        btn.setAttribute('aria-label', 'Cambiar a modo oscuro');
                    }
                }
            });
        }
    };
    
    // ===== INICIALIZACIÓN =====
    const App = {
        init() {
            // Cache DOM elements
            DOM = {
                nav: $.qs('.nav'),
                toggle: $.qs('#menu-toggle'),
                menu: $.qs('#menu'),
                links: $.qsa('.nav__link'),
                body: document.body
            };
            
            // Verificar elementos críticos
            if (!DOM.nav || !DOM.toggle || !DOM.menu) {
                console.warn('Elementos críticos no encontrados');
                return;
            }
            
            // Inicializar módulos
            Menu.init();
            Navigation.init();
            Auth.init();
            ThemeToggle.init();
            Performance.init();
            
            // Set initial state
            Navigation.updateActiveLink();
            
            // App ready
            document.body.classList.add('app-ready');
            console.log('🚀 App inicializada - Versión 4.0 Ultra Optimizada');
        }
    };
    
    // ===== INICIO =====
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', App.init);
    } else {
        App.init();
    }
    
    // Export para testing
    window.App = { Menu, Navigation, Auth, ThemeToggle };
    
})();