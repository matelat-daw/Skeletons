<?php
echo '
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
      <div class="card shadow-sm">
        <div class="card-body">
          <h2 class="card-title text-center mb-4">Crear Cuenta</h2>
          <form id="registerForm" novalidate>
            <div id="registerAlert" class="alert alert-danger d-none" role="alert"></div>
            <div class="mb-3">
              <label for="reg-name" class="form-label">Nombre completo</label>
              <input type="text" class="form-control" id="reg-name" name="name" required>
              <div class="invalid-feedback">Por favor ingresa tu nombre completo.</div>
            </div>
            <div class="mb-3">
              <label for="reg-email" class="form-label">Correo electrónico</label>
              <input type="email" class="form-control" id="reg-email" name="email" required>
              <div class="invalid-feedback">Por favor ingresa un correo válido.</div>
            </div>
            <div class="mb-3">
              <label for="reg-password" class="form-label">Contraseña</label>
              <input type="password" class="form-control" id="reg-password" name="password" required minlength="6">
              <div class="invalid-feedback">La contraseña debe tener al menos 6 caracteres.</div>
            </div>
            <div class="mb-3">
              <label for="reg-confirm-password" class="form-label">Confirmar contraseña</label>
              <input type="password" class="form-control" id="reg-confirm-password" name="confirm_password" required>
              <div class="invalid-feedback">Las contraseñas no coinciden.</div>
            </div>
            <div class="mb-3 form-check">
              <input type="checkbox" class="form-check-input" id="reg-terms" name="terms" required>
              <label class="form-check-label" for="reg-terms">Acepto los términos y condiciones</label>
              <div class="invalid-feedback">Debes aceptar los términos y condiciones.</div>
            </div>
            <button type="submit" class="btn btn-primary w-100">Crear Cuenta</button>
          </form>
          <p class="text-center mt-3 mb-0">
            ¿Ya tienes cuenta? <a href="#" onclick="showLoginModal(); return false;">Inicia sesión aquí</a>
          </p>
        </div>
      </div>
    </div>
  </div>
</div>';
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
  var form = document.getElementById('registerForm');
  var password = document.getElementById('reg-password');
  var confirmPassword = document.getElementById('reg-confirm-password');
  var alertBox = document.getElementById('registerAlert');
  form.addEventListener('submit', function(event) {
    event.preventDefault();
    event.stopPropagation();
    var valid = form.checkValidity();
    if (password.value !== confirmPassword.value) {
      confirmPassword.classList.add('is-invalid');
      valid = false;
    } else {
      confirmPassword.classList.remove('is-invalid');
    }
    if (!valid) {
      form.classList.add('was-validated');
      alertBox.textContent = 'Por favor corrige los errores en el formulario.';
      alertBox.classList.remove('d-none');
    } else {
      alertBox.classList.add('d-none');
      // Aquí puedes agregar la lógica para enviar el formulario vía AJAX o PHP
      form.submit();
    }
  });
});
</script>