import { Component, OnInit, Inject } from '@angular/core';
import { FormGroup, FormControl, ReactiveFormsModule, FormsModule } from '@angular/forms';
import { AuthService } from '../../services/auth/auth.service';
import { StandaloneAuthService } from '../../services/auth/standalone-auth.service';
import { Router, RouterLink } from '@angular/router';
import { GoogleSigninButtonModule, SocialAuthService } from '@abacritt/angularx-social-login';
import { CommonModule } from '@angular/common';
import { HttpClient } from '@angular/common/http';
import { firstValueFrom } from 'rxjs';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [ReactiveFormsModule, FormsModule, RouterLink, GoogleSigninButtonModule, CommonModule],
  templateUrl: './login.component.html',
  styleUrl: './login.component.css'
})
export class LoginComponent implements OnInit {
  loginError: string = '';
  isProcessingLogin: boolean = false;

  constructor(
    private authService: AuthService,
    @Inject(StandaloneAuthService) private standaloneAuthService: StandaloneAuthService,
    private authGoogle: SocialAuthService,
    private router: Router,
    private http: HttpClient
  ) {}

  showPassword: boolean = false;
  errorMessages: { [key: string]: string[] } = {};

  form = new FormGroup({
    email: new FormControl('cesarmatelat@gmail.com'), // Prellenado para pruebas
    password: new FormControl('test123'), // Prellenado para pruebas
  });

  // Getter público para el template
  get standaloneAuth() {
    return this.authService;
  }

  ngOnInit() {
    if (this.authService.isAuthenticated()) {
      this.router.navigate(['/profile']);
    }
    this.authGoogle.authState.subscribe((user) => {
      if (user && !this.isProcessingLogin) {
        this.isProcessingLogin = true;
        this.verifyGoogleUserServerSide(user.idToken);
      }
    });
}

  async verifyGoogleUserServerSide(token: string): Promise<void> {
      try {
        await this.authService.googleLogin(token);
        this.router.navigate(['/profile']);
        this.isProcessingLogin = false;
      }
      catch (error) {
        console.error('Error al verificar el usuario en el servidor:', error);
        this.loginError = 'Error al verificar credenciales. Por favor, intente nuevamente.';
        this.isProcessingLogin = false;
        // Limpiar datos almacenados en caso de error
        sessionStorage.removeItem('auth_token');
    }
  }

  async onSubmit(): Promise<void> {
    this.errorMessages = {};
    this.loginError = '';
    
    try {
      const { email, password } = this.form.value;
      
      console.log('Usando AuthService real para login');
      
      // Usar el AuthService real en lugar del standalone
      await this.authService.login(email!, password!);
      console.log('Login exitoso con AuthService real');
      
      // Verificar que el usuario esté autenticado
      console.log('Usuario autenticado:', this.authService.isAuthenticated());
      console.log('Token:', this.authService.token());
      
      // Intentar navegar al perfil
      console.log('Intentando navegar a /profile...');
      const navigationResult = await this.router.navigate(['/profile']);
      console.log('Resultado de navegación:', navigationResult);
      
      if (!navigationResult) {
        console.error('La navegación falló');
        this.loginError = 'Login exitoso pero la navegación falló. Recarga la página.';
      }
    } catch (error: any) {
      console.error('Error en login:', error);
      this.loginError = 'Error de autenticación: ' + (error.message || error.error?.message || 'Error desconocido');
      
      if (error.message && error.message.includes('|')) {
        const errorList = error.message.split('|');
        errorList.forEach((err: string) => {
            const [field, message] = err.split(': ');
            if (field && message) {
                this.errorMessages[field] = this.errorMessages[field] || [];
                this.errorMessages[field].push(message);
            }
        });
      }
    }
  }

  toggleServer(): void {
    // El AuthService real siempre usa la URL de Ngrok configurada
    console.log('AuthService real está usando:', 'https://settled-muskrat-peaceful.ngrok-free.app');
    this.loginError = '';
    this.errorMessages = {};
  }
}