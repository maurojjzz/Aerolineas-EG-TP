<?php
    $env = parse_ini_file(__DIR__ . '/../../.env');

    $host = $env['DB_HOST'];
    $usuario = $env['DB_USER'];
    $password = $env['DB_PASSWORD'];
    $baseDatos = $env['DB_NAME'];

    $link = mysqli_connect($host, $usuario, $password, $baseDatos);

    if (!$link) {
        die("Error de conexión: " . mysqli_connect_error());
    }

    mysqli_set_charset($link, "utf8mb4");

?>