// Auth Service - Servicio de autenticación
class AuthService {
    constructor() {
        this.baseUrl = 'https://api.economiacircularcanarias.com'; // URL del servidor
        this.currentUser = null;
        this.token = null;
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
            const hashedPassword = await this.hashPassword(userData.password);
            
            const payload = {
                name: userData.name,
                email: userData.email,
                password: hashedPassword,
                confirmPassword: await this.hashPassword(userData.confirmPassword),
                acceptTerms: userData.acceptTerms
            };

            const response = await fetch(`${this.baseUrl}/auth/register`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'include', // Para recibir cookies
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (response.ok) {
                return {
                    success: true,
                    message: 'Registro exitoso. Revisa tu email para confirmar tu cuenta.',
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
            const hashedPassword = await this.hashPassword(credentials.password);
            
            const payload = {
                email: credentials.email,
                password: hashedPassword,
                rememberMe: credentials.rememberMe || false
            };

            const response = await fetch(`${this.baseUrl}/auth/login`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'include', // Para recibir cookies
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (response.ok) {
                this.token = data.token;
                this.currentUser = data.user;
                
                // El token JWT se almacena automáticamente en cookies por el servidor
                // Disparar evento de login exitoso
                this.dispatchAuthEvent('login', this.currentUser);
                
                return {
                    success: true,
                    message: 'Login exitoso',
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
            const response = await fetch(`${this.baseUrl}/auth/logout`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${this.token}`,
                    'Content-Type': 'application/json',
                },
                credentials: 'include'
            });

            // Limpiar datos locales independientemente de la respuesta del servidor
            this.token = null;
            this.currentUser = null;
            
            // Disparar evento de logout
            this.dispatchAuthEvent('logout');
            
            return {
                success: true,
                message: 'Sesión cerrada exitosamente'
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
        if (!this.token) return false;

        try {
            const response = await fetch(`${this.baseUrl}/auth/validate`, {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${this.token}`,
                },
                credentials: 'include'
            });

            if (response.ok) {
                const data = await response.json();
                this.currentUser = data.user;
                this.dispatchAuthEvent('validated', this.currentUser);
                return true;
            } else {
                this.logout();
                return false;
            }
        } catch (error) {
            console.error('Error validando token:', error);
            this.logout();
            return false;
        }
    }

    // Cifrar password con SHA-512
    async hashPassword(password) {
        const encoder = new TextEncoder();
        const data = encoder.encode(password);
        const hashBuffer = await crypto.subtle.digest('SHA-512', data);
        const hashArray = Array.from(new Uint8Array(hashBuffer));
        const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
        return hashHex;
    }

    // Obtener token de la cookie
    getTokenFromCookie() {
        const cookies = document.cookie.split(';');
        for (let cookie of cookies) {
            const [name, value] = cookie.trim().split('=');
            if (name === 'auth_token') {
                return decodeURIComponent(value);
            }
        }
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
