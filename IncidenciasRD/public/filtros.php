<?php
include("conexion.php");

// Variables
$provincia = $_GET['provincia'] ?? '';
$tipo = $_GET['tipo'] ?? '';
$fecha1 = $_GET['fecha1'] ?? '';
$fecha2 = $_GET['fecha2'] ?? '';
$busqueda = $_GET['busqueda'] ?? '';

$query = "SELECT * FROM incidencias WHERE 1=1";

if($provincia) $query .= " AND provincia='$provincia'";
if($tipo) $query .= " AND tipo='$tipo'";
if($fecha1 && $fecha2) $query .= " AND fecha BETWEEN '$fecha1' AND '$fecha2'";
if($busqueda) $query .= " AND titulo LIKE '%$busqueda%'";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Filtros de Incidencias</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">

<h2>Filtrar Incidencias</h2>

<form method="get" class="row g-3">
  <div class="col-md-3">
    <label>Provincia</label>
    <input type="text" name="provincia" class="form-control" value="<?= $provincia ?>">
  </div>
  <div class="col-md-3">
    <label>Tipo</label>
    <input type="text" name="tipo" class="form-control" value="<?= $tipo ?>">
  </div>
  <div class="col-md-3">
    <label>Fecha inicio</label>
    <input type="date" name="fecha1" class="form-control" value="<?= $fecha1 ?>">
  </div>
  <div class="col-md-3">
    <label>Fecha fin</label>
    <input type="date" name="fecha2" class="form-control" value="<?= $fecha2 ?>">
  </div>
  <div class="col-md-4">
    <label>Búsqueda por título</label>
    <input type="text" name="busqueda" class="form-control" value="<?= $busqueda ?>">
  </div>
  <div class="col-md-2 align-self-end">
    <button type="submit" class="btn btn-primary w-100">Buscar</button>
  </div>
</form>

<hr>

<h3>Resultados</h3>
<table class="table table-bordered">
  <tr>
    <th>Título</th>
    <th>Provincia</th>
    <th>Tipo</th>
    <th>Fecha</th>
    <th>Ver</th>
  </tr>
  <?php while($row = $result->fetch_assoc()): ?>
  <tr>
    <td><?= $row['titulo'] ?></td>
    <td><?= $row['provincia'] ?></td>
    <td><?= $row['tipo'] ?></td>
    <td><?= $row['fecha'] ?></td>
    <td><a href="detalle.php?id=<?= $row['id'] ?>" class="btn btn-info btn-sm">Detalles</a></td>
  </tr>
  <?php endwhile; ?>
</table>

</body>
</html>
