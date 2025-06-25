function toggleMobileMenu() {
    const menu = document.getElementById('menu');
    const menuToggle = document.getElementById('menu-toggle');
    
    if (menu) {
        menu.classList.toggle('active');
        
        // Cambiar el icono del botón
        if (menu.classList.contains('active')) {
            menuToggle.innerHTML = '&times;'; // X para cerrar
            menuToggle.setAttribute('aria-label', 'Cerrar menú');
        } else {
            menuToggle.innerHTML = '&#9776;'; // Hamburguesa para abrir
            menuToggle.setAttribute('aria-label', 'Abrir menú');
        }
    }
}

// Cerrar menú móvil cuando se hace clic en un enlace
function closeMobileMenuOnClick() {
    const menu = document.getElementById('menu');
    const menuToggle = document.getElementById('menu-toggle');
    
    if (menu && menu.classList.contains('active')) {
        menu.classList.remove('active');
        menuToggle.innerHTML = '&#9776;';
        menuToggle.setAttribute('aria-label', 'Abrir menú');
    }
}

// Cerrar menú móvil cuando se redimensiona la pantalla
function handleResize() {
    const menu = document.getElementById('menu');
    const menuToggle = document.getElementById('menu-toggle');
    
    if (window.innerWidth >= 768) {
        // En tablets y desktop, asegurar que el menú esté visible
        if (menu) {
            menu.classList.remove('active');
        }
        if (menuToggle) {
            menuToggle.innerHTML = '&#9776;';
            menuToggle.setAttribute('aria-label', 'Abrir menú');
        }
    }
}

function loadPage(page) {
    // Cerrar menú móvil al cargar página
    closeMobileMenuOnClick();
    
    // Mostrar indicador de carga
    const mainContent = document.getElementById('main-content');
    if (!mainContent) {
        console.error('Error: No se encontró el contenedor main-content');
        return;
    }
    
    mainContent.innerHTML = '<div class="loading">Cargando...</div>';
    
    // Usar Fetch API en lugar de XMLHttpRequest
    fetch('load_page.php?page=' + encodeURIComponent(page))
        .then(response => {
            if (!response.ok) {
                throw new Error('Error al cargar la página: ' + response.status);
            }
            return response.text();
        })
        .then(data => {
            // Cargar el contenido en el contenedor
            mainContent.innerHTML = data;
            
            // Actualizar la URL sin recargar la página (opcional)
            if (history.pushState) {
                const newUrl = window.location.protocol + "//" + 
                              window.location.host + 
                              window.location.pathname + '?page=' + page;
                history.pushState({page: page}, '', newUrl);
            }
            
            // Actualizar clases activas en el menú
            updateActiveMenuItem(page);
        })
        .catch(error => {
            console.error('Error:', error);
            mainContent.innerHTML = '<div class="error">' + error.message + '</div>';
        });
}

function updateActiveMenuItem(page) {
    // Remover clase active de todos los enlaces
    const menuLinks = document.querySelectorAll('#menu a');
    menuLinks.forEach(link => {
        link.classList.remove('active');
    });
    
    // Agregar clase active al enlace correspondiente
    const pageMapping = {
        'home': 0,
        'about': 1,
        'contact': 2
    };
    
    if (pageMapping.hasOwnProperty(page)) {
        const activeLink = menuLinks[pageMapping[page]];
        if (activeLink) {
            activeLink.classList.add('active');
        }
    }
}

// Funciones para el modal de login
function showLoginModal() {
    const modal = document.getElementById('loginModal');
    if (!modal) {
        // Crear el modal si no existe
        createLoginModal();
        return;
    }
    
    // Si ya existe, mostrar y cargar contenido
    modal.style.display = 'block';
    loadLoginContent();
}

