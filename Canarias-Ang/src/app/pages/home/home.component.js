// Home Component - Economía Circular Canarias
class HomeComponent {
    constructor() {
        this.template = `
            <div class="home-component">
                <section class="hero-section text-center mb-2">
                    <div class="card" style="background: linear-gradient(135deg, var(--canarias-blue), var(--canarias-ocean)); color: white;">
                        <h1>🏝️ Bienvenido a la Economía Circular de Canarias</h1>
                        <p class="mt-1">
                            Descubre productos locales y sostenibles que impulsan nuestra economía circular
                        </p>
                        <div class="mt-1">
                            <a href="#/productos" class="btn btn-secondary" data-navigate="/productos">
                                🛒 Explorar Productos
                            </a>
                            <a href="#/economia-circular" class="btn btn-success" data-navigate="/economia-circular">
                                ♻️ Conocer Más
                            </a>
                        </div>
                    </div>
                </section>

                <section class="stats-section mb-2">
                    <h2 class="text-center mb-1">📊 Impacto de la Economía Circular</h2>
                    <div class="grid grid-3">
                        <div class="card text-center">
                            <h3 style="color: var(--canarias-blue);">500+</h3>
                            <p>Productores Locales</p>
                        </div>
                        <div class="card text-center">
                            <h3 style="color: var(--canarias-green);">1,200</h3>
                            <p>Productos Sostenibles</p>
                        </div>
                        <div class="card text-center">
                            <h3 style="color: var(--canarias-ocean);">85%</h3>
                            <p>Reducción de Emisiones</p>
                        </div>
                    </div>
                </section>

                <section class="featured-products mb-2">
                    <h2 class="text-center mb-1">⭐ Productos Destacados</h2>
                    <div class="grid grid-2">
                        <div class="card">
                            <h3>🧀 Queso Majorero</h3>
                            <p>
                                Auténtico queso de cabra de Fuerteventura, elaborado de forma tradicional 
                                por productores locales que mantienen vivas nuestras tradiciones.
                            </p>
                            <div class="mt-1">
                                <span style="background: var(--canarias-green); color: white; padding: 0.3rem 0.8rem; border-radius: 15px; font-size: 0.9rem;">
                                    🏝️ Origen Canario
                                </span>
                            </div>
                            <button class="btn btn-primary mt-1">Ver Producto</button>
                        </div>
                        
                        <div class="card">
                            <h3>🍌 Plátano de Canarias</h3>
                            <p>
                                Cultivado de manera sostenible en nuestras islas, el plátano canario 
                                es símbolo de calidad y compromiso con el medio ambiente.
                            </p>
                            <div class="mt-1">
                                <span style="background: var(--canarias-yellow); color: var(--canarias-dark); padding: 0.3rem 0.8rem; border-radius: 15px; font-size: 0.9rem;">
                                    ♻️ Sostenible
                                </span>
                            </div>
                            <button class="btn btn-success mt-1">Comprar Ahora</button>
                        </div>
                    </div>
                </section>

                <section class="cta-section">
                    <div class="card text-center" style="background: linear-gradient(135deg, var(--canarias-yellow), #fff8dc);">
                        <h2 style="color: var(--canarias-dark);">"Si compras aquí, vuelve a Ti"</h2>
                        <p style="color: var(--canarias-dark);" class="mt-1">
                            Apoya la economía local canaria y contribuye a un futuro más sostenible
                        </p>
                        <a href="#/sobre-nosotros" class="btn btn-primary mt-1" data-navigate="/sobre-nosotros">
                            ❤️ Únete al Movimiento
                        </a>
                    </div>
                </section>
            </div>
        `;
    }

    render() {
        return this.template;
    }

    afterRender() {
        this.initializeNavigation();
        this.animateStats();
    }

    initializeNavigation() {
        const navLinks = this.getElement().querySelectorAll('[data-navigate]');
        
        navLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const route = link.getAttribute('data-navigate');
                window.appRouter.navigate(route);
            });
        });
    }

    animateStats() {
        const statsNumbers = this.getElement().querySelectorAll('.stats-section h3');
        
        statsNumbers.forEach((stat, index) => {
            setTimeout(() => {
                stat.style.transform = 'scale(1.1)';
                stat.style.transition = 'transform 0.5s ease';
                
                setTimeout(() => {
                    stat.style.transform = 'scale(1)';
                }, 500);
            }, index * 200);
        });
    }

    getElement() {
        return document.querySelector('.home-component');
    }
}

// Exportar el componente
window.HomeComponent = HomeComponent;
