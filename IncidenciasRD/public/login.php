<?php
require __DIR__ . '/../src/db.php';
require __DIR__ . '/../src/helpers.php';
require __DIR__ . '/../src/auth.php';

start_session();
$title = "Ingreso";
$pdo = db();
$err = '';

if (is_post()) {
  csrf_require();
  $email = trim($_POST['email'] ?? '');
  $pass  = $_POST['password'] ?? '';

  $stmt = $pdo->prepare("
    SELECT u.*, r.name AS role_name
    FROM users u
    JOIN roles r ON r.id = u.role_id
    WHERE u.email = ? AND u.status='active' LIMIT 1
  ");
  $stmt->execute([$email]);
  $u = $stmt->fetch();

  if ($u && !empty($u['password_hash']) && password_verify($pass, $u['password_hash'])) {
    login_user($u);
    redirect('index.php?page=mapa');
  } else {
    $err = "Credenciales inválidas.";
  }
}

ob_start();
?>
<div class="row justify-content-center">
  <div class="col-md-4">
    <div class="card p-3">
      <h3 class="mb-3">Iniciar sesión</h3>
      <?php if ($err): ?>
        <div class="alert alert-danger"><?= e($err) ?></div>
      <?php endif; ?>
      <form method="post">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" required autofocus>
        </div>
        <div class="mb-3">
          <label class="form-label">Contraseña</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <button class="btn btn-primary w-100">Entrar</button>
      </form>
      <hr>
      <a class="btn btn-outline-secondary w-100 mb-2" href="super-login.php">Entrar como Validador/Admin</a>
      <!-- Si luego activas OAuth:
      <a class="btn btn-outline-dark w-100 mb-2" href="callback-google.php">Continuar con Google</a>
      <a class="btn btn-outline-dark w-100" href="callback-microsoft.php">Continuar con Microsoft</a>
      -->
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
