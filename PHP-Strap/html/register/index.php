<?php
echo '
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
      <div class="card shadow-lg border-0 rounded-3">
        <div class="card-header bg-dark text-white text-center rounded-top-3">
          <h1 class="card-title fs-4 mb-0">Crear Cuenta</h1>
        </div>
        <div class="card-body p-4">
          <form id="registerForm">
            <div class="mb-3">
              <label for="reg-name" class="form-label">Nombre completo</label>
              <input type="text" class="form-control" id="reg-name" name="name" required>
            </div>
            
            <div class="mb-3">
              <label for="reg-email" class="form-label">Correo electrónico</label>
              <input type="email" class="form-control" id="reg-email" name="email" required>
            </div>
            
            <div class="mb-3">
              <label for="reg-password" class="form-label">Contraseña</label>
              <input type="password" class="form-control" id="reg-password" name="password" required>
            </div>
            
            <div class="mb-3">
              <label for="reg-confirm-password" class="form-label">Confirmar contraseña</label>
              <input type="password" class="form-control" id="reg-confirm-password" name="confirm_password" required>
            </div>
            
            <div class="mb-3 form-check">
              <input type="checkbox" class="form-check-input" id="reg-terms" name="terms" required>
              <label class="form-check-label" for="reg-terms">Acepto los términos y condiciones</label>
            </div>
            
            <button type="submit" class="btn btn-dark w-100">Crear Cuenta</button>
            
            <div class="text-center mt-3">
              <p class="mb-0 small">¿Ya tienes cuenta? 
                <a href="#" onclick="showLoginModal(); return false;">Inicia sesión aquí</a>
              </p>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
';
?>