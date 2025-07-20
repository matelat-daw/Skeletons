// Register Component - Componente de registro de usuarios
class RegisterComponent {
    constructor() {
        this.template = `
            <div class="auth-component register-component">
                <div class="auth-container">
                    <div class="auth-card">
                        <div class="auth-header">
                            <div class="auth-logo">
                                🏝️
                            </div>
                            <h2>Crear Cuenta</h2>
                            <p class="auth-subtitle">Únete a la Economía Circular de Canarias</p>
                        </div>
                        
                        <form id="registerForm" class="auth-form">
                            <div class="form-group">
                                <label for="registerName" class="form-label">
                                    <span class="label-icon">👤</span>
                                    Nombre Completo
                                </label>
                                <div class="input-container">
                                    <input 
                                        type="text" 
                                        id="registerName" 
                                        name="name" 
                                        required 
                                        class="form-input"
                                        placeholder="Tu nombre completo"
                                        autocomplete="name"
                                    >
                                </div>
                                <span class="form-error" id="nameError"></span>
                            </div>

                            <div class="form-group">
                                <label for="registerEmail" class="form-label">
                                    <span class="label-icon">📧</span>
                                    Correo Electrónico
                                </label>
                                <div class="input-container">
                                    <input 
                                        type="email" 
                                        id="registerEmail" 
                                        name="email" 
                                        required 
                                        class="form-input"
                                        placeholder="tu@email.com"
                                        autocomplete="email"
                                    >
                                </div>
                                <span class="form-error" id="emailError"></span>
                            </div>

                            <div class="form-group">
                                <label for="registerPassword" class="form-label">
                                    <span class="label-icon">🔒</span>
                                    Contraseña
                                </label>
                                <div class="input-container password-container">
                                    <input 
                                        type="password" 
                                        id="registerPassword" 
                                        name="password" 
                                        required 
                                        class="form-input"
                                        placeholder="Mínimo 8 caracteres"
                                        autocomplete="new-password"
                                    >
                                    <button type="button" class="password-toggle" id="toggleRegisterPassword">
                                        <span class="eye-icon">👁️</span>
                                    </button>
                                </div>
                                <div class="password-strength" id="passwordStrength">
                                    <div class="strength-bar">
                                        <div class="strength-fill" id="strengthFill"></div>
                                    </div>
                                    <span class="strength-text" id="strengthText">Fortaleza de contraseña</span>
                                </div>
                                <span class="form-error" id="passwordError"></span>
                            </div>

                            <div class="form-group">
                                <label for="confirmPassword" class="form-label">
                                    <span class="label-icon">🔒</span>
                                    Confirmar Contraseña
                                </label>
                                <div class="input-container password-container">
                                    <input 
                                        type="password" 
                                        id="confirmPassword" 
                                        name="confirmPassword" 
                                        required 
                                        class="form-input"
                                        placeholder="Repite tu contraseña"
                                        autocomplete="new-password"
                                    >
                                    <button type="button" class="password-toggle" id="toggleConfirmPassword">
                                        <span class="eye-icon">👁️</span>
                                    </button>
                                </div>
                                <span class="form-error" id="confirmPasswordError"></span>
                            </div>

                            <div class="form-group checkbox-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" id="acceptTerms" name="acceptTerms" required>
                                    <span class="checkmark"></span>
                                    <span class="checkbox-text">
                                        Acepto los 
                                        <a href="#/terminos" class="auth-link" target="_blank">términos y condiciones</a>
                                        y la 
                                        <a href="#/privacidad" class="auth-link" target="_blank">política de privacidad</a>
                                    </span>
                                </label>
                                <span class="form-error" id="termsError"></span>
                            </div>

                            <button type="submit" class="btn-auth btn-primary" id="registerBtn">
                                <span class="btn-content">
                                    <span class="btn-icon">🎯</span>
                                    <span class="btn-text">Crear Cuenta</span>
                                </span>
                                <span class="btn-loader">
                                    <span class="loader-spin">⏳</span>
                                    Creando cuenta...
                                </span>
                            </button>
                        </form>

                        <div class="auth-divider">
                            <span>¿Ya tienes cuenta?</span>
                        </div>

                        <div class="auth-footer">
                            <a href="#/login" class="btn-auth btn-secondary" data-navigate="/login">
                                <span class="btn-icon">🔐</span>
                                Iniciar Sesión
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `;
        this.addStyles();
    }

