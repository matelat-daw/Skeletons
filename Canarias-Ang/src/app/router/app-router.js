// Router - Sistema de enrutamiento estilo Angular
class AppRouter {
    constructor() {
        this.routes = {};
        this.currentRoute = '';
        this.init();
    }

    // Definir rutas de la aplicación
    defineRoutes() {
        this.routes = {
            '/': () => new HomeComponent(),
            '/productos': () => new ProductosComponent(),
            '/economia-circular': () => new EconomiaCircularComponent(),
            '/sobre-nosotros': () => new SobreNosotrosComponent(),
            '/contacto': () => new ContactoComponent(),
            '/login': () => new LoginComponent(),
            '/register': () => new RegisterComponent(),
            '/profile': () => new ProfileComponent(),
            '/orders': () => new OrdersComponent(),
            '/settings': () => new SettingsComponent()
        };
    }

    init() {
        this.defineRoutes();
        
        // Escuchar cambios en el hash
        window.addEventListener('hashchange', () => {
            this.handleRouteChange();
        });

        // Cargar ruta inicial
        this.handleRouteChange();
    }

    navigate(route) {
        window.location.hash = route;
    }

    handleRouteChange() {
        const hash = window.location.hash.slice(1) || '/';
        this.loadRoute(hash);
    }

    loadRoute(route) {
        const routeHandler = this.routes[route];
        
        if (routeHandler) {
            this.currentRoute = route;
            const component = routeHandler();
            this.renderComponent(component);
            
            // Actualizar navegación activa
            if (window.NavComponent) {
                window.NavComponent.updateActiveLink();
            }
        } else {
            // Ruta no encontrada - redirigir a home
            this.navigate('/');
        }
    }

    renderComponent(component) {
        const routerOutlet = document.getElementById('router-outlet');
        if (routerOutlet && component) {
            routerOutlet.innerHTML = component.render();
            
            // Ejecutar lógica post-render del componente
            if (component.afterRender) {
                setTimeout(() => component.afterRender(), 10);
            }
        }
    }

    getCurrentRoute() {
        return this.currentRoute;
    }
}

// Crear componentes simples para las rutas faltantes
class EconomiaCircularComponent {
    constructor() {
        this.template = `
            <div class="economia-circular-component">
                <section class="hero text-center mb-2">
                    <div class="card" style="background: linear-gradient(135deg, var(--canarias-green), var(--canarias-ocean)); color: white;">
                        <h1>♻️ Economía Circular en Canarias</h1>
                        <p class="mt-1">
                            Transformando residuos en recursos para un futuro sostenible
                        </p>
                    </div>
                </section>

                <section class="principios mb-2">
                    <h2 class="text-center mb-1">🔄 Principios de la Economía Circular</h2>
                    <div class="grid grid-3">
                        <div class="card text-center">
                            <h3>🎯 Reducir</h3>
                            <p>Minimizar el consumo de recursos naturales y la generación de residuos</p>
                        </div>
                        <div class="card text-center">
                            <h3>🔄 Reutilizar</h3>
                            <p>Dar nueva vida a productos y materiales en lugar de desecharlos</p>
                        </div>
                        <div class="card text-center">
                            <h3>♻️ Reciclar</h3>
                            <p>Transformar residuos en nuevos productos útiles</p>
                        </div>
                    </div>
                </section>

                <section class="impacto">
                    <div class="card">
                        <h2 class="text-center">🌍 Nuestro Impacto en Canarias</h2>
                        <div class="grid grid-2 mt-1">
                            <div>
                                <h3>🏝️ Beneficios Locales</h3>
                                <ul>
                                    <li>Creación de empleos verdes</li>
                                    <li>Reducción de dependencia exterior</li>
                                    <li>Fortalecimiento de la economía local</li>
                                    <li>Preservación del medio ambiente insular</li>
                                </ul>
                            </div>
                            <div>
                                <h3>📊 Resultados Conseguidos</h3>
                                <ul>
                                    <li>85% reducción en emisiones de CO2</li>
                                    <li>500+ empleos verdes creados</li>
                                    <li>70% menos residuos a vertedero</li>
                                    <li>1,200 productos circulares en mercado</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        `;
    }

