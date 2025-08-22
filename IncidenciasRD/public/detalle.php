<?php
$title = "Detalle de Incidencia";
$pdo = db();
$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
  SELECT i.*, GROUP_CONCAT(t.name) as types, u.name as reporter
  FROM incidents i
  LEFT JOIN incident_incident_type it ON it.incident_id=i.id
  LEFT JOIN incident_types t ON t.id=it.type_id
  LEFT JOIN users u ON u.id=i.reporter_id
  WHERE i.id=? AND i.status='approved'
  GROUP BY i.id
");
$stmt->execute([$id]);
$inc = $stmt->fetch();

if (!$inc) { http_response_code(404); exit("No encontrado"); }

$photos = $pdo->prepare("SELECT * FROM incident_photos WHERE incident_id=?");
$photos->execute([$id]);
$photos = $photos->fetchAll();

ob_start();
?>
<h2><?= e($inc['title']) ?></h2>
<p><strong>Fecha:</strong> <?= e($inc['occurred_at']) ?></p>
<p><strong>Tipos:</strong> <?= e($inc['types']) ?></p>
<p><strong>Provincia/Municipio:</strong> <?= e($inc['province_id']) ?>/<?= e($inc['municipality_id']) ?></p>
<p><strong>Muertos:</strong> <?= e($inc['deaths']) ?>, <strong>Heridos:</strong> <?= e($inc['injuries']) ?></p>
<p><strong>Pérdida estimada:</strong> RD$<?= e($inc['loss_dop']) ?></p>
<p><strong>Descripción:</strong> <?= nl2br(e($inc['description'])) ?></p>

<?php if ($photos): ?>
  <div class="row">
    <?php foreach($photos as $ph): ?>
      <div class="col-md-3 mb-2">
        <img src="assets/uploads/<?= e($ph['path']) ?>" class="img-fluid rounded">
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<div id="map" style="height:300px;" class="mb-3"></div>

<script>
var map = L.map('map').setView([<?= $inc['latitude'] ?? 18.5 ?>, <?= $inc['longitude'] ?? -69.9 ?>], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
L.marker([<?= $inc['latitude'] ?? 18.5 ?>, <?= $inc['longitude'] ?? -69.9 ?>]).addTo(map);
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
