<?php
include("conexion.php");

if($_SERVER["REQUEST_METHOD"]=="POST"){
  $nombre = $_POST['nombre'];
  $email = $_POST['email'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

  $sql = "INSERT INTO usuarios (nombre,email,password) VALUES ('$nombre','$email','$password')";
  if($conn->query($sql)){
    echo "<script>alert('Registrado con éxito, ahora inicie sesión'); window.location='login.php';</script>";
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registro</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">
<h2>Registro de Usuario</h2>
<form method="post" class="w-50">
  <div class="mb-3">
    <label>Nombre</label>
    <input type="text" name="nombre" class="form-control" required>
  </div>
  <div class="mb-3">
    <label>Email</label>
    <input type="email" name="email" class="form-control" required>
  </div>
  <div class="mb-3">
    <label>Contraseña</label>
    <input type="password" name="password" class="form-control" required>
  </div>
  <button class="btn btn-success">Registrarse</button>
</form>
</body>
</html>
