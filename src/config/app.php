<?php

$envPath = dirname(__DIR__, 2) . '/.env';

$env = parse_ini_file($envPath);

if($env === false) {
    throw new Exception("Error al leer el archivo .env");
}

define('BASE_URL', rtrim($env['APP_BASE_URL'] ?? '', '/'));

function url(string $path = ''):string{

    $path = ltrim($path, '/');

    if($path === '') {
        return BASE_URL ?: '/';
    }     

    return BASE_URL . '/' . $path;

}

?>