    addStyles() {
        if (!document.getElementById('register-styles')) {
            const style = document.createElement('style');
            style.id = 'register-styles';
            style.textContent = `
                /* Register Component Styles */
                .register-component .auth-container {
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 2rem 1rem;
                    background: linear-gradient(135deg, var(--canarias-blue) 0%, var(--canarias-yellow) 100%);
                }

                .register-component .auth-card {
                    background: var(--canarias-white);
                    border-radius: 20px;
                    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
                    padding: 3rem;
                    width: 100%;
                    max-width: 450px;
                    position: relative;
                    overflow: hidden;
                }

                .register-component .auth-card::before {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    height: 4px;
                    background: linear-gradient(90deg, var(--canarias-blue), var(--canarias-yellow));
                }

                .register-component .auth-header {
                    text-align: center;
                    margin-bottom: 2rem;
                }

                .register-component .auth-logo {
                    font-size: 3rem;
                    margin-bottom: 1rem;
                    display: inline-block;
                    animation: bounce 2s ease-in-out infinite;
                }

                @keyframes bounce {
                    0%, 20%, 50%, 80%, 100% {
                        transform: translateY(0);
                    }
                    40% {
                        transform: translateY(-10px);
                    }
                    60% {
                        transform: translateY(-5px);
                    }
                }

                .register-component .auth-header h2 {
                    color: var(--canarias-dark);
                    margin: 0 0 0.5rem 0;
                    font-size: 1.8rem;
                    font-weight: 700;
                }

                .register-component .auth-subtitle {
                    color: #666;
                    margin: 0;
                    font-size: 0.95rem;
                }

                .register-component .form-group {
                    margin-bottom: 1.5rem;
                }

                .register-component .form-label {
                    display: flex;
                    align-items: center;
                    margin-bottom: 0.5rem;
                    color: var(--canarias-dark);
                    font-weight: 600;
                    font-size: 0.9rem;
                }

                .register-component .label-icon {
                    margin-right: 0.5rem;
                    font-size: 1rem;
                }

                .register-component .input-container {
                    position: relative;
                }

                .register-component .form-input {
                    width: 100%;
                    padding: 0.875rem 1rem;
                    border: 2px solid #E9ECEF;
                    border-radius: 12px;
                    font-size: 1rem;
                    transition: all 0.3s ease;
                    background: var(--canarias-white);
                    color: var(--canarias-dark);
                    box-sizing: border-box;
                }

                .register-component .form-input:focus {
                    outline: none;
                    border-color: var(--canarias-blue);
                    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
                }

                .register-component .form-input::placeholder {
                    color: #999;
                }

                .register-component .password-container {
                    position: relative;
                }

                .register-component .password-container .form-input {
                    padding-right: 3rem;
                }

                .register-component .password-toggle {
                    position: absolute;
                    right: 1rem;
                    top: 50%;
                    transform: translateY(-50%);
                    background: none;
                    border: none;
                    cursor: pointer;
                    font-size: 1rem;
                    color: #666;
                    transition: color 0.3s ease;
                    z-index: 10;
                }

                .register-component .password-toggle:hover {
                    color: var(--canarias-blue);
                }

                .register-component .password-strength {
                    margin-top: 0.75rem;
                }

                .register-component .strength-bar {
                    width: 100%;
                    height: 6px;
                    background: #E9ECEF;
                    border-radius: 3px;
                    overflow: hidden;
                    margin-bottom: 0.5rem;
                }

                .register-component .strength-fill {
                    height: 100%;
                    width: 0%;
                    background: #dc3545;
                    transition: all 0.4s ease;
                    border-radius: 3px;
                }

                .register-component .strength-fill.weak {
                    background: #dc3545;
                    width: 25%;
                }

                .register-component .strength-fill.fair {
                    background: #ffc107;
                    width: 50%;
                }

                .register-component .strength-fill.good {
                    background: #28a745;
                    width: 75%;
                }

                .register-component .strength-fill.strong {
                    background: #28a745;
                    width: 100%;
                }

                .register-component .strength-text {
                    font-size: 0.8rem;
                    color: #666;
                    font-weight: 500;
                }

                .register-component .strength-text.weak {
                    color: #dc3545;
                }

                .register-component .strength-text.fair {
                    color: #ffc107;
                }

                .register-component .strength-text.good {
                    color: #28a745;
                }

                .register-component .strength-text.strong {
                    color: #28a745;
                }

                .register-component .checkbox-group {
                    margin: 2rem 0;
                }

                .register-component .checkbox-label {
                    display: flex;
                    align-items: flex-start;
                    cursor: pointer;
                    line-height: 1.5;
                    font-size: 0.9rem;
                }

                .register-component .checkbox-label input {
                    margin-right: 0.75rem;
                    margin-top: 0.25rem;
                    transform: scale(1.2);
                }

                .register-component .checkbox-text {
                    color: #666;
                }

                .register-component .auth-link {
                    color: var(--canarias-blue);
                    text-decoration: none;
                    font-weight: 600;
                }

                .register-component .auth-link:hover {
                    text-decoration: underline;
                }

                .register-component .btn-auth {
                    width: 100%;
                    padding: 1rem;
                    border: none;
                    border-radius: 12px;
                    font-size: 1rem;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    text-decoration: none;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    position: relative;
                    overflow: hidden;
                }

                .register-component .btn-primary {
                    background: linear-gradient(135deg, var(--canarias-blue), #2980b9);
                    color: white;
                    margin-bottom: 1.5rem;
                }

                .register-component .btn-primary:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 8px 25px rgba(52, 152, 219, 0.3);
                }

                .register-component .btn-primary:disabled {
                    opacity: 0.7;
                    cursor: not-allowed;
                    transform: none;
                }

                .register-component .btn-secondary {
                    background: transparent;
                    color: var(--canarias-blue);
                    border: 2px solid var(--canarias-blue);
                }

                .register-component .btn-secondary:hover {
                    background: var(--canarias-blue);
                    color: white;
                }

                .register-component .btn-content {
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                }

                .register-component .btn-loader {
                    display: none;
                    align-items: center;
                    gap: 0.5rem;
                }

                .register-component .btn-auth:disabled .btn-content {
                    display: none;
                }

                .register-component .btn-auth:disabled .btn-loader {
                    display: flex;
                }

                .register-component .loader-spin {
                    animation: spin 1s linear infinite;
                }

                @keyframes spin {
                    from { transform: rotate(0deg); }
                    to { transform: rotate(360deg); }
                }

                .register-component .auth-divider {
                    text-align: center;
                    margin: 2rem 0 1.5rem;
                    position: relative;
                    color: #666;
                    font-size: 0.9rem;
                }

                .register-component .auth-divider::before {
                    content: '';
                    position: absolute;
                    top: 50%;
                    left: 0;
                    right: 0;
                    height: 1px;
                    background: #E9ECEF;
                    z-index: 1;
                }

                .register-component .auth-divider span {
                    background: var(--canarias-white);
                    padding: 0 1rem;
                    position: relative;
                    z-index: 2;
                }

                .register-component .form-error {
                    display: block;
                    color: #dc3545;
                    font-size: 0.8rem;
                    margin-top: 0.5rem;
                    opacity: 0;
                    transform: translateY(-10px);
                    transition: all 0.3s ease;
                }

                .register-component .form-error.show {
                    opacity: 1;
                    transform: translateY(0);
                }

                /* Responsive Design */
                @media (max-width: 768px) {
                    .register-component .auth-container {
                        padding: 1rem;
                    }

                    .register-component .auth-card {
                        padding: 2rem 1.5rem;
                        margin: 1rem 0;
                    }

                    .register-component .auth-header h2 {
                        font-size: 1.5rem;
                    }

                    .register-component .checkbox-label {
                        font-size: 0.85rem;
                    }
                }

                /* Dark Theme Support */
                [data-theme="dark"] .register-component .auth-card {
                    background: #2d2d2d;
                    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                }

                [data-theme="dark"] .register-component .form-input {
                    background: #404040;
                    border-color: #555;
                    color: #fff;
                }

                [data-theme="dark"] .register-component .form-input:focus {
                    border-color: var(--canarias-blue);
                }

                [data-theme="dark"] .register-component .strength-bar {
                    background: #404040;
                }
            `;
            document.head.appendChild(style);
        }
    }

