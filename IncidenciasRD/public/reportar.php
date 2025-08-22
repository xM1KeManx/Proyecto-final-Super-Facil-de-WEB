<?php
require_login();
$title = "Reportar Incidencia";
$pdo = db();

if (is_post()) {
  csrf_require();
  $stmt = $pdo->prepare("
    INSERT INTO incidents (reporter_id, occurred_at, title, description, province_id, municipality_id, neighborhood_id, latitude, longitude, deaths, injuries, loss_dop, social_link, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
  ");
  $stmt->execute([
    $_SESSION['user']['id'],
    $_POST['occurred_at'],
    $_POST['title'],
    $_POST['description'],
    $_POST['province_id'] ?: null,
    $_POST['municipality_id'] ?: null,
    $_POST['neighborhood_id'] ?: null,
    $_POST['latitude'] ?: null,
    $_POST['longitude'] ?: null,
    $_POST['deaths'] ?: 0,
    $_POST['injuries'] ?: 0,
    $_POST['loss_dop'] ?: 0,
    $_POST['social_link'] ?: null
  ]);
  $incident_id = $pdo->lastInsertId();

  // tipos
  if (!empty($_POST['types'])) {
    $ins = $pdo->prepare("INSERT INTO incident_incident_type (incident_id, type_id) VALUES (?, ?)");
    foreach ($_POST['types'] as $t) {
      $ins->execute([$incident_id, $t]);
    }
  }

  // fotos
  if (!empty($_FILES['photos']['name'][0])) {
    foreach ($_FILES['photos']['tmp_name'] as $i => $tmp) {
      if ($_FILES['photos']['error'][$i] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['photos']['name'][$i], PATHINFO_EXTENSION);
        $fname = uniqid() . "." . $ext;
        move_uploaded_file($tmp, __DIR__ . "/assets/uploads/$fname");
        $pdo->prepare("INSERT INTO incident_photos (incident_id, path) VALUES (?, ?)")->execute([$incident_id, $fname]);
      }
    }
  }

  redirect("?page=detalle&id=$incident_id");
}

$types = $pdo->query("SELECT * FROM incident_types ORDER BY name")->fetchAll();
$provinces = $pdo->query("SELECT * FROM provinces ORDER BY name")->fetchAll();

ob_start();
?>
<h2>Reportar Incidencia</h2>
<form method="post" enctype="multipart/form-data" class="card p-3">
  <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
  <div class="mb-3">
    <label class="form-label">Fecha de ocurrencia</label>
    <input type="datetime-local" name="occurred_at" class="form-control" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Título</label>
    <input type="text" name="title" class="form-control" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Descripción</label>
    <textarea name="description" class="form-control" rows="3"></textarea>
  </div>
  <div class="mb-3">
    <label class="form-label">Tipo</label><br>
    <?php foreach($types as $t): ?>
      <div class="form-check form-check-inline">
        <input type="checkbox" class="form-check-input" name="types[]" value="<?= $t['id'] ?>">
        <label class="form-check-label"><?= e($t['name']) ?></label>
      </div>
    <?php endforeach; ?>
  </div>
  <div class="row">
    <div class="col-md-4 mb-3">
      <label class="form-label">Provincia</label>
      <select name="province_id" class="form-select">
        <option value="">--</option>
        <?php foreach($provinces as $p): ?>
          <option value="<?= $p['id'] ?>"><?= e($p['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4 mb-3">
      <label class="form-label">Municipio</label>
      <input type="text" name="municipality_id" class="form-control">
    </div>
    <div class="col-md-4 mb-3">
      <label class="form-label">Barrio</label>
      <input type="text" name="neighborhood_id" class="form-control">
    </div>
  </div>
  <div class="row">
    <div class="col-md-6 mb-3">
      <label class="form-label">Latitud</label>
      <input type="text" name="latitude" class="form-control">
    </div>
    <div class="col-md-6 mb-3">
      <label class="form-label">Longitud</label>
      <input type="text" name="longitude" class="form-control">
    </div>
  </div>
  <div class="row">
    <div class="col-md-4 mb-3">
      <label class="form-label">Muertos</label>
      <input type="number" name="deaths" class="form-control" min="0">
    </div>
    <div class="col-md-4 mb-3">
      <label class="form-label">Heridos</label>
      <input type="number" name="injuries" class="form-control" min="0">
    </div>
    <div class="col-md-4 mb-3">
      <label class="form-label">Pérdida RD$</label>
      <input type="number" name="loss_dop" class="form-control" min="0" step="0.01">
    </div>
  </div>
  <div class="mb-3">
    <label class="form-label">Link a redes sociales</label>
    <input type="url" name="social_link" class="form-control">
  </div>
  <div class="mb-3">
    <label class="form-label">Fotos</label>
    <input type="file" name="photos[]" multiple class="form-control">
  </div>
  <button class="btn btn-primary">Enviar Reporte</button>
</form>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
