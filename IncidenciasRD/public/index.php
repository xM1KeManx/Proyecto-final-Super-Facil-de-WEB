<?php
require __DIR__ . '/../src/db.php';
require __DIR__ . '/../src/helpers.php';
require __DIR__ . '/../src/auth.php';

start_session();

// router básico
$page = $_GET['page'] ?? 'mapa';
$file = __DIR__ . "/$page.php";

if (preg_match('/^[a-z0-9_-]+$/i', $page) && file_exists($file)) {
    require $file;
} else {
    http_response_code(404);
    echo "Página no encontrada";
}
