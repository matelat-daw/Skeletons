<?php
echo '
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container-fluid">
    <a class="navbar-brand" href="#" onclick="loadPage(\'home\'); return false;">Mi Web</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link" href="#" onclick="loadPage(\'home\'); return false;" id="nav-home">Inicio</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#" onclick="loadPage(\'about\'); return false;" id="nav-about">Acerca de</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#" onclick="loadPage(\'contact\'); return false;" id="nav-contact">Contacto</a>
        </li>
      </ul>
      <div class="d-flex">
        <button class="btn btn-outline-light me-2" onclick="loadPage(\'register\'); return false;">Registrarse</button>
        <button class="btn btn-light" onclick="showLoginModal(); return false;">Login</button>
      </div>
    </div>
  </div>
</nav>
';
?>
