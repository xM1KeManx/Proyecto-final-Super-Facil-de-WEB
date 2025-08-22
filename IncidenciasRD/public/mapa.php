<?php
$title = "Mapa de Incidencias";
$pdo = db();
$types = $pdo->query("SELECT id, name FROM incident_types ORDER BY name")->fetchAll();
$provinces = $pdo->query("SELECT id, name FROM provinces ORDER BY name")->fetchAll();

// recoger filtros (para repoblar form)
$province_id = $_GET['province_id'] ?? '';
$type_id     = $_GET['type_id'] ?? '';
$date_from   = $_GET['date_from'] ?? '';
$date_to     = $_GET['date_to'] ?? '';
$q           = $_GET['q'] ?? '';
$all_time    = isset($_GET['all_time']) ? '1' : '';
ob_start();
?>
<h2 class="mb-3">Mapa de Incidencias</h2>

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
    <div class="form-check">
      <input class="form-check-input" type="checkbox" name="all_time" value="1" id="all_time" <?= $all_time?'checked':'' ?>>
      <label class="form-check-label" for="all_time">Todo el tiempo</label>
    </div>
    <button class="btn btn-primary">Aplicar</button>
    <a class="btn btn-outline-secondary" href="?page=mapa">Limpiar</a>
  </div>
</form>

<div id="map"></div>

<script>
const params = new URLSearchParams(<?= json_encode($_GET) ?>);
const url = 'api/incidents_geojson.php' + (params.toString() ? '?' + params.toString() : '');

var map = L.map('map').setView([18.5, -69.9], 7);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {attribution:'© OpenStreetMap'}).addTo(map);

fetch(url)
  .then(r => r.json())
  .then(data => {
    var markers = L.markerClusterGroup();
    var geoJson = L.geoJSON(data, {
      onEachFeature: function (feature, layer) {
        layer.bindPopup(`<strong>${feature.properties.title}</strong><br>${feature.properties.description ?? ''}<br><small>${feature.properties.occurred_at}</small><br><a href="?page=detalle&id=${feature.properties.id}">Ver más</a>`);
      },
      pointToLayer: function(feature, latlng){
        let icon = L.icon({ iconUrl: 'assets/img/icon-' + (feature.properties.type || 'incidente') + '.png', iconSize: [25,25] });
        return L.marker(latlng, {icon});
      }
    });
    markers.addLayer(geoJson);
    map.addLayer(markers);
  });
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