    render() {
        return this.template;
    }

    afterRender() {
        // Animación de entrada para las tarjetas
        const cards = document.querySelectorAll('.economia-circular-component .card');
        cards.forEach((card, index) => {
            setTimeout(() => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'all 0.5s ease';
                
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 100);
            }, index * 200);
        });
    }
}

class SobreNosotrosComponent {
    constructor() {
        this.template = `
            <div class="sobre-nosotros-component">
                <section class="hero text-center mb-2">
                    <div class="card" style="background: linear-gradient(135deg, var(--canarias-yellow), #fff8dc);">
                        <h1 style="color: var(--canarias-dark);">ℹ️ Sobre Nosotros</h1>
                        <p style="color: var(--canarias-dark);" class="mt-1">
                            Conoce más sobre nuestra misión de impulsar la economía circular en Canarias
                        </p>
                    </div>
                </section>

                <section class="mision mb-2">
                    <div class="grid grid-2">
                        <div class="card">
                            <h2>🎯 Nuestra Misión</h2>
                            <p>
                                Promover un modelo económico sostenible en las Islas Canarias que 
                                fortalezca la economía local, proteja nuestro entorno único y 
                                mejore la calidad de vida de todos los canarios.
                            </p>
                        </div>
                        <div class="card">
                            <h2>👁️ Nuestra Visión</h2>
                            <p>
                                Ser el referente en economía circular para territorios insulares, 
                                demostrando que es posible un desarrollo sostenible que respete 
                                nuestros límites naturales y potencie nuestras fortalezas.
                            </p>
                        </div>
                    </div>
                </section>

                <section class="valores mb-2">
                    <h2 class="text-center mb-1">💎 Nuestros Valores</h2>
                    <div class="grid grid-3">
                        <div class="card text-center">
                            <h3>🏝️ Identidad Canaria</h3>
                            <p>Valoramos y preservamos nuestra cultura, tradiciones y productos únicos</p>
                        </div>
                        <div class="card text-center">
                            <h3>🌱 Sostenibilidad</h3>
                            <p>Comprometidos con prácticas que respeten nuestro frágil ecosistema insular</p>
                        </div>
                        <div class="card text-center">
                            <h3>🤝 Colaboración</h3>
                            <p>Trabajamos juntos, productores y consumidores, por un objetivo común</p>
                        </div>
                    </div>
                </section>

                <section class="equipo">
                    <div class="card text-center">
                        <h2>👥 Nuestro Equipo</h2>
                        <p class="mt-1">
                            Somos un equipo multidisciplinar de profesionales canarios apasionados por 
                            el desarrollo sostenible de nuestras islas. Desde economistas hasta biólogos 
                            marinos, todos trabajamos con un objetivo común: hacer de Canarias un ejemplo 
                            mundial de economía circular insular.
                        </p>
                        <p class="mt-1" style="color: var(--canarias-blue); font-weight: bold;">
                            "Si compras aquí, vuelve a Ti" - no es solo nuestro lema, es nuestra filosofía de vida.
                        </p>
                    </div>
                </section>
            </div>
        `;
    }

    render() {
        return this.template;
    }

    afterRender() {
        // Agregar efectos de hover a las tarjetas de valores
        const valorCards = document.querySelectorAll('.valores .card');
        valorCards.forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.style.transform = 'scale(1.05)';
                card.style.transition = 'transform 0.3s ease';
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'scale(1)';
            });
        });
    }
}

