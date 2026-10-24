<?php
    $envFile = __DIR__ . '/../../.env';
    $env = is_file($envFile) ? parse_ini_file($envFile) : [];

    if (!is_array($env)) {
        $env = [];
    }

    $host = getenv('DB_HOST') ?: ($env['DB_HOST'] ?? '');
    $usuario = getenv('DB_USER') ?: ($env['DB_USER'] ?? '');
    $password = getenv('DB_PASSWORD') ?: ($env['DB_PASSWORD'] ?? '');
    $baseDatos = getenv('DB_NAME') ?: ($env['DB_NAME'] ?? '');

    $link = mysqli_connect($host, $usuario, $password, $baseDatos);

    if (!$link) {
        // El detalle técnico queda en el log del servidor, nunca en la respuesta.
        error_log('Error de conexión a la base de datos: ' . mysqli_connect_error());
        http_response_code(500);
        die('No pudimos conectarnos con la base de datos. Intentá nuevamente en unos minutos.');
    }

    mysqli_set_charset($link, "utf8mb4");

?>