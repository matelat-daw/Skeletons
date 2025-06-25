// Función para cargar páginas dinámicamente
function loadPage(page) {
    // Colapsar navbar en móviles
    const navbarCollapse = document.getElementById('navbarNav');
    if (navbarCollapse && navbarCollapse.classList.contains('show')) {
        // Verificar si Bootstrap está disponible
        if (typeof bootstrap !== 'undefined') {
            const bsCollapse = new bootstrap.Collapse(navbarCollapse);
            bsCollapse.hide();
        } else {
            // Fallback manual
            navbarCollapse.classList.remove('show');
        }
    }
    
    // Mostrar indicador de carga
    const mainContent = document.getElementById('main-content');
    if (!mainContent) {
        console.error('Error: No se encontró el contenedor main-content');
        return;
    }
    
    mainContent.innerHTML = '<div class="loading"><i class="bi bi-hourglass-split me-2"></i>Cargando...</div>';
    
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
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Error:</strong> ${error.message}
                </div>
            `;
        });
}

function updateActiveMenuItem(page) {
    // Remover clase active de todos los enlaces
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
    navLinks.forEach(link => {
        link.classList.remove('active');
        link.style.backgroundColor = '';
    });
    
    // Agregar clase active al enlace correspondiente
    const activeLink = document.getElementById('nav-' + page);
    if (activeLink) {
        activeLink.classList.add('active');
        activeLink.style.backgroundColor = '#555';
    }
}

// Funciones para el modal de login - ENFOQUE ORIGINAL
function showLoginModal() {
    console.log('Iniciando showLoginModal...');
    
    // Remover modal existente si lo hay
    const existingModal = document.getElementById('loginModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Crear modal base sin contenido
    const modalHTML = `
        <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <!-- El contenido se cargará aquí vía AJAX -->
        </div>
    `;
    
    // Agregar al final del body
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Esperar a que Bootstrap esté disponible y luego mostrar modal
    waitForBootstrap(function() {
        const modal = document.getElementById('loginModal');
        if (modal && typeof bootstrap !== 'undefined') {
            console.log('Bootstrap disponible, cargando contenido del modal...');
            
            try {
                // Cargar contenido primero, luego mostrar modal
                loadLoginContent();
                
            } catch (error) {
                console.error('Error al crear modal Bootstrap:', error);
                showModalManually();
            }
        } else {
            console.error('Bootstrap no disponible o modal no encontrado');
            showModalManually();
        }
    });
}

// Fallback manual para mostrar modal
function showModalManually() {
    console.log('Mostrando modal manualmente...');
    const modal = document.getElementById('loginModal');
    if (modal) {
        // Cargar contenido primero
        loadLoginContent();
        
        // Mostrar modal manualmente después de cargar contenido
        setTimeout(() => {
            // Agregar backdrop manualmente
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            backdrop.style.zIndex = '1050';
            document.body.appendChild(backdrop);
            
            // Mostrar modal manualmente
            modal.style.display = 'block';
            modal.style.zIndex = '1055';
            modal.classList.add('show');
            modal.setAttribute('aria-modal', 'true');
            modal.setAttribute('role', 'dialog');
            
            document.body.classList.add('modal-open');
            document.body.style.paddingRight = '0px';
        }, 300);
    }
}

function loadLoginContent() {
    const modal = document.getElementById('loginModal');
    if (!modal) {
        console.error('No se encontró modal element');
        return;
    }
    
    console.log('Cargando contenido del login desde load_page.php...');
    
    // Mostrar indicador de carga mientras se carga el contenido
    modal.innerHTML = `
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center p-5">
                    <div class="loading">
                        <i class="bi bi-hourglass-split me-2"></i>Cargando formulario de login...
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Usar Fetch API para cargar el contenido del modal desde html/login/index.php
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
            
            // Ahora mostrar el modal con Bootstrap
            if (typeof bootstrap !== 'undefined') {
                try {
                    const bsModal = new bootstrap.Modal(modal, {
                        backdrop: 'static',
                        keyboard: false
                    });
                    bsModal.show();
                } catch (error) {
                    console.error('Error al mostrar modal con Bootstrap:', error);
                    // Fallback manual
                    showModalManuallyAfterLoad();
                }
            } else {
                // Fallback manual
                showModalManuallyAfterLoad();
            }
            
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
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-body">
                            <div class="alert alert-danger" role="alert">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>Error:</strong> ${error.message}
                            </div>
                            <div class="text-center">
                                <button type="button" class="btn btn-secondary" onclick="closeLoginModal()">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Mostrar modal con error
            if (typeof bootstrap !== 'undefined') {
                try {
                    const bsModal = new bootstrap.Modal(modal, {
                        backdrop: 'static',
                        keyboard: false
                    });
                    bsModal.show();
                } catch (error) {
                    showModalManuallyAfterLoad();
                }
            } else {
                showModalManuallyAfterLoad();
            }
        });
}

