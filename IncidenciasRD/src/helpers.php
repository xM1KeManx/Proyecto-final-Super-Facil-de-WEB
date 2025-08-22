<?php
function now() { return (new DateTime('now', new DateTimeZone('America/Santo_Domingo')))->format('Y-m-d H:i:s'); }
function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function redirect($path){ header("Location: {$path}"); exit; }
function is_post(){ return $_SERVER['REQUEST_METHOD']==='POST'; }
function require_login(){ if(empty($_SESSION['user'])) redirect('login.php'); }
function role_in($roles){ return !empty($_SESSION['user']) && in_array($_SESSION['user']['role'], (array)$roles, true); }
function csrf_token(){
  if(empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(16));
  return $_SESSION['csrf'];
}
function csrf_require(){
  if(!is_post() || empty($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'])) {
    http_response_code(403); exit('CSRF invalid');
  }
}
