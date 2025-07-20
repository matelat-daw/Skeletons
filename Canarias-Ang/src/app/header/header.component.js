// Header Component - Economía Circular Canarias
class HeaderComponent {
    constructor() {
        this.template = `
            <header>
                <div class="header-content">
                    <a href="#/" class="logo" data-navigate="/">
                        🏝️ Economía Circular Canarias
                    </a>
                    <button class="theme-toggle" id="themeToggle">
                        🌙 Modo Oscuro
                    </button>
                </div>
            </header>
        `;
    }

    render() {
        return this.template;
    }

    afterRender() {
        this.initializeThemeToggle();
        this.initializeNavigation();
    }

    initializeThemeToggle() {
        const themeToggle = document.getElementById('themeToggle');
        if (!themeToggle) return;

        // Cargar tema guardado
        const savedTheme = localStorage.getItem('canarias-theme') || 'light';
        this.applyTheme(savedTheme);
        this.updateThemeToggleText(savedTheme);

        themeToggle.addEventListener('click', () => {
            const currentTheme = document.body.getAttribute('data-theme') || 'light';
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            
            this.applyTheme(newTheme);
            localStorage.setItem('canarias-theme', newTheme);
            this.updateThemeToggleText(newTheme);
        });
    }

    applyTheme(theme) {
        document.body.setAttribute('data-theme', theme);
        
        if (theme === 'dark') {
            document.documentElement.style.setProperty('--canarias-light-gray', '#1a1a1a');
            document.documentElement.style.setProperty('--canarias-white', '#2d2d2d');
            document.documentElement.style.setProperty('--canarias-dark', '#ffffff');
            document.documentElement.style.setProperty('--canarias-border', '#404040');
        } else {
            document.documentElement.style.setProperty('--canarias-light-gray', '#F8F9FA');
            document.documentElement.style.setProperty('--canarias-white', '#FFFFFF');
            document.documentElement.style.setProperty('--canarias-dark', '#2C3E50');
            document.documentElement.style.setProperty('--canarias-border', '#E9ECEF');
        }
    }

    updateThemeToggleText(theme) {
        const themeToggle = document.getElementById('themeToggle');
        if (themeToggle) {
            themeToggle.textContent = theme === 'light' ? '🌙 Modo Oscuro' : '☀️ Modo Claro';
        }
    }

    initializeNavigation() {
        const logoLink = document.querySelector('.logo[data-navigate]');
        if (logoLink) {
            logoLink.addEventListener('click', (e) => {
                e.preventDefault();
                const route = logoLink.getAttribute('data-navigate');
                window.appRouter.navigate(route);
            });
        }
    }
}

// Exportar el componente
window.HeaderComponent = HeaderComponent;
