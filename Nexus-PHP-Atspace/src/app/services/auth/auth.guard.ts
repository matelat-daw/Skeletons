import { CanActivateFn } from '@angular/router';
import { AuthService } from './auth.service';
import { StandaloneAuthService } from './standalone-auth.service';
import { Router } from '@angular/router';
import { inject } from '@angular/core';

export const authGuard: CanActivateFn = () => {
  const auth = inject(AuthService);
  const standaloneAuth = inject(StandaloneAuthService) as StandaloneAuthService;
  const router = inject(Router);
  
  // Verificar ambos servicios de autenticación
  const isAuthenticated = auth.isAuthenticated() || standaloneAuth.isAuthenticated();
  
  console.log('AuthGuard - Auth original:', auth.isAuthenticated());
  console.log('AuthGuard - Standalone auth:', standaloneAuth.isAuthenticated());
  console.log('AuthGuard - Resultado final:', isAuthenticated);
  
  if (!isAuthenticated) {
    console.log('AuthGuard - Redirigiendo a login');
    router.navigate(['/login']);
    return false;
  }
  
  console.log('AuthGuard - Acceso permitido');
  return true;
};