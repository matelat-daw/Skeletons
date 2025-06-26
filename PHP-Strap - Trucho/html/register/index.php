<?php
echo '
<div class="auth-container">
    <h1>Crear Cuenta</h1>
    <form class="auth-form" id="registerForm">
        <div class="form-group">
            <label for="reg-name">Nombre completo</label>
            <input type="text" id="reg-name" name="name" required>
        </div>
        
        <div class="form-group">
            <label for="reg-email">Correo electrónico</label>
            <input type="email" id="reg-email" name="email" required>
        </div>
        
        <div class="form-group">
            <label for="reg-password">Contraseña</label>
            <input type="password" id="reg-password" name="password" required>
        </div>
        
        <div class="form-group">
            <label for="reg-confirm-password">Confirmar contraseña</label>
            <input type="password" id="reg-confirm-password" name="confirm_password" required>
        </div>
        
        <div class="form-group checkbox-group">
            <input type="checkbox" id="reg-terms" name="terms" required>
            <label for="reg-terms">Acepto los términos y condiciones</label>
        </div>
        
        <button type="submit" class="btn-submit">Crear Cuenta</button>
        
        <p class="auth-link">
            ¿Ya tienes cuenta? 
            <a href="#" onclick="showLoginModal(); return false;">Inicia sesión aquí</a>
        </p>
    </form>
</div>
';
?>