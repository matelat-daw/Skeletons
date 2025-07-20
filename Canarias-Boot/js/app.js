// Canarias Economic Circular - JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Theme Management
    const themeToggle = document.getElementById('themeToggle');
    const htmlElement = document.documentElement;
    
    // Check for saved theme or default to light
    const savedTheme = localStorage.getItem('canarias-theme');
    if (savedTheme) {
        htmlElement.setAttribute('data-bs-theme', savedTheme);
        updateThemeIcon(savedTheme);
    } else {
        htmlElement.setAttribute('data-bs-theme', 'light');
        updateThemeIcon('light');
    }

    // Theme toggle functionality
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            const currentTheme = htmlElement.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            
            htmlElement.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('canarias-theme', newTheme);
            updateThemeIcon(newTheme);
            
            // Show toast notification
            showToast(`Tema cambiado a ${newTheme === 'light' ? 'claro' : 'oscuro'}`);
        });
    }

    // Function to update theme icon
    function updateThemeIcon(theme) {
        const themeIcon = document.getElementById('themeIcon');
        if (themeIcon) {
            themeIcon.className = theme === 'light' ? 'bi bi-moon-fill' : 'bi bi-sun-fill';
        }
    }

    // Login Form Handler
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;
            
            // Simple validation
            if (!email || !password) {
                showToast('Por favor, complete todos los campos', 'warning');
                return;
            }
            
            // Simulate login process
            showToast('Iniciando sesión...', 'info');
            setTimeout(() => {
                showToast('¡Bienvenido a la Economía Circular de Canarias!', 'success');
                bootstrap.Modal.getInstance(document.getElementById('loginModal')).hide();
                
                // Update UI to show user is logged in
                updateAuthenticationUI(true, email);
            }, 1500);
        });
    }

    // Register Form Handler
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const name = document.getElementById('registerName').value;
            const email = document.getElementById('registerEmail').value;
            const password = document.getElementById('registerPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            // Simple validation
            if (!name || !email || !password || !confirmPassword) {
                showToast('Por favor, complete todos los campos', 'warning');
                return;
            }
            
            if (password !== confirmPassword) {
                showToast('Las contraseñas no coinciden', 'error');
                return;
            }
            
            // Simulate registration process
            showToast('Creando cuenta...', 'info');
            setTimeout(() => {
                showToast('¡Cuenta creada exitosamente! Bienvenido/a', 'success');
                bootstrap.Modal.getInstance(document.getElementById('registerModal')).hide();
                
                // Update UI to show user is logged in
                updateAuthenticationUI(true, email);
            }, 1500);
        });
    }

    // Function to show toast notifications
    function showToast(message, type = 'info') {
        const toastContainer = document.getElementById('toastContainer');
        if (!toastContainer) return;

        const toastId = 'toast-' + Date.now();
        const iconClass = getToastIcon(type);
        const bgClass = getToastBackground(type);
        
        const toastHTML = `
            <div id="${toastId}" class="toast ${bgClass}" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <i class="${iconClass} me-2"></i>
                    <strong class="me-auto">Canarias Circular</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body text-white">
                    ${message}
                </div>
            </div>
        `;
        
        toastContainer.insertAdjacentHTML('beforeend', toastHTML);
        
        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement, {
            autohide: true,
            delay: 3000
        });
        
        toast.show();
        
        // Remove toast element after it's hidden
        toastElement.addEventListener('hidden.bs.toast', function() {
            toastElement.remove();
        });
    }

    // Helper function to get toast icon based on type
    function getToastIcon(type) {
        const icons = {
            'info': 'bi bi-info-circle-fill text-primary',
            'success': 'bi bi-check-circle-fill text-success',
            'warning': 'bi bi-exclamation-triangle-fill text-warning',
            'error': 'bi bi-x-circle-fill text-danger'
        };
        return icons[type] || icons['info'];
    }

    // Helper function to get toast background based on type
    function getToastBackground(type) {
        const backgrounds = {
            'info': 'bg-primary',
            'success': 'bg-success',
            'warning': 'bg-warning',
            'error': 'bg-danger'
        };
        return backgrounds[type] || backgrounds['info'];
    }

    // Function to update authentication UI
    function updateAuthenticationUI(isLoggedIn, userEmail = '') {
        const authButtons = document.querySelector('.auth-buttons');
        const userMenu = document.querySelector('.user-menu');
        
        if (isLoggedIn) {
            if (authButtons) authButtons.style.display = 'none';
            if (userMenu) {
                userMenu.style.display = 'block';
                const userEmailSpan = userMenu.querySelector('.user-email');
                if (userEmailSpan) userEmailSpan.textContent = userEmail;
            }
        } else {
            if (authButtons) authButtons.style.display = 'flex';
            if (userMenu) userMenu.style.display = 'none';
        }
    }

    // Card hover effects
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Initialize counters with animation
    function animateCounters() {
        const counters = document.querySelectorAll('.counter');
        counters.forEach(counter => {
            const target = parseInt(counter.getAttribute('data-target'));
            const duration = 2000;
            const step = target / (duration / 16);
            let current = 0;
            
            const timer = setInterval(() => {
                current += step;
                if (current >= target) {
                    counter.textContent = target.toLocaleString();
                    clearInterval(timer);
                } else {
                    counter.textContent = Math.floor(current).toLocaleString();
                }
            }, 16);
        });
    }

    // Trigger counter animation when page loads
    setTimeout(animateCounters, 500);

    // Welcome message on page load
    setTimeout(() => {
        showToast('¡Bienvenido a la Plataforma de Economía Circular de Canarias!', 'success');
    }, 1000);
});
