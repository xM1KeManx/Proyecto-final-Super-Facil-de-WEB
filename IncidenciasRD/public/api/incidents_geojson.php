<?php
require __DIR__ . '/../../src/db.php';
header('Content-Type: application/json; charset=utf-8');

$pdo = db();

$params = [];
$w = ["i.status='approved'", "i.occurred_at >= NOW() - INTERVAL 1 DAY"]; // últimas 24h por defecto

// Filtros opcionales
if (!empty($_GET['province_id'])) { $w[] = "i.province_id = ?"; $params[] = (int)$_GET['province_id']; }
if (!empty($_GET['type_id']))     { $w[] = "t.id = ?";         $params[] = (int)$_GET['type_id']; }
if (!empty($_GET['date_from']))   { $w[] = "i.occurred_at >= ?"; $params[] = $_GET['date_from']." 00:00:00"; }
if (!empty($_GET['date_to']))     { $w[] = "i.occurred_at <= ?"; $params[] = $_GET['date_to']." 23:59:59"; }
if (!empty($_GET['q']))           { $w[] = "i.title LIKE ?";     $params[] = "%".$_GET['q']."%"; }

// Si el usuario pide ver más de 24h, permite quitar esa condición pasando all_time=1
if (!empty($_GET['all_time'])) {
  $w = array_filter($w, fn($c) => $c !== "i.occurred_at >= NOW() - INTERVAL 1 DAY");
}

$sql = "
  SELECT i.id, i.title, i.description, i.latitude, i.longitude, i.occurred_at,
         GROUP_CONCAT(DISTINCT t.name) as types
  FROM incidents i
  LEFT JOIN incident_incident_type it ON it.incident_id=i.id
  LEFT JOIN incident_types t ON t.id=it.type_id
  " . (count($w) ? "WHERE ".implode(" AND ", $w) : "") . "
  GROUP BY i.id
  ORDER BY i.occurred_at DESC
  LIMIT 500
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$features = [];
foreach ($rows as $r) {
  if ($r['latitude'] === null || $r['longitude'] === null) continue;
  $features[] = [
    "type" => "Feature",
    "geometry" => [
      "type" => "Point",
      "coordinates" => [(float)$r['longitude'], (float)$r['latitude']]
    ],
    "properties" => [
      "id" => $r['id'],
      "title" => $r['title'],
      "description" => $r['description'],
      "type" => explode(",", (string)$r['types'])[0] ?: 'incidente',
      "occurred_at" => $r['occurred_at']
    ]
  ];
}

echo json_encode(["type"=>"FeatureCollection", "features"=>$features], JSON_UNESCAPED_UNICODE);
