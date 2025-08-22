<?php
$title = "Lista de Incidencias";
$pdo = db();

// recoger filtros
$province_id = $_GET['province_id'] ?? '';
$type_id     = $_GET['type_id'] ?? '';
$date_from   = $_GET['date_from'] ?? '';
$date_to     = $_GET['date_to'] ?? '';
$q           = $_GET['q'] ?? '';

$w = ["i.status='approved'"];
$params = [];

if ($province_id !== '') { $w[] = "i.province_id = ?"; $params[] = (int)$province_id; }
if ($type_id !== '')     { $w[] = "t.id = ?";         $params[] = (int)$type_id; }
if ($date_from)          { $w[] = "i.occurred_at >= ?"; $params[] = $date_from." 00:00:00"; }
if ($date_to)            { $w[] = "i.occurred_at <= ?"; $params[] = $date_to." 23:59:59"; }
if ($q)                  { $w[] = "i.title LIKE ?";     $params[] = "%".$q."%"; }

$sql = "
  SELECT i.*, GROUP_CONCAT(DISTINCT t.name) as types, u.name as reporter
  FROM incidents i
  LEFT JOIN incident_incident_type it ON it.incident_id=i.id
  LEFT JOIN incident_types t ON t.id=it.type_id
  LEFT JOIN users u ON u.id=i.reporter_id
  ".(count($w) ? "WHERE ".implode(" AND ", $w) : "")."
  GROUP BY i.id
  ORDER BY i.occurred_at DESC
  LIMIT 100
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$incidents = $stmt->fetchAll();

// catálogos para selects
$types = $pdo->query("SELECT id, name FROM incident_types ORDER BY name")->fetchAll();
$provinces = $pdo->query("SELECT id, name FROM provinces ORDER BY name")->fetchAll();

ob_start();
?>
<h2 class="mb-3">Lista de Incidencias</h2>

<form class="row g-2 mb-3">
  <div class="col-md-3">
    <label class="form-label">Provincia</label>
    <select name="province_id" class="form-select">
      <option value="">Todas</option>
      <?php foreach($provinces as $p): ?>
        <option value="<?= $p['id'] ?>" <?= $province_id==$p['id']?'selected':'' ?>><?= e($p['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-3">
    <label class="form-label">Tipo</label>
    <select name="type_id" class="form-select">
      <option value="">Todos</option>
      <?php foreach($types as $t): ?>
        <option value="<?= $t['id'] ?>" <?= $type_id==$t['id']?'selected':'' ?>><?= e($t['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-2">
    <label class="form-label">Desde</label>
    <input type="date" name="date_from" value="<?= e($date_from) ?>" class="form-control">
  </div>
  <div class="col-md-2">
    <label class="form-label">Hasta</label>
    <input type="date" name="date_to" value="<?= e($date_to) ?>" class="form-control">
  </div>
  <div class="col-md-2">
    <label class="form-label">Título</label>
    <input type="text" name="q" value="<?= e($q) ?>" class="form-control" placeholder="Buscar...">
  </div>
  <div class="col-12 d-flex gap-2 align-items-end">
    <button class="btn btn-primary">Aplicar</button>
    <a class="btn btn-outline-secondary" href="?page=lista">Limpiar</a>
  </div>
</form>

<?php foreach ($incidents as $inc): ?>
  <div class="card-incidente">
    <h5><?= e($inc['title']) ?></h5>
    <div><strong>Fecha:</strong> <?= e($inc['occurred_at']) ?></div>
    <div><strong>Tipo:</strong> <?= e($inc['types']) ?></div>
    <div><strong>Reportado por:</strong> <?= e($inc['reporter']) ?></div>
    <a href="?page=detalle&id=<?= $inc['id'] ?>" class="btn btn-sm btn-primary mt-2">Ver detalle</a>
  </div>
<?php endforeach; ?>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
