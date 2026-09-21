<?php
    $env = parse_ini_file(__DIR__ . '/../../.env');

    $host = getenv('DB_HOST') ?: $env['DB_HOST'];
    $usuario = getenv('DB_USER') ?: $env['DB_USER'];
    $password = getenv('DB_PASSWORD') ?: $env['DB_PASSWORD'];
    $baseDatos = getenv('DB_NAME') ?: $env['DB_NAME'];

    $link = mysqli_connect($host, $usuario, $password, $baseDatos);

    if (!$link) {
        die("Error de conexión: " . mysqli_connect_error());
    }

    mysqli_set_charset($link, "utf8mb4");

?>