// Función auxiliar para mostrar modal manualmente después de cargar contenido
function showModalManuallyAfterLoad() {
    const modal = document.getElementById('loginModal');
    if (modal) {
        // Agregar backdrop manualmente
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        backdrop.style.zIndex = '1050';
        document.body.appendChild(backdrop);
        
        // Mostrar modal manualmente
        modal.style.display = 'block';
        modal.style.zIndex = '1055';
        modal.classList.add('show');
        modal.setAttribute('aria-modal', 'true');
        modal.setAttribute('role', 'dialog');
        
        document.body.classList.add('modal-open');
        document.body.style.paddingRight = '0px';
    }
}

function closeLoginModal() {
    console.log('Cerrando modal de login...');
    const modal = document.getElementById('loginModal');
    if (!modal) return;
    
    try {
        // Intentar cerrar con Bootstrap
        if (typeof bootstrap !== 'undefined') {
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) {
                bsModal.hide();
            }
        }
        
        // Cerrar manualmente como fallback
        modal.style.display = 'none';
        modal.classList.remove('show');
        modal.setAttribute('aria-hidden', 'true');
        modal.removeAttribute('aria-modal');
        modal.removeAttribute('role');
        
        // Remover backdrop
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            backdrop.remove();
        }
        
        // Restaurar body
        document.body.classList.remove('modal-open');
        document.body.style.paddingRight = '';
        document.body.style.overflow = '';
        
        // Remover modal del DOM después de un delay
        setTimeout(() => {
            if (modal && modal.parentNode) {
                modal.remove();
            }
        }, 300);
        
    } catch (error) {
        console.error('Error al cerrar modal:', error);
        // Forzar limpieza
        if (modal && modal.parentNode) {
            modal.remove();
        }
        document.body.classList.remove('modal-open');
        document.body.style.paddingRight = '';
        document.body.style.overflow = '';
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) backdrop.remove();
    }
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
    
    // Simulación de login exitoso con toast
    showToast('¡Login exitoso!', 'success');
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
    showToast('¡Registro exitoso!', 'success');
    loadPage('home');
}

// Función para mostrar toasts
function showToast(message, type = 'info') {
    waitForBootstrap(function() {
        try {
            const toastId = 'toast-' + Date.now();
            const iconClass = type === 'success' ? 'bi-check-circle' : 
                             type === 'danger' ? 'bi-exclamation-triangle' : 'bi-info-circle';
            
            const toastHTML = `
                <div class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true" id="${toastId}">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="bi ${iconClass} me-2"></i>${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            `;
            
            // Crear contenedor de toasts si no existe
            let toastContainer = document.getElementById('toast-container');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.id = 'toast-container';
                toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
                toastContainer.style.zIndex = '9999';
                document.body.appendChild(toastContainer);
            }
            
            toastContainer.insertAdjacentHTML('beforeend', toastHTML);
            
            const toastElement = document.getElementById(toastId);
            const toast = new bootstrap.Toast(toastElement, {
                autohide: true,
                delay: 3000
            });
            
            toast.show();
            
            // Remover el toast del DOM después de que se oculte
            toastElement.addEventListener('hidden.bs.toast', () => {
                toastElement.remove();
            });
        } catch (error) {
            console.error('Error al mostrar toast:', error);
            // Fallback: usar alert
            alert(message);
        }
    });
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

// Función para esperar a que Bootstrap esté disponible
function waitForBootstrap(callback) {
    if (window.bootstrapReady && typeof bootstrap !== 'undefined') {
        callback();
    } else {
        document.addEventListener('bootstrapReady', callback, { once: true });
    }
}

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    // Esperar a que Bootstrap esté disponible antes de inicializar
    waitForBootstrap(function() {
        console.log('Aplicación inicializada con Bootstrap disponible');
    });
    
    // Verificar si hay un parámetro de página en la URL
    const urlParams = new URLSearchParams(window.location.search);
    const currentPage = urlParams.get('page');
    
    if (currentPage) {
        updateActiveMenuItem(currentPage);
    } else {
        updateActiveMenuItem('home');
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
