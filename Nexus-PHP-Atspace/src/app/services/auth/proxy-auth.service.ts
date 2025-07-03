import { Injectable, signal } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class ProxyAuthService {

  private readonly PROXY_URL = 'https://b895-88-24-26-59.ngrok-free.app/proxy.php'
  private readonly API_BASE = 'https://b895-88-24-26-59.ngrok-free.app'

  token = signal<string | null>(sessionStorage.getItem('auth_token'));

  constructor() { }

  private async proxyRequest(endpoint: string, options: RequestInit = {}): Promise<Response> {
    const targetUrl = this.API_BASE + endpoint;
    
    const proxyOptions: RequestInit = {
      ...options,
      headers: {
        ...options.headers,
        'X-Proxy-Target': targetUrl,
        'X-Proxy-Method': options.method || 'GET',
        'Content-Type': 'application/json'
      },
      // NO incluir credentials aquí para evitar el problema CORS
    };

    console.log('Proxy request to:', targetUrl, 'via', this.PROXY_URL);
    
    return fetch(this.PROXY_URL, proxyOptions);
  }

  async login(email: string, password: string, rememberMe: boolean = false): Promise<any> {
    try {
      console.log('Intentando login con:', { email, url: this.API_BASE + '/api/Auth/Login' });
      
      const response = await this.proxyRequest('/api/Auth/Login', {
        method: 'POST',
        body: JSON.stringify({ email, password, rememberMe })
      });

      console.log('Respuesta del servidor:', response.status, response.statusText);
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();
      console.log('Datos de respuesta:', data);
      
      if (data.success) {
        // Guardar token si viene en la respuesta
        if (data.token) {
          this.token.set(data.token);
          sessionStorage.setItem('auth_token', data.token);
        }
        return data;
      } else {
        throw new Error(data.message || 'Login falló');
      }
    } catch (error) {
      console.error('Error en login:', error);
      throw error;
    }
  }

  async getProfile(): Promise<any> {
    try {
      const response = await this.proxyRequest('/api/Account/Profile', {
        method: 'GET'
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      return await response.json();
    } catch (error) {
      console.error('Error getting profile:', error);
      throw error;
    }
  }

  async logout(): Promise<any> {
    try {
      const response = await this.proxyRequest('/api/Account/Logout', {
        method: 'POST'
      });

      // Limpiar token local independientemente de la respuesta
      this.token.set(null);
      sessionStorage.removeItem('auth_token');

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      return await response.json();
    } catch (error) {
      console.error('Error en logout:', error);
      // Limpiar token local aunque falle la petición
      this.token.set(null);
      sessionStorage.removeItem('auth_token');
      throw error;
    }
  }
}