function createLoginModal() {
    // Crear el modal dinámicamente
    const modalHTML = `
        <div id="loginModal" class="modal">
            <div id="loginModalContent">
                <div class="loading">Cargando formulario de login...</div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    const modal = document.getElementById('loginModal');
    modal.style.display = 'block';
    
    // Cargar el contenido del login
    loadLoginContent();
}

function loadLoginContent() {
    const modalContent = document.getElementById('loginModalContent');
    if (!modalContent) return;
    
    modalContent.innerHTML = '<div class="loading">Cargando formulario de login...</div>';
    
    // Usar Fetch API para cargar el modal
    fetch('load_page.php?page=login')
        .then(response => {
            if (!response.ok) {
                throw new Error('Error al cargar el formulario de login');
            }
            return response.text();
        })
        .then(data => {
            modalContent.innerHTML = data;
            
            // Enfocar el primer campo del formulario
            const emailField = document.getElementById('login-email');
            if (emailField) {
                setTimeout(() => emailField.focus(), 100);
            }
            
            // Agregar event listener al formulario cargado dinámicamente
            const loginForm = document.getElementById('loginForm');
            if (loginForm) {
                loginForm.addEventListener('submit', handleLoginSubmit);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            modalContent.innerHTML = '<div class="error">' + error.message + '</div>';
        });
}

function closeLoginModal() {
    const modal = document.getElementById('loginModal');
    if (modal) {
        modal.style.display = 'none';
        // Limpiar el contenido para evitar problemas de memoria
        const modalContent = document.getElementById('loginModalContent');
        if (modalContent) {
            modalContent.innerHTML = '';
        }
    }
}

// Manejar el envío del formulario de login
function handleLoginSubmit(event) {
    event.preventDefault();
    
    const email = document.getElementById('login-email').value;
    const password = document.getElementById('login-password').value;
    
    // Aquí puedes agregar la lógica de autenticación
    console.log('Login attempt:', { email, password });
    
    // Ejemplo de simulación de login exitoso
    alert('Login exitoso! (Esta es una simulación)');
    closeLoginModal();
}

// Manejar el envío del formulario de registro
function handleRegisterSubmit(event) {
    event.preventDefault();
    
    const name = document.getElementById('reg-name').value;
    const email = document.getElementById('reg-email').value;
    const password = document.getElementById('reg-password').value;
    const confirmPassword = document.getElementById('reg-confirm-password').value;
    const terms = document.getElementById('reg-terms').checked;
    
    // Validar que las contraseñas coincidan
    if (password !== confirmPassword) {
        alert('Las contraseñas no coinciden');
        return;
    }
    
    // Validar que se acepten los términos
    if (!terms) {
        alert('Debes aceptar los términos y condiciones');
        return;
    }
    
    // Aquí puedes agregar la lógica de registro
    console.log('Register attempt:', { name, email, password });
    
    // Ejemplo de simulación de registro exitoso
    alert('Registro exitoso! (Esta es una simulación)');
    loadPage('home');
}

// Manejar el botón atrás del navegador
window.addEventListener('popstate', function(event) {
    if (event.state && event.state.page) {
        loadPage(event.state.page);
    } else {
        // Si no hay estado, cargar la página home
        loadPage('home');
    }
});

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    // Configurar el botón toggle del menú móvil
    const menuToggle = document.getElementById('menu-toggle');
    if (menuToggle) {
        menuToggle.addEventListener('click', toggleMobileMenu);
    }
    
    // Configurar el redimensionado de ventana
    window.addEventListener('resize', handleResize);
    
    // Verificar si hay un parámetro de página en la URL
    const urlParams = new URLSearchParams(window.location.search);
    const currentPage = urlParams.get('page');
    
    if (currentPage) {
        updateActiveMenuItem(currentPage);
    } else {
        updateActiveMenuItem('home');
    }
    
    // Cerrar menú móvil al hacer clic fuera de él
    document.addEventListener('click', function(event) {
        const menu = document.getElementById('menu');
        const menuToggle = document.getElementById('menu-toggle');
        const nav = document.querySelector('nav');
        const modal = document.getElementById('loginModal');
        
        // Si el clic no fue en el nav y el menú está activo, cerrarlo
        if (menu && menu.classList.contains('active') && 
            !nav.contains(event.target)) {
            closeMobileMenuOnClick();
        }
        
        // Cerrar modal si se hace clic fuera de él
        if (modal && event.target === modal) {
            closeLoginModal();
        }
    });
    
    // Event listener para el formulario de registro (se agrega dinámicamente)
    document.addEventListener('submit', function(event) {
        if (event.target.id === 'registerForm') {
            handleRegisterSubmit(event);
        }
        // El formulario de login se maneja dinámicamente en loadLoginContent()
    });
});