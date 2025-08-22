<?php
// config/config.example.php
return [
  'db' => [
    'host' => '127.0.0.1',
    'name' => 'incidencias',
    'user' => 'root',
    'pass' => '',
    'charset' => 'utf8mb4',
  ],
  'security' => [
    'session_name' => 'incidencias_sess',
    'csrf_key' => 'cambia-esto',
  ],
  'oauth' => [
    'google' => [
      'client_id' => 'GOOGLE_CLIENT_ID',
      'client_secret' => 'GOOGLE_CLIENT_SECRET',
      'redirect_uri' => 'http://localhost/incidencias-app/public/callback-google.php'
    ],
    'microsoft' => [
      'client_id' => 'MS_CLIENT_ID',
      'client_secret' => 'MS_CLIENT_SECRET',
      'redirect_uri' => 'http://localhost/incidencias-app/public/callback-microsoft.php',
      'tenant' => 'common' // o tu tenant
    ]
  ],
  'uploads' => [
    'dir' => __DIR__ . '/../public/assets/uploads',
    'max_mb' => 5,
    'allowed' => ['image/jpeg','image/png','image/webp']
  ]
];
