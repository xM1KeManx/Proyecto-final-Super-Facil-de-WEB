<?php
require __DIR__ . '/../src/auth.php';
start_session();
logout_user();
header('Location: login.php'); exit;
