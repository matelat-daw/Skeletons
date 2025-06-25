<?php
echo '
    <nav>
        <button id="menu-toggle" aria-label="Abrir menú">&#9776;</button>
        <ul id="menu">
            <li><a href="#" onclick="loadPage(\'home\'); return false;">Inicio</a></li>
            <li><a href="#" onclick="loadPage(\'about\'); return false;">Acerca de</a></li>
            <li><a href="#" onclick="loadPage(\'contact\'); return false;">Contacto</a></li>
            <li class="mobile-auth">
                <a href="#" onclick="loadPage(\'register\'); return false;">Registrarse</a>
            </li>
            <li class="mobile-auth">
                <a href="#" onclick="showLoginModal(); return false;">Login</a>
            </li>
        </ul>
        <div class="auth-buttons">
            <button class="btn-register" onclick="loadPage(\'register\'); return false;">Registrarse</button>
            <button class="btn-login" onclick="showLoginModal(); return false;">Login</button>
        </div>
    </nav>
';