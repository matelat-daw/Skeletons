// Auth Service - Servicio de autenticación para Economía Circular Canarias
class AuthService {
    constructor() {
        this.baseUrl = 'http://localhost/Skeletons/PHP-API-NEXUS'; // URL del servidor PHP local
        this.currentUs    // Obtener token de la cookie
    getTokenFromCookie() {
        const cookies = document.cookie.split(';');
        for (let cookie of cookies) {
            const [name, value] = cookie.trim().split('=');
            if (name === 'canarias_auth_token') {
                console.log('🍪 Token encontrado en cookie');
                return value;
            }
        }
        console.log('🍪 No se encontró token en cookies');
        return null;
    }

    // Verificar si el usuario está autenticado       this.token = null;
        this.init();
    }

    init() {
        // Verificar si hay un token almacenado al iniciar
        this.token = this.getTokenFromCookie();
        if (this.token) {
            this.validateToken();
        }
    }

    // Registro de usuario
    async register(userData) {
        try {
            // Preparar datos para el backend PHP (sin hashear la contraseña, se hace en el backend)
            const payload = {
                name: userData.name,
                email: userData.email,
                password: userData.password,
                confirmPassword: userData.confirmPassword,
                acceptTerms: userData.acceptTerms
            };

            console.log('🔄 Enviando registro al servidor...', payload);

            const response = await fetch(`${this.baseUrl}/api/auth/register`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'include', // Para recibir cookies
                body: JSON.stringify(payload)
            });

            const data = await response.json();
            console.log('📩 Respuesta del servidor:', data);

            if (response.ok && data.success) {
                return {
                    success: true,
                    message: data.message || 'Registro exitoso. Revisa tu email para confirmar tu cuenta.',
                    data: data
                };
            } else {
                return {
                    success: false,
                    message: data.message || 'Error en el registro',
                    errors: data.errors || []
                };
            }
        } catch (error) {
            console.error('Error en registro:', error);
            return {
                success: false,
                message: 'Error de conexión. Intenta nuevamente.',
                error: error.message
            };
        }
    }

    // Login de usuario
    async login(credentials) {
        try {
            // Enviar contraseña sin hashear, el backend se encarga del hashing
            const payload = {
                email: credentials.email,
                password: credentials.password, // Sin hashear
                rememberMe: credentials.rememberMe || false
            };

            console.log('🔄 Enviando login al servidor...', { email: payload.email, rememberMe: payload.rememberMe });

            const response = await fetch(`${this.baseUrl}/api/auth/login`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'include', // Para recibir cookies
                body: JSON.stringify(payload)
            });

            const data = await response.json();
            console.log('📩 Respuesta de login:', data);

            if (response.ok && data.success) {
                this.token = data.token;
                this.currentUser = data.user;
                
                // El token JWT se almacena automáticamente en cookies por el servidor
                // Disparar evento de login exitoso
                this.dispatchAuthEvent('login', this.currentUser);
                
                return {
                    success: true,
                    message: data.message || 'Login exitoso',
                    user: this.currentUser
                };
            } else {
                return {
                    success: false,
                    message: data.message || 'Credenciales inválidas',
                    errors: data.errors || []
                };
            }
        } catch (error) {
            console.error('Error en login:', error);
            return {
                success: false,
                message: 'Error de conexión. Intenta nuevamente.',
                error: error.message
            };
        }
    }

    // Logout
    async logout() {
        try {
            console.log('🔄 Cerrando sesión...');
            
            const response = await fetch(`${this.baseUrl}/api/auth/logout`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${this.token}`,
                    'Content-Type': 'application/json',
                },
                credentials: 'include'
            });

            const data = await response.json();
            console.log('📩 Respuesta de logout:', data);

            // Limpiar datos locales independientemente de la respuesta del servidor
            this.token = null;
            this.currentUser = null;
            
            // Disparar evento de logout
            this.dispatchAuthEvent('logout');
            
            return {
                success: true,
                message: data.message || 'Sesión cerrada exitosamente'
            };
        } catch (error) {
            console.error('Error en logout:', error);
            // Limpiar datos locales aun si falla la petición
            this.token = null;
            this.currentUser = null;
            this.dispatchAuthEvent('logout');
            
            return {
                success: true,
                message: 'Sesión cerrada localmente'
            };
        }
    }

    // Confirmar email
    async confirmEmail(token) {
        try {
            const response = await fetch(`${this.baseUrl}/auth/confirm-email`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ token })
            });

            const data = await response.json();

            return {
                success: response.ok,
                message: data.message || (response.ok ? 'Email confirmado exitosamente' : 'Error al confirmar email')
            };
        } catch (error) {
            console.error('Error en confirmación de email:', error);
            return {
                success: false,
                message: 'Error de conexión. Intenta nuevamente.'
            };
        }
    }

    // Validar token actual
    async validateToken() {
        if (!this.token) {
            console.log('🔍 No hay token para validar');
            return false;
        }

        try {
            console.log('🔍 Validando token...');
            
            const response = await fetch(`${this.baseUrl}/api/auth/validate`, {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${this.token}`,
                },
                credentials: 'include'
            });

            const data = await response.json();
            console.log('📩 Respuesta de validación:', data);

            if (response.ok && data.success && data.valid) {
                this.currentUser = data.user;
                this.dispatchAuthEvent('validated', this.currentUser);
                return true;
            } else {
                // Token inválido, limpiar datos
                this.token = null;
                this.currentUser = null;
                this.dispatchAuthEvent('logout');
                return false;
            }
        } catch (error) {
            console.error('Error validando token:', error);
            this.token = null;
            this.currentUser = null;
            this.dispatchAuthEvent('logout');
            return false;
        }
    }

    // Obtener token de la cookie
    getTokenFromCookie() {
        const cookies = document.cookie.split(';');
        for (let cookie of cookies) {
            const [name, value] = cookie.trim().split('=');
            if (name === 'canarias_auth_token') {
                console.log('🍪 Token encontrado en cookie');
                return decodeURIComponent(value);
            }
        }
        console.log('🍪 No se encontró token en cookies');
        return null;
    }

    // Verificar si el usuario está autenticado
    isAuthenticated() {
        return this.token !== null && this.currentUser !== null;
    }

    // Obtener usuario actual
    getCurrentUser() {
        return this.currentUser;
    }

    // Obtener token actual
    getToken() {
        return this.token;
    }

    // Disparar eventos de autenticación
    dispatchAuthEvent(type, data = null) {
        const event = new CustomEvent(`auth-${type}`, {
            detail: data
        });
        window.dispatchEvent(event);
    }

    // Solicitar restablecimiento de contraseña
    async requestPasswordReset(email) {
        try {
            const response = await fetch(`${this.baseUrl}/auth/reset-password-request`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ email })
            });

            const data = await response.json();

            return {
                success: response.ok,
                message: data.message || (response.ok ? 'Se ha enviado un email con instrucciones' : 'Error al solicitar restablecimiento')
            };
        } catch (error) {
            console.error('Error en solicitud de reset:', error);
            return {
                success: false,
                message: 'Error de conexión. Intenta nuevamente.'
            };
        }
    }

    // Restablecer contraseña
    async resetPassword(token, newPassword) {
        try {
            const hashedPassword = await this.hashPassword(newPassword);
            
            const response = await fetch(`${this.baseUrl}/auth/reset-password`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    token, 
                    password: hashedPassword 
                })
            });

            const data = await response.json();

            return {
                success: response.ok,
                message: data.message || (response.ok ? 'Contraseña restablecida exitosamente' : 'Error al restablecer contraseña')
            };
        } catch (error) {
            console.error('Error en reset de contraseña:', error);
            return {
                success: false,
                message: 'Error de conexión. Intenta nuevamente.'
            };
        }
    }
}

// Crear instancia global del servicio de autenticación
window.authService = new AuthService();

// Exportar la clase
window.AuthService = AuthService;
