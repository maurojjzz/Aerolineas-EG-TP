<?php
require_once __DIR__ . '/src/config/conexion.php';

// Cambiá estos dos por los del usuario real
$email = 'pablito@test.com';
$pwd   = 'loQueEscribisteEnElLogin';

$stmt = mysqli_prepare($link, "SELECT idUsuario, contrasena FROM usuario WHERE email = ?");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

if (!$row) {
    die("No existe el usuario con email: $email\n");
}

$hashBD = $row['contrasena'];

var_dump([
    'email'          => $email,
    'hash_BD'        => $hashBD,
    'largo_BD'       => strlen($hashBD),
    'pwd_probado'    => $pwd,
    'verify_BD'      => password_verify($pwd, $hashBD),
    'verify_BD_len'  => password_get_info($hashBD),
]);

// Generamos uno nuevo con la misma pwd para comparar
$nuevo = password_hash($pwd, PASSWORD_DEFAULT);
var_dump([
    'hash_fresco'    => $nuevo,
    'verify_fresco'  => password_verify($pwd, $nuevo),
]);