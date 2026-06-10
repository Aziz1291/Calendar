<?php
// ============================================================
//  Database Configuration — EXAMPLE / TEMPLATE
//  Copy this file to config.php and fill in your real values.
// ============================================================
return [
    'host'     => 'YOUR_AIVEN_HOST',
    'port'     => 'YOUR_PORT',
    'dbname'   => 'defaultdb',
    'username' => 'avnadmin',
    'password' => 'YOUR_PASSWORD',
    'ssl_ca'   => __DIR__ . '/ca.pem',  // Path to Aiven CA certificate
];
