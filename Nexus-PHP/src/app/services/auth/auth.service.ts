import { Injectable, signal, computed } from '@angular/core';
import { SocialAuthService } from '@abacritt/angularx-social-login';
import { User } from '../../models/user';

@Injectable({ providedIn: 'root' })
export class AuthService {
  constructor(private authGoogle: SocialAuthService) {}
  private API_URL = 'http://localhost:8080/api/Auth';
  private passwordRegex = /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*\W).{8,}$/;
  private emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

  token = signal<string | null>(sessionStorage.getItem('auth_token'));
  user = signal<User | null>(null);
  profileImage = signal('');
  isProcessingLogin = false;
  isAuthenticated = computed(() => !!this.token());

  private handleErrors(errors: string[]): never {
    throw new Error(errors.join('|'));
  }

  private async fetchAndHandle(url: string, options: RequestInit, errorMap?: Record<string, string>): Promise<string> {
    const response = await fetch(url, options);
    const responseText = await response.text();
    if (!response.ok) {
      if (errorMap) {
        for (const key in errorMap) {
          if (responseText.includes(key)) this.handleErrors([errorMap[key]]);
        }
      }
      this.handleErrors([`global: ${responseText}`]);
    }
    return responseText;
  }

  async register(formData: FormData): Promise<string> {
    const errors: string[] = [];
    const nick = (formData.get('Nick')?.toString() || '').trim();
    const name = formData.get('Name')?.toString().trim();
    const surname1 = formData.get('Surname1')?.toString().trim();
    const email = formData.get('Email')?.toString().trim();
    const password = formData.get('Password')?.toString().trim();
    const password2 = formData.get('Password2')?.toString().trim();
    if (!nick) errors.push('nick: El nombre de usuario es obligatorio.');
    if (nick?.length > 20) errors.push('nick: Máximo 20 caracteres.');
    if (!name) errors.push('nombre: El nombre es obligatorio.');
    if (!surname1) errors.push('apellido: El apellido es obligatorio.');
    if (!email) errors.push('email: El email es obligatorio.');
    if (email && !this.emailRegex.test(email)) errors.push('email: Formato de email inválido.');
    if (!password) errors.push('password: La contraseña es obligatoria.');
    else {
      if (password.length < 8) errors.push('password: Debe tener al menos 8 caracteres.');
      if (!this.passwordRegex.test(password)) errors.push('password: Requiere mayúscula, minúscula, número y carácter especial.');
    }
    if (!password2 || password !== password2) errors.push('password2: Las contraseñas no coinciden.');
    if (errors.length > 0) this.handleErrors(errors);
    return await this.fetchAndHandle(
      `${this.API_URL}/Register`,
      { method: 'POST', body: formData },
      { 'Nick': 'nick: El nombre de usuario ya está registrado.', 'E-mail': 'email: El email ya está registrado.' }
    );
  }

  async login(email: string, password: string): Promise<void> {
    const errors: string[] = [];
    if (!email) errors.push('email: El email es obligatorio.');
    if (email && !this.emailRegex.test(email)) errors.push('email: Formato de email inválido.');
    if (!password) errors.push('password: La contraseña es obligatoria.');
    if (errors.length > 0) this.handleErrors(errors);
    const responseText = await this.fetchAndHandle(
      `${this.API_URL}/Login`,
      { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ email, password }),
      credentials: 'include'
     }
    );
    if (/Confirmado|confirmado/.test(responseText)) this.handleErrors(['global: Email no verificado. Por favor revisa tu correo.']);
    sessionStorage.setItem('login_method', 'local');
    this.token.set(responseText);
  }

  async googleLogin(token: string): Promise<void> {
    const responseText = await this.fetchAndHandle(
      `${this.API_URL}/GoogleLogin`,
      { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ token }) }
    );
    // sessionStorage.setItem('auth_token', responseText);
    sessionStorage.setItem('login_method', 'google');
    this.token.set(responseText);
  }

  async logout(): Promise<void> {
    await this.fetchAndHandle(
      'http://localhost:8080/api/Account/Logout',
      { method: 'POST', credentials: 'include' }
    );
    const loginMethod = sessionStorage.getItem('login_method');
    if (loginMethod === 'google') {
      this.authGoogle.signOut();
    }
    sessionStorage.removeItem('login_method');
    sessionStorage.clear();
    this.token.set(null);
    this.user.set(null);
    this.profileImage.set('');
  }
}