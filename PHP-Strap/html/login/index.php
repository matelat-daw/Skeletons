<?php
echo '
<div class="modal-content">
    <div class="modal-header">
        <h2>Iniciar Sesión</h2>
        <span class="close" onclick="closeLoginModal()">&times;</span>
    </div>
    <form class="auth-form" id="loginForm">
        <div class="form-group">
            <label for="login-email">Correo electrónico</label>
            <input type="email" id="login-email" name="email" required>
        </div>
        
        <div class="form-group">
            <label for="login-password">Contraseña</label>
            <input type="password" id="login-password" name="password" required>
        </div>
        
        <div class="form-group checkbox-group">
            <input type="checkbox" id="remember-me" name="remember">
            <label for="remember-me">Recordarme</label>
        </div>
        
        <button type="submit" class="btn-submit">Iniciar Sesión</button>
        
        <div class="auth-links">
            <a href="#" class="forgot-password">¿Olvidaste tu contraseña?</a>
            <p>¿No tienes cuenta? 
                <a href="#" onclick="closeLoginModal(); loadPage(\'register\'); return false;">Regístrate aquí</a>
            </p>
        </div>
    </form>
</div>
';
?>