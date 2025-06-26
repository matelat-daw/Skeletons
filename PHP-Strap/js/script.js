// Función para cargar páginas dinámicamente
function loadPage(page) {
    // Colapsar navbar en móviles (estructura original)
    const menu = document.getElementById('menu');
    if (menu && menu.classList.contains('active')) {
        menu.classList.remove('active');
    }
    
    // Mostrar indicador de carga
    const mainContent = document.getElementById('main-content');
    if (!mainContent) {
        console.error('Error: No se encontró el contenedor main-content');
        return;
    }
    
    mainContent.innerHTML = '<div class="loading">Cargando...</div>';
    
    // Usar Fetch API para cargar la página
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
            
            // Actualizar la URL sin recargar la página
            if (history.pushState) {
                const newUrl = window.location.protocol + "//" + 
                              window.location.host + 
                              window.location.pathname + '?page=' + page;
                history.pushState({page: page}, '', newUrl);
            }
            
            // Actualizar clases activas en el menú
            updateActiveMenuItem(page);
            
            // Scroll al top
            window.scrollTo(0, 0);
        })
        .catch(error => {
            console.error('Error:', error);
            mainContent.innerHTML = `
                <div class="error">
                    <strong>Error:</strong> ${error.message}
                </div>
            `;
        });
}

function updateActiveMenuItem(page) {
    // Remover clase active de todos los enlaces (estructura original)
    const menuLinks = document.querySelectorAll('#menu a');
    menuLinks.forEach(link => {
        link.classList.remove('active');
    });
    
    // Agregar clase active al enlace correspondiente
    const activeLink = document.getElementById('nav-' + page);
    if (activeLink) {
        activeLink.classList.add('active');
    }
}

// Funciones para el modal de login - ENFOQUE ORIGINAL SIMPLIFICADO
function showLoginModal() {
    console.log('Iniciando showLoginModal...');
    
    // Remover modal existente si lo hay
    const existingModal = document.getElementById('loginModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Crear modal base
    const modalHTML = `
        <div class="modal" id="loginModal">
            <div class="loading">Cargando formulario de login...</div>
        </div>
    `;
    
    // Agregar al final del body
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Cargar contenido del modal
    loadLoginContent();
}

function loadLoginContent() {
    const modal = document.getElementById('loginModal');
    if (!modal) {
        console.error('No se encontró modal element');
        return;
    }
    
    console.log('Cargando contenido del login desde load_page.php...');
    
    // Usar Fetch API para cargar el contenido del modal
    fetch('load_page.php?page=login')
        .then(response => {
            console.log('Respuesta recibida:', response.status);
            if (!response.ok) {
                throw new Error('Error al cargar el formulario de login: ' + response.status);
            }
            return response.text();
        })
        .then(data => {
            console.log('Contenido del modal cargado desde login/index.php');
            
            // Insertar el contenido cargado directamente en el modal
            modal.innerHTML = data;
            
            // Mostrar el modal
            modal.classList.add('show');
            modal.style.display = 'block';
            
            // Enfocar el primer campo del formulario
            const emailField = document.getElementById('login-email');
            if (emailField) {
                setTimeout(() => emailField.focus(), 300);
            }
            
            // Agregar event listener al formulario cargado dinámicamente
            const loginForm = document.getElementById('loginForm');
            if (loginForm) {
                loginForm.addEventListener('submit', handleLoginSubmit);
                console.log('Event listener agregado al formulario de login');
            }
        })
        .catch(error => {
            console.error('Error al cargar login:', error);
            modal.innerHTML = `
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>Error</h2>
                        <span class="close" onclick="closeLoginModal()">&times;</span>
                    </div>
                    <div class="auth-form">
                        <div class="error">
                            <strong>Error:</strong> ${error.message}
                        </div>
                        <button type="button" class="btn-submit" onclick="closeLoginModal()">Cerrar</button>
                    </div>
                </div>
            `;
            modal.classList.add('show');
            modal.style.display = 'block';
        });
}

function closeLoginModal() {
    console.log('Cerrando modal de login...');
    const modal = document.getElementById('loginModal');
    if (!modal) return;
    
    // Cerrar modal
    modal.classList.remove('show');
    modal.style.display = 'none';
    
    // Remover modal del DOM después de un delay
    setTimeout(() => {
        if (modal && modal.parentNode) {
            modal.remove();
        }
    }, 300);
}

// Función para cerrar modal después de login exitoso
function closeLoginModalAfterSuccess() {
    closeLoginModal();
}

// Manejar el envío del formulario de login
function handleLoginSubmit(event) {
    event.preventDefault();
    
    const form = event.target;
    const email = document.getElementById('login-email').value;
    const password = document.getElementById('login-password').value;
    
    // Validación básica
    if (!email || !password) {
        form.classList.add('was-validated');
        return;
    }
    
    // Aquí puedes agregar la lógica de autenticación
    console.log('Login attempt:', { email, password });
    
    // Simulación de login exitoso
    alert('¡Login exitoso!');
    closeLoginModalAfterSuccess();
}

// Manejar el envío del formulario de registro
function handleRegisterSubmit(event) {
    event.preventDefault();
    
    const form = event.target;
    const name = document.getElementById('reg-name').value;
    const email = document.getElementById('reg-email').value;
    const password = document.getElementById('reg-password').value;
    const confirmPassword = document.getElementById('reg-confirm-password').value;
    const terms = document.getElementById('reg-terms').checked;
    
    // Validar que las contraseñas coincidan
    if (password !== confirmPassword) {
        const confirmField = document.getElementById('reg-confirm-password');
        confirmField.setCustomValidity('Las contraseñas no coinciden');
        form.classList.add('was-validated');
        return;
    } else {
        document.getElementById('reg-confirm-password').setCustomValidity('');
    }
    
    // Validar que se acepten los términos
    if (!terms) {
        form.classList.add('was-validated');
        return;
    }
    
    // Validar otros campos
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }
    
    // Aquí puedes agregar la lógica de registro
    console.log('Register attempt:', { name, email, password });
    
    // Simulación de registro exitoso
    alert('¡Registro exitoso!');
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
    console.log('Aplicación inicializada');
    
    // Verificar si hay un parámetro de página en la URL
    const urlParams = new URLSearchParams(window.location.search);
    const currentPage = urlParams.get('page');
    
    if (currentPage) {
        updateActiveMenuItem(currentPage);
    } else {
        updateActiveMenuItem('home');
    }
    
    // Toggle para menú móvil (estructura original)
    const menuToggle = document.getElementById('menu-toggle');
    const menu = document.getElementById('menu');
    
    if (menuToggle && menu) {
        menuToggle.addEventListener('click', function() {
            menu.classList.toggle('active');
        });
    }
    
    // Event listener para el formulario de registro (se agrega dinámicamente)
    document.addEventListener('submit', function(event) {
        if (event.target.id === 'registerForm') {
            handleRegisterSubmit(event);
        }
    });
    
    // Event listener para validación en tiempo real de contraseñas
    document.addEventListener('input', function(event) {
        if (event.target.id === 'reg-confirm-password') {
            const password = document.getElementById('reg-password').value;
            const confirmPassword = event.target.value;
            
            if (password !== confirmPassword) {
                event.target.setCustomValidity('Las contraseñas no coinciden');
            } else {
                event.target.setCustomValidity('');
            }
        }
    });
});
