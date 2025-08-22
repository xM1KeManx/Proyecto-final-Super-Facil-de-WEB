<?php $user = $_SESSION['user'] ?? null; ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="index.php">Incidencias RD</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="?page=mapa">Mapa</a></li>
        <li class="nav-item"><a class="nav-link" href="?page=lista">Lista</a></li>
        <?php if ($user && in_array($user['role'], ['reportero','admin'])): ?>
          <li class="nav-item"><a class="nav-link" href="?page=reportar">Reportar</a></li>
        <?php endif; ?>
        <?php if ($user && in_array($user['role'], ['validador','admin'])): ?>
          <li class="nav-item"><a class="nav-link" href="super-dashboard.php">/super</a></li>
        <?php endif; ?>
      </ul>
      <ul class="navbar-nav ms-auto">
        <?php if ($user): ?>
          <li class="nav-item"><span class="navbar-text">👤 <?= e($user['name']) ?></span></li>
          <li class="nav-item"><a class="nav-link" href="logout.php">Salir</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="login.php">Ingresar</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