class ContactoComponent {
    constructor() {
        this.template = `
            <div class="contacto-component">
                <section class="hero text-center mb-2">
                    <div class="card" style="background: linear-gradient(135deg, var(--canarias-blue), var(--canarias-ocean)); color: white;">
                        <h1>📞 Contacto</h1>
                        <p class="mt-1">
                            ¿Tienes preguntas? ¡Nos encantaría escucharte!
                        </p>
                    </div>
                </section>

                <section class="contacto-info mb-2">
                    <div class="grid grid-2">
                        <div class="card">
                            <h2>📍 Información de Contacto</h2>
                            <div class="mt-1">
                                <p><strong>📧 Email:</strong> info@economiacircularcanarias.com</p>
                                <p><strong>📱 Teléfono:</strong> +34 922 123 456</p>
                                <p><strong>📍 Dirección:</strong> 
                                   Calle Economía Circular, 123<br>
                                   38001 Santa Cruz de Tenerife<br>
                                   Islas Canarias, España
                                </p>
                                <p><strong>🕒 Horario:</strong> 
                                   Lunes a Viernes: 9:00 - 18:00<br>
                                   Sábados: 9:00 - 14:00
                                </p>
                            </div>
                        </div>
                        
                        <div class="card">
                            <h2>📝 Envíanos un Mensaje</h2>
                            <form id="contactForm" class="mt-1">
                                <div class="form-group mb-1">
                                    <label for="nombre">Nombre:</label>
                                    <input type="text" id="nombre" name="nombre" required style="width: 100%; padding: 0.5rem; margin-top: 0.5rem; border: 2px solid var(--canarias-border); border-radius: 5px;">
                                </div>
                                
                                <div class="form-group mb-1">
                                    <label for="email">Email:</label>
                                    <input type="email" id="email" name="email" required style="width: 100%; padding: 0.5rem; margin-top: 0.5rem; border: 2px solid var(--canarias-border); border-radius: 5px;">
                                </div>
                                
                                <div class="form-group mb-1">
                                    <label for="asunto">Asunto:</label>
                                    <select id="asunto" name="asunto" required style="width: 100%; padding: 0.5rem; margin-top: 0.5rem; border: 2px solid var(--canarias-border); border-radius: 5px;">
                                        <option value="">Selecciona un asunto</option>
                                        <option value="productos">Consulta sobre productos</option>
                                        <option value="colaboracion">Colaboración como productor</option>
                                        <option value="economia-circular">Información sobre economía circular</option>
                                        <option value="otro">Otro</option>
                                    </select>
                                </div>
                                
                                <div class="form-group mb-1">
                                    <label for="mensaje">Mensaje:</label>
                                    <textarea id="mensaje" name="mensaje" rows="4" required style="width: 100%; padding: 0.5rem; margin-top: 0.5rem; border: 2px solid var(--canarias-border); border-radius: 5px; resize: vertical;"></textarea>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    📧 Enviar Mensaje
                                </button>
                            </form>
                        </div>
                    </div>
                </section>

                <section class="redes-sociales">
                    <div class="card text-center">
                        <h2>🌐 Síguenos en Redes Sociales</h2>
                        <div class="mt-1" style="display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap;">
                            <a href="#" class="btn btn-primary">📘 Facebook</a>
                            <a href="#" class="btn btn-success">📷 Instagram</a>
                            <a href="#" class="btn btn-primary">🐦 Twitter</a>
                            <a href="#" class="btn btn-secondary">💼 LinkedIn</a>
                        </div>
                        <p class="mt-1">
                            Mantente al día con nuestras últimas noticias y productos
                        </p>
                    </div>
                </section>
            </div>
        `;
    }

    render() {
        return this.template;
    }

    afterRender() {
        const form = document.getElementById('contactForm');
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleFormSubmit(e);
            });
        }
    }

    handleFormSubmit(e) {
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData);
        
        // Simular envío del formulario
        alert(`¡Gracias ${data.nombre}! 
        
Hemos recibido tu mensaje sobre: ${data.asunto}

Te responderemos a ${data.email} en las próximas 24-48 horas.

¡Gracias por contactar con Economía Circular Canarias!`);
        
        // Limpiar formulario
        e.target.reset();
    }
}

// Exportar clases
window.AppRouter = AppRouter;
window.EconomiaCircularComponent = EconomiaCircularComponent;
window.SobreNosotrosComponent = SobreNosotrosComponent;
window.ContactoComponent = ContactoComponent;
