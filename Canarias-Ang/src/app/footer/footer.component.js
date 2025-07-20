// Footer Component - Economía Circular Canarias
class FooterComponent {
    constructor() {
        this.template = `
            <footer>
                <div class="footer-content">
                    <p>💛 Hecho con amor en las Islas Canarias 💙</p>
                    <p>"Si compras aquí, vuelve a Ti"</p>
                    <div class="islands-list">
                        <span class="island-badge">🏝️ Tenerife</span>
                        <span class="island-badge">🏝️ Gran Canaria</span>
                        <span class="island-badge">🏝️ Lanzarote</span>
                        <span class="island-badge">🏝️ Fuerteventura</span>
                        <span class="island-badge">🏝️ La Palma</span>
                        <span class="island-badge">🏝️ La Gomera</span>
                        <span class="island-badge">🏝️ El Hierro</span>
                    </div>
                    <p class="mt-1">
                        <small>© ${new Date().getFullYear()} Economía Circular Canarias. Todos los derechos reservados.</small>
                    </p>
                </div>
            </footer>
        `;
    }

    render() {
        return this.template;
    }

    afterRender() {
        // Aquí puedes agregar cualquier lógica adicional del footer
        this.initializeIslandBadges();
    }

    initializeIslandBadges() {
        const islandBadges = document.querySelectorAll('.island-badge');
        
        islandBadges.forEach((badge, index) => {
            // Agregar animación escalonada
            badge.style.animationDelay = `${index * 0.1}s`;
            
            // Agregar efecto hover
            badge.addEventListener('mouseenter', () => {
                badge.style.transform = 'scale(1.1)';
                badge.style.transition = 'transform 0.3s ease';
            });
            
            badge.addEventListener('mouseleave', () => {
                badge.style.transform = 'scale(1)';
            });
        });
    }
}

// Exportar el componente
window.FooterComponent = FooterComponent;
