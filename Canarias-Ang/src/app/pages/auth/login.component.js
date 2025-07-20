// Login Component - Componente de inicio de sesión
class LoginComponent {
    constructor() {
        this.template = `
            <div class="auth-component login-component">
                <div class="auth-container">
                    <div class="auth-card card">
                        <div class="auth-header">
                            <h2>🏝️ Iniciar Sesión</h2>
                            <p>Bienvenido de vuelta a Economía Circular Canarias</p>
                        </div>
                        
                        <form id="loginForm" class="auth-form">
                            <div class="form-group">
                                <label for="loginEmail">📧 Email</label>
                                <input 
                                    type="email" 
                                    id="loginEmail" 
                                    name="email" 
                                    required 
                                    class="form-input"
                                    placeholder="tu@email.com"
                                    autocomplete="email"
                                >
                                <span class="form-error" id="emailError"></span>
                            </div>

                            <div class="form-group">
                                <label for="loginPassword">🔒 Contraseña</label>
                                <div class="password-input-container">
                                    <input 
                                        type="password" 
                                        id="loginPassword" 
                                        name="password" 
                                        required 
                                        class="form-input"
                                        placeholder="Tu contraseña"
                                        autocomplete="current-password"
                                    >
                                    <button type="button" class="password-toggle" id="toggleLoginPassword">
                                        👁️
                                    </button>
                                </div>
                                <span class="form-error" id="passwordError"></span>
                            </div>

                            <div class="form-group form-checkbox">
                                <label class="checkbox-label">
                                    <input type="checkbox" id="rememberMe" name="rememberMe">
                                    <span class="checkmark"></span>
                                    Recordarme en este dispositivo
                                </label>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary btn-auth" id="loginBtn">
                                    <span class="btn-text">🔐 Iniciar Sesión</span>
                                    <span class="btn-loader" style="display: none;">⏳ Iniciando...</span>
                                </button>
                            </div>

                            <div class="auth-links">
                                <a href="#" class="link-secondary" id="forgotPasswordLink">
                                    ¿Olvidaste tu contraseña?
                                </a>
                            </div>
                        </form>

                        <div class="auth-divider">
                            <span>¿No tienes cuenta?</span>
                        </div>

                        <div class="auth-switch">
                            <a href="#/register" class="btn btn-secondary" data-navigate="/register">
                                👤 Crear Nueva Cuenta
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <style>
                .auth-component {
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 2rem;
                    background: linear-gradient(135deg, var(--canarias-light-gray), #e3f2fd);
                }

                .auth-container {
                    width: 100%;
                    max-width: 400px;
                }

                .auth-card {
                    padding: 3rem 2rem;
                    border-radius: 15px;
                    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
                    background: var(--canarias-white);
                }

                .auth-header {
                    text-align: center;
                    margin-bottom: 2rem;
                }

                .auth-header h2 {
                    color: var(--canarias-blue);
                    margin-bottom: 0.5rem;
                    font-size: 1.8rem;
                }

                .auth-header p {
                    color: #666;
                    font-size: 0.95rem;
                }

                .auth-form {
                    display: flex;
                    flex-direction: column;
                    gap: 1.5rem;
                }

                .form-group {
                    display: flex;
                    flex-direction: column;
                    gap: 0.5rem;
                }

                .form-group label {
                    font-weight: 600;
                    color: var(--canarias-dark);
                    font-size: 0.9rem;
                }

                .form-input {
                    padding: 0.75rem 1rem;
                    border: 2px solid var(--canarias-border);
                    border-radius: 8px;
                    font-size: 1rem;
                    transition: all 0.3s ease;
                    background: var(--canarias-white);
                }

                .form-input:focus {
                    outline: none;
                    border-color: var(--canarias-blue);
                    box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
                }

                .password-input-container {
                    position: relative;
                }

                .password-toggle {
                    position: absolute;
                    right: 0.75rem;
                    top: 50%;
                    transform: translateY(-50%);
                    background: none;
                    border: none;
                    cursor: pointer;
                    padding: 0.25rem;
                    font-size: 1rem;
                }

                .form-checkbox {
                    flex-direction: row;
                    align-items: center;
                }

                .checkbox-label {
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                    cursor: pointer;
                    font-size: 0.9rem;
                }

                .checkbox-label input[type="checkbox"] {
                    width: 18px;
                    height: 18px;
                    accent-color: var(--canarias-blue);
                }

                .form-error {
                    color: #dc3545;
                    font-size: 0.85rem;
                    display: none;
                }

                .form-error.show {
                    display: block;
                }

                .btn-auth {
                    width: 100%;
                    padding: 0.875rem;
                    font-size: 1rem;
                    font-weight: 600;
                    position: relative;
                }

                .btn-auth:disabled {
                    opacity: 0.7;
                    cursor: not-allowed;
                }

                .auth-links {
                    text-align: center;
                    margin-top: 1rem;
                }

                .link-secondary {
                    color: var(--canarias-blue);
                    text-decoration: none;
                    font-size: 0.9rem;
                    transition: color 0.3s ease;
                }

                .link-secondary:hover {
                    color: var(--canarias-ocean);
                    text-decoration: underline;
                }

                .auth-divider {
                    text-align: center;
                    margin: 2rem 0 1rem;
                    position: relative;
                    color: #666;
                    font-size: 0.9rem;
                }

                .auth-divider::before {
                    content: '';
                    position: absolute;
                    top: 50%;
                    left: 0;
                    right: 0;
                    height: 1px;
                    background: var(--canarias-border);
                    z-index: 1;
                }

                .auth-divider span {
                    background: var(--canarias-white);
                    padding: 0 1rem;
                    position: relative;
                    z-index: 2;
                }

                .auth-switch {
                    text-align: center;
                }

                @media (max-width: 480px) {
                    .auth-component {
                        padding: 1rem;
                    }
                    
                    .auth-card {
                        padding: 2rem 1.5rem;
                    }
                }
            </style>
        `;
    }

