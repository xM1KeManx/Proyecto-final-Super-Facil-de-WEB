<?php
require __DIR__ . '/../src/db.php';
require __DIR__ . '/../src/helpers.php';
require __DIR__ . '/../src/auth.php';

start_session();
$title = "/super";
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

  if ($u && in_array($u['role_id'], [2,3], true) && password_verify($pass, $u['password_hash'])) {
    login_user($u);
    redirect('super-dashboard.php');
  } else {
    $err = "Credenciales inválidas o rol no autorizado.";
  }
}

ob_start();
?>
<div class="row justify-content-center">
  <div class="col-md-4">
    <div class="card p-3">
      <h3 class="mb-3">/super</h3>
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
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
