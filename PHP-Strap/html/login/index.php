<?php
echo '
<div class="modal-content p-0 border-0 rounded-3 shadow-lg" style="max-width:400px;margin:auto;">
  <div class="modal-header bg-primary text-white rounded-top-3">
    <h2 class="modal-title fs-4 mb-0">Iniciar Sesión</h2>
    <button type="button" class="btn-close btn-close-white ms-auto" aria-label="Cerrar" onclick="closeLoginModal()"></button>
  </div>
  <div class="p-4">
    <form id="loginForm">
      <div class="mb-3">
        <label for="login-email" class="form-label">Correo electrónico</label>
        <input type="email" class="form-control" id="login-email" name="email" required>
      </div>
      <div class="mb-3">
        <label for="login-password" class="form-label">Contraseña</label>
        <input type="password" class="form-control" id="login-password" name="password" required>
      </div>
      <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="remember-me" name="remember">
        <label class="form-check-label" for="remember-me">Recordarme</label>
      </div>
      <button type="submit" class="btn btn-primary w-100">Iniciar Sesión</button>
      <div class="text-center mt-3">
        <a href="#" class="small">¿Olvidaste tu contraseña?</a>
        <p class="mb-0 mt-2 small">¿No tienes cuenta? <a href="#" onclick="closeLoginModal(); loadPage(\'register\'); return false;">Regístrate aquí</a></p>
      </div>
    </form>
  </div>
</div>';
?>