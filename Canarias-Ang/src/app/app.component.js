// App Component Principal - Economía Circular Canarias
class AppComponent {
    constructor() {
        this.headerComponent = new HeaderComponent();
        this.navComponent = new NavComponent();
        this.footerComponent = new FooterComponent();
        
        this.template = `
            <div class="app-container">
                ${this.headerComponent.render()}
                ${this.navComponent.render()}
                <main>
                    <div id="router-outlet" class="router-outlet">
                        <!-- El contenido de las rutas se cargará aquí -->
                    </div>
                </main>
                ${this.footerComponent.render()}
            </div>
        `;
    }

    render() {
        return this.template;
    }

    init() {
        // Renderizar la aplicación en el DOM
        const appRoot = document.getElementById('app-root');
        if (appRoot) {
            appRoot.innerHTML = this.render();
            
            // Ejecutar afterRender de componentes
            this.headerComponent.afterRender();
            this.navComponent.afterRender();
            this.footerComponent.afterRender();
            
            // Inicializar el router
            window.appRouter = new AppRouter();
        }
    }
}

// Exportar el componente principal
window.AppComponent = AppComponent;
