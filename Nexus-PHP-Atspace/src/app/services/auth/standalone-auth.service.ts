import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable, BehaviorSubject, firstValueFrom } from 'rxjs';
import { tap, catchError } from 'rxjs/operators';
import { of } from 'rxjs';

export interface User {
  id: number;
  email: string;
  name: string;
  createdAt?: string;
  isEmailConfirmed?: boolean;
}

export interface LoginRequest {
  email: string;
  password: string;
}

export interface LoginResponse {
  success: boolean;
  message: string;
  user: User;
}

@Injectable({
  providedIn: 'root'
})
export class StandaloneAuthService {
  private readonly localUrl = 'http://localhost:8000';
  private readonly ngrokUrl = 'https://b895-88-24-26-59.ngrok-free.app';
  private useNgrok = false; // Por defecto usar local

  private currentUserSubject = new BehaviorSubject<User | null>(null);
  private isAuthenticatedSubject = new BehaviorSubject<boolean>(false);

  public currentUser$ = this.currentUserSubject.asObservable();
  public isAuthenticated$ = this.isAuthenticatedSubject.asObservable();

  constructor(private http: HttpClient) {
    // Cargar usuario desde localStorage si existe
    this.loadUserFromStorage();
    // Verificar si hay sesión activa al inicializar
    this.checkAuthStatus();
  }

  private loadUserFromStorage(): void {
    const storedUser = localStorage.getItem('standalone_auth_user');
    if (storedUser) {
      try {
        const user: User = JSON.parse(storedUser);
        this.currentUserSubject.next(user);
        this.isAuthenticatedSubject.next(true);
        console.log('Usuario cargado desde localStorage:', user);
      } catch (error) {
        console.error('Error parsing stored user:', error);
        localStorage.removeItem('standalone_auth_user');
      }
    }
  }

  private saveUserToStorage(user: User): void {
    localStorage.setItem('standalone_auth_user', JSON.stringify(user));
  }

  private clearUserFromStorage(): void {
    localStorage.removeItem('standalone_auth_user');
  }

  private get baseUrl(): string {
    return this.useNgrok ? this.ngrokUrl : this.localUrl;
  }

  setUseNgrok(useNgrok: boolean): void {
    this.useNgrok = useNgrok;
    console.log('Cambiado a:', useNgrok ? 'Ngrok URL' : 'Local URL', this.baseUrl);
  }

  getCurrentUrl(): string {
    return this.baseUrl;
  }

  private getHttpOptions() {
    const headers: any = {
      'Content-Type': 'application/json'
    };
    
    // Añadir header de Ngrok si estamos usando Ngrok
    if (this.useNgrok) {
      headers['ngrok-skip-browser-warning'] = 'true';
    }
    
    return {
      headers: new HttpHeaders(headers),
      withCredentials: true // Importante para cookies
    };
  }

  login(email: string, password: string): Observable<LoginResponse> {
    const loginData: LoginRequest = { email, password };
    
    console.log('Intentando login con servidor standalone:', { email, url: `${this.baseUrl}/api/Auth/Login` });
    
    return this.http.post<LoginResponse>(`${this.baseUrl}/api/Auth/Login`, loginData, this.getHttpOptions())
      .pipe(
        tap(response => {
          console.log('Respuesta de login standalone:', response);
          if (response.success && response.user) {
            this.currentUserSubject.next(response.user);
            this.isAuthenticatedSubject.next(true);
            this.saveUserToStorage(response.user); // Guardar en localStorage
            console.log('Usuario guardado en localStorage');
          }
        }),
        catchError(error => {
          console.error('Error en login standalone:', error);
          this.currentUserSubject.next(null);
          this.isAuthenticatedSubject.next(false);
          this.clearUserFromStorage();
          throw error;
        })
      );
  }

  logout(): Observable<any> {
    return this.http.post(`${this.baseUrl}/api/Account/Logout`, {}, this.getHttpOptions())
      .pipe(
        tap(() => {
          this.currentUserSubject.next(null);
          this.isAuthenticatedSubject.next(false);
          this.clearUserFromStorage();
          console.log('Logout exitoso - usuario eliminado de localStorage');
        }),
        catchError(error => {
          console.error('Error en logout standalone:', error);
          // Limpiar estado local aunque el logout remoto falle
          this.currentUserSubject.next(null);
          this.isAuthenticatedSubject.next(false);
          this.clearUserFromStorage();
          return of(null);
        })
      );
  }

  getProfile(): Observable<{ success: boolean; user: User }> {
    return this.http.get<{ success: boolean; user: User }>(`${this.baseUrl}/api/Account/Profile`, this.getHttpOptions())
      .pipe(
        tap(response => {
          console.log('Respuesta de perfil standalone:', response);
          if (response.success && response.user) {
            this.currentUserSubject.next(response.user);
            this.isAuthenticatedSubject.next(true);
            this.saveUserToStorage(response.user);
          }
        }),
        catchError(error => {
          console.error('Error en perfil standalone:', error);
          this.currentUserSubject.next(null);
          this.isAuthenticatedSubject.next(false);
          this.clearUserFromStorage();
          throw error;
        })
      );
  }

  private checkAuthStatus(): void {
    // Solo verificar si ya tenemos un usuario en localStorage
    if (this.getCurrentUser()) {
      console.log('Usuario ya autenticado desde localStorage');
      return;
    }
    
    this.getProfile().subscribe({
      next: (response) => {
        console.log('Check auth status exitoso:', response);
      },
      error: (error) => {
        console.log('Check auth status falló (esperado si no hay sesión):', error);
        this.currentUserSubject.next(null);
        this.isAuthenticatedSubject.next(false);
        this.clearUserFromStorage();
      }
    });
  }

  getCurrentUser(): User | null {
    return this.currentUserSubject.value;
  }

  isAuthenticated(): boolean {
    return this.isAuthenticatedSubject.value;
  }
}
