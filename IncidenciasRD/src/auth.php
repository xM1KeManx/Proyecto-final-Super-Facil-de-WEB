<?php
function start_session(){
  $cfg = require __DIR__ . '/../config/config.php';
  session_name($cfg['security']['session_name']);
  session_start();
}
function login_user(array $user){
  $_SESSION['user'] = [
    'id'=>$user['id'],
    'name'=>$user['name'],
    'email'=>$user['email'],
    'role'=>$user['role_name'] ?? $user['role'] ?? 'reportero'
  ];
}
function logout_user(){ $_SESSION = []; session_destroy(); }
