import { Component, OnInit } from '@angular/core';
import { FormGroup, FormControl, ReactiveFormsModule, FormsModule } from '@angular/forms';
import { AuthService } from '../../services/auth/auth.service';
import { StandaloneAuthService } from '../../services/auth/standalone-auth.service';
import { Router, RouterLink } from '@angular/router';
import { GoogleSigninButtonModule, SocialAuthService } from '@abacritt/angularx-social-login';
import { CommonModule } from '@angular/common';
import { HttpClient } from '@angular/common/http';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [ReactiveFormsModule, FormsModule, RouterLink, GoogleSigninButtonModule, CommonModule],
  templateUrl: './login.component.html',
  styleUrl: './login.component.css'
})
export class LoginComponent implements OnInit  {
  loginError: string = '';
  isProcessingLogin: boolean = false;
  useStandalone: boolean = true; // Flag para cambiar entre servicios

  constructor(
    private authService: AuthService, 
    private standaloneAuthService: StandaloneAuthService,
    private authGoogle: SocialAuthService, 
    private router: Router, 
    private http: HttpClient
  ){}

  showPassword: boolean = false;
  errorMessages: { [key: string]: string[] } = {};

  form = new FormGroup({
    email: new FormControl('cesarmatelat@gmail.com'), // Prellenado para pruebas
    password: new FormControl('test123'), // Prellenado para pruebas
  });

  ngOnInit() {
    const currentService = this.useStandalone ? this.standaloneAuthService : this.authService;
    
    if (currentService.isAuthenticated()) {
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
      
      if (this.useStandalone) {
        // Usar servidor standalone
        console.log('Usando servidor standalone para login');
        const response = await this.standaloneAuthService.login(email!, password!).toPromise();
        console.log('Login exitoso con standalone:', response);
        await this.router.navigate(['/profile']);
      } else {
        // Usar servidor Apache original
        console.log('Usando servidor Apache para login');
        await this.authService.login(email!, password!);
        await this.router.navigate(['/profile']);
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

  toggleService(): void {
    this.useStandalone = !this.useStandalone;
    this.loginError = '';
    this.errorMessages = {};
    console.log('Cambiado a:', this.useStandalone ? 'Servidor Standalone' : 'Servidor Apache');
  }
}
