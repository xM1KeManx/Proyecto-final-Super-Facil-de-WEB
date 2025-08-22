<?php
require __DIR__ . '/../src/db.php';
require __DIR__ . '/../src/helpers.php';
require __DIR__ . '/../src/auth.php';

start_session();
ensure_super(); // Función que verifica rol validator/admin

$title = "Panel Super";
$pdo = db();

// Estadísticas: cantidad de incidencias por tipo
$stats = $pdo->query("
  SELECT t.name, COUNT(it.incident_id) AS total
  FROM incident_types t
  LEFT JOIN incident_incident_type it ON it.type_id = t.id
  LEFT JOIN incidents i ON i.id = it.incident_id AND i.status='approved'
  GROUP BY t.id
")->fetchAll();

// Catálogos
$provincias = $pdo->query("SELECT * FROM provinces ORDER BY name")->fetchAll();
$municipios = $pdo->query("SELECT * FROM municipalities ORDER BY name")->fetchAll();
$barrios    = $pdo->query("SELECT * FROM neighborhoods ORDER BY name")->fetchAll();
$tipos      = $pdo->query("SELECT * FROM incident_types ORDER BY name")->fetchAll();

ob_start();
?>

<h2>Panel de Administración /super</h2>

<h4>Estadísticas de incidencias</h4>
<canvas id="chartIncidencias" style="max-width:600px;"></canvas>

<h4 class="mt-4">Catálogos</h4>

<!-- Provincias -->
<div class="mb-3">
  <h5>Provincias</h5>
  <?php foreach ($provincias as $p): ?>
    <div><?= e($p['name']) ?> <a href="editar_provincia.php?id=<?= $p['id'] ?>">Editar</a></div>
  <?php endforeach; ?>
  <a href="agregar_provincia.php" class="btn btn-sm btn-primary mt-1">Agregar provincia</a>
</div>

<!-- Tipos de incidencias -->
<div class="mb-3">
  <h5>Tipos de Incidencias</h5>
  <?php foreach ($tipos as $t): ?>
    <div><?= e($t['name']) ?> <a href="editar_tipo.php?id=<?= $t['id'] ?>">Editar</a></div>
  <?php endforeach; ?>
  <a href="agregar_tipo.php" class="btn btn-sm btn-primary mt-1">Agregar tipo</a>
</div>

<!-- Puedes repetir lo mismo para municipios y barrios -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('chartIncidencias').getContext('2d');
const chart = new Chart(ctx, {
  type: 'bar',
  data: {
    labels: <?= json_encode(array_column($stats, 'name')) ?>,
    datasets: [{
      label: 'Cantidad de incidencias',
      data: <?= json_encode(array_column($stats, 'total')) ?>,
      backgroundColor: 'rgba(54, 162, 235, 0.6)'
    }]
  },
  options: { responsive: true, plugins: { legend: { display: false } } }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