    render() {
        return this.template;
    }

    afterRender() {
        this.initializeForm();
        this.initializePasswordToggle();
        this.initializeNavigation();
        this.initializeForgotPassword();
    }

    initializeForm() {
        const form = document.getElementById('loginForm');
        if (!form) return;

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            await this.handleLogin(e);
        });
    }

    async handleLogin(e) {
        const formData = new FormData(e.target);
        const credentials = {
            email: formData.get('email'),
            password: formData.get('password'),
            rememberMe: formData.get('rememberMe') === 'on'
        };

        // Validar campos
        if (!this.validateForm(credentials)) {
            return;
        }

        // Mostrar estado de carga
        this.setLoadingState(true);

        try {
            const result = await window.authService.login(credentials);

            if (result.success) {
                this.showSuccess(result.message);
                
                // Redirigir después del login exitoso
                setTimeout(() => {
                    window.appRouter.navigate('/');
                }, 1500);
            } else {
                this.showError(result.message);
            }
        } catch (error) {
            console.error('Error en login:', error);
            this.showError('Error de conexión. Intenta nuevamente.');
        } finally {
            this.setLoadingState(false);
        }
    }

    validateForm(credentials) {
        this.clearErrors();
        let isValid = true;

        // Validar email
        if (!credentials.email) {
            this.showFieldError('emailError', 'El email es requerido');
            isValid = false;
        } else if (!this.isValidEmail(credentials.email)) {
            this.showFieldError('emailError', 'El email no es válido');
            isValid = false;
        }

        // Validar contraseña
        if (!credentials.password) {
            this.showFieldError('passwordError', 'La contraseña es requerida');
            isValid = false;
        } else if (credentials.password.length < 6) {
            this.showFieldError('passwordError', 'La contraseña debe tener al menos 6 caracteres');
            isValid = false;
        }

        return isValid;
    }

    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    showFieldError(fieldId, message) {
        const errorElement = document.getElementById(fieldId);
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.classList.add('show');
        }
    }

    clearErrors() {
        const errorElements = document.querySelectorAll('.form-error');
        errorElements.forEach(element => {
            element.classList.remove('show');
            element.textContent = '';
        });
    }

    setLoadingState(isLoading) {
        const btn = document.getElementById('loginBtn');
        const btnText = btn.querySelector('.btn-text');
        const btnLoader = btn.querySelector('.btn-loader');

        if (isLoading) {
            btn.disabled = true;
            btnText.style.display = 'none';
            btnLoader.style.display = 'inline';
        } else {
            btn.disabled = false;
            btnText.style.display = 'inline';
            btnLoader.style.display = 'none';
        }
    }

    showError(message) {
        alert(`❌ Error: ${message}`);
    }

    showSuccess(message) {
        alert(`✅ ${message}`);
    }

    initializePasswordToggle() {
        const toggleBtn = document.getElementById('toggleLoginPassword');
        const passwordInput = document.getElementById('loginPassword');

        if (toggleBtn && passwordInput) {
            toggleBtn.addEventListener('click', () => {
                const isPassword = passwordInput.type === 'password';
                passwordInput.type = isPassword ? 'text' : 'password';
                toggleBtn.textContent = isPassword ? '🙈' : '👁️';
            });
        }
    }

    initializeNavigation() {
        const navLinks = document.querySelectorAll('[data-navigate]');
        
        navLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const route = link.getAttribute('data-navigate');
                window.appRouter.navigate(route);
            });
        });
    }

    initializeForgotPassword() {
        const forgotLink = document.getElementById('forgotPasswordLink');
        
        if (forgotLink) {
            forgotLink.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleForgotPassword();
            });
        }
    }

    async handleForgotPassword() {
        const email = prompt('Ingresa tu email para restablecer la contraseña:');
        
        if (email) {
            const result = await window.authService.requestPasswordReset(email);
            
            if (result.success) {
                alert(`✅ ${result.message}`);
            } else {
                alert(`❌ ${result.message}`);
            }
        }
    }
}

// Exportar el componente
window.LoginComponent = LoginComponent;