    render() {
        return this.template;
    }

    afterRender() {
        this.initializeForm();
        this.initializePasswordToggles();
        this.initializePasswordStrength();
        this.initializeNavigation();
        this.initializeCheckboxAnimation();
    }

    initializeCheckboxAnimation() {
        const checkbox = document.getElementById('acceptTerms');
        if (checkbox) {
            checkbox.addEventListener('change', () => {
                const label = checkbox.closest('.checkbox-label');
                if (checkbox.checked) {
                    label.style.transform = 'scale(1.02)';
                    setTimeout(() => {
                        label.style.transform = 'scale(1)';
                    }, 150);
                }
            });
        }
    }

    initializeForm() {
        const form = document.getElementById('registerForm');
        if (!form) return;

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            await this.handleRegister(e);
        });

        // Validación en tiempo real
        const inputs = form.querySelectorAll('.form-input');
        inputs.forEach(input => {
            input.addEventListener('blur', () => this.validateField(input));
            input.addEventListener('input', () => this.clearFieldError(input));
        });
    }

    validateField(input) {
        const fieldName = input.name;
        const value = input.value.trim();

        switch (fieldName) {
            case 'name':
                if (!value) {
                    this.showFieldError('nameError', 'El nombre es requerido');
                } else if (value.length < 2) {
                    this.showFieldError('nameError', 'El nombre debe tener al menos 2 caracteres');
                }
                break;
            
            case 'email':
                if (!value) {
                    this.showFieldError('emailError', 'El email es requerido');
                } else if (!this.isValidEmail(value)) {
                    this.showFieldError('emailError', 'El email no es válido');
                }
                break;

            case 'password':
                if (!value) {
                    this.showFieldError('passwordError', 'La contraseña es requerida');
                } else if (value.length < 8) {
                    this.showFieldError('passwordError', 'La contraseña debe tener al menos 8 caracteres');
                }
                break;

            case 'confirmPassword':
                const password = document.getElementById('registerPassword').value;
                if (!value) {
                    this.showFieldError('confirmPasswordError', 'Debes confirmar la contraseña');
                } else if (password !== value) {
                    this.showFieldError('confirmPasswordError', 'Las contraseñas no coinciden');
                }
                break;
        }
    }

    clearFieldError(input) {
        const fieldName = input.name;
        const errorId = fieldName + 'Error';
        const errorElement = document.getElementById(errorId);
        if (errorElement) {
            errorElement.classList.remove('show');
        }
    }

    async handleRegister(e) {
        const formData = new FormData(e.target);
        const userData = {
            name: formData.get('name'),
            email: formData.get('email'),
            password: formData.get('password'),
            confirmPassword: formData.get('confirmPassword'),
            acceptTerms: formData.get('acceptTerms') === 'on'
        };

        // Validar campos
        if (!this.validateForm(userData)) {
            return;
        }

        // Mostrar estado de carga
        this.setLoadingState(true);

        try {
            const result = await window.authService.register(userData);

            if (result.success) {
                this.showSuccess(result.message);
                
                // Limpiar formulario
                e.target.reset();
                
                // Redirigir a página de confirmación o login después de un tiempo
                setTimeout(() => {
                    window.appRouter.navigate('/login');
                }, 3000);
            } else {
                this.showError(result.message);
                
                // Mostrar errores específicos del servidor
                if (result.errors && result.errors.length > 0) {
                    result.errors.forEach(error => {
                        if (error.field) {
                            this.showFieldError(`${error.field}Error`, error.message);
                        }
                    });
                }
            }
        } catch (error) {
            console.error('Error en registro:', error);
            this.showError('Error de conexión. Intenta nuevamente.');
        } finally {
            this.setLoadingState(false);
        }
    }

    validateForm(userData) {
        this.clearErrors();
        let isValid = true;

        // Validar nombre
        if (!userData.name.trim()) {
            this.showFieldError('nameError', 'El nombre es requerido');
            isValid = false;
        } else if (userData.name.trim().length < 2) {
            this.showFieldError('nameError', 'El nombre debe tener al menos 2 caracteres');
            isValid = false;
        }

        // Validar email
        if (!userData.email) {
            this.showFieldError('emailError', 'El email es requerido');
            isValid = false;
        } else if (!this.isValidEmail(userData.email)) {
            this.showFieldError('emailError', 'El email no es válido');
            isValid = false;
        }

        // Validar contraseña
        const passwordStrength = this.checkPasswordStrength(userData.password);
        if (!userData.password) {
            this.showFieldError('passwordError', 'La contraseña es requerida');
            isValid = false;
        } else if (userData.password.length < 8) {
            this.showFieldError('passwordError', 'La contraseña debe tener al menos 8 caracteres');
            isValid = false;
        } else if (passwordStrength.level < 2) {
            this.showFieldError('passwordError', 'La contraseña es muy débil. Usa mayúsculas, números y símbolos');
            isValid = false;
        }

        // Validar confirmación de contraseña
        if (!userData.confirmPassword) {
            this.showFieldError('confirmPasswordError', 'Debes confirmar la contraseña');
            isValid = false;
        } else if (userData.password !== userData.confirmPassword) {
            this.showFieldError('confirmPasswordError', 'Las contraseñas no coinciden');
            isValid = false;
        }

        // Validar términos y condiciones
        if (!userData.acceptTerms) {
            this.showFieldError('termsError', 'Debes aceptar los términos y condiciones');
            isValid = false;
        }

        return isValid;
    }

    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    checkPasswordStrength(password) {
        let score = 0;
        let feedback = [];

        if (password.length >= 8) score++;
        if (password.length >= 12) score++;
        if (/[a-z]/.test(password)) score++;
        if (/[A-Z]/.test(password)) score++;
        if (/[0-9]/.test(password)) score++;
        if (/[^A-Za-z0-9]/.test(password)) score++;

        let level = 0;
        let text = 'Muy débil';
        let className = 'weak';

        if (score >= 2) {
            level = 1;
            text = 'Débil';
            className = 'weak';
        }
        if (score >= 4) {
            level = 2;
            text = 'Regular';
            className = 'fair';
        }
        if (score >= 5) {
            level = 3;
            text = 'Buena';
            className = 'good';
        }
        if (score >= 6) {
            level = 4;
            text = 'Muy fuerte';
            className = 'strong';
        }

        return { level, text, className, score };
    }

    initializePasswordStrength() {
        const passwordInput = document.getElementById('registerPassword');
        const strengthFill = document.getElementById('strengthFill');
        const strengthText = document.getElementById('strengthText');

        if (passwordInput && strengthFill && strengthText) {
            passwordInput.addEventListener('input', (e) => {
                const password = e.target.value;
                const strength = this.checkPasswordStrength(password);

                // Limpiar clases anteriores
                strengthFill.className = 'strength-fill';
                strengthText.className = 'strength-text';

                if (password.length > 0) {
                    strengthFill.classList.add(strength.className);
                    strengthText.classList.add(strength.className);
                    strengthText.textContent = strength.text;
                } else {
                    strengthText.textContent = 'Fortaleza de contraseña';
                }
            });
        }
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
        const btn = document.getElementById('registerBtn');
        if (!btn) return;

        const btnContent = btn.querySelector('.btn-content');
        const btnLoader = btn.querySelector('.btn-loader');

        if (isLoading) {
            btn.disabled = true;
            btnContent.style.display = 'none';
            btnLoader.style.display = 'flex';
        } else {
            btn.disabled = false;
            btnContent.style.display = 'flex';
            btnLoader.style.display = 'none';
        }
    }

    showError(message) {
        // Crear notificación de error moderna
        this.showNotification(message, 'error');
    }

    showSuccess(message) {
        // Crear notificación de éxito moderna
        this.showNotification(message, 'success');
    }

    showNotification(message, type) {
        // Remover notificación anterior si existe
        const existingNotification = document.querySelector('.auth-notification');
        if (existingNotification) {
            existingNotification.remove();
        }

        const notification = document.createElement('div');
        notification.className = `auth-notification ${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <span class="notification-icon">
                    ${type === 'success' ? '✅' : '❌'}
                </span>
                <span class="notification-message">${message}</span>
                <button class="notification-close" onclick="this.parentElement.parentElement.remove()">
                    ×
                </button>
            </div>
        `;

        // Agregar estilos si no existen
        if (!document.getElementById('notification-styles')) {
            const style = document.createElement('style');
            style.id = 'notification-styles';
            style.textContent = `
                .auth-notification {
                    position: fixed;
                    top: 2rem;
                    right: 2rem;
                    z-index: 10000;
                    max-width: 400px;
                    border-radius: 12px;
                    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
                    animation: slideInRight 0.3s ease;
                }

                .auth-notification.success {
                    background: linear-gradient(135deg, #28a745, #20c997);
                    color: white;
                }

                .auth-notification.error {
                    background: linear-gradient(135deg, #dc3545, #e74c3c);
                    color: white;
                }

                .notification-content {
                    display: flex;
                    align-items: center;
                    padding: 1rem 1.5rem;
                    gap: 0.75rem;
                }

                .notification-icon {
                    font-size: 1.2rem;
                    flex-shrink: 0;
                }

                .notification-message {
                    flex: 1;
                    font-size: 0.9rem;
                    font-weight: 500;
                }

                .notification-close {
                    background: none;
                    border: none;
                    color: inherit;
                    font-size: 1.5rem;
                    cursor: pointer;
                    opacity: 0.7;
                    transition: opacity 0.3s ease;
                    flex-shrink: 0;
                }

                .notification-close:hover {
                    opacity: 1;
                }

                @keyframes slideInRight {
                    from {
                        transform: translateX(100%);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0);
                        opacity: 1;
                    }
                }

                @media (max-width: 768px) {
                    .auth-notification {
                        top: 1rem;
                        right: 1rem;
                        left: 1rem;
                        max-width: none;
                    }
                }
            `;
            document.head.appendChild(style);
        }

        document.body.appendChild(notification);

        // Auto-remover después de 5 segundos
        setTimeout(() => {
            if (notification.parentElement) {
                notification.style.animation = 'slideInRight 0.3s ease reverse';
                setTimeout(() => notification.remove(), 300);
            }
        }, 5000);
    }

    initializePasswordToggles() {
        const toggles = [
            { btn: 'toggleRegisterPassword', input: 'registerPassword' },
            { btn: 'toggleConfirmPassword', input: 'confirmPassword' }
        ];

        toggles.forEach(({ btn, input }) => {
            const toggleBtn = document.getElementById(btn);
            const passwordInput = document.getElementById(input);

            if (toggleBtn && passwordInput) {
                toggleBtn.addEventListener('click', () => {
                    const isPassword = passwordInput.type === 'password';
                    const eyeIcon = toggleBtn.querySelector('.eye-icon');
                    
                    passwordInput.type = isPassword ? 'text' : 'password';
                    eyeIcon.textContent = isPassword ? '🙈' : '👁️';
                    
                    // Agregar animación de clic
                    toggleBtn.style.transform = 'scale(0.9)';
                    setTimeout(() => {
                        toggleBtn.style.transform = 'scale(1)';
                    }, 150);
                });
            }
        });
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
}

// Exportar el componente
window.RegisterComponent = RegisterComponent;
