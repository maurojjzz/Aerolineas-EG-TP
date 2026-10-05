<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/conexion.php';

$token = trim($_GET['token'] ?? '');

if (!$token) {
    flash_set('error', 'Token inválido.');
    redirect('index.php?pagina=login');
}

// Buscar usuario con ese token
$stmt = mysqli_prepare($link, "SELECT idUsuario, rol, activo FROM usuario WHERE tokenVerificacion = ? AND emailVerificado = 0");
mysqli_stmt_bind_param($stmt, 's', $token);
mysqli_stmt_execute($stmt);
$usuario = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$usuario) {
    flash_set('error', 'El enlace de verificación es inválido o ya fue usado.');
    redirect('index.php?pagina=login');
}

// Marcar email como verificado
// Si es cliente también lo activamos; si es CEO solo verificamos el email (el admin lo activa después)
$activar = $usuario['rol'] === 'cliente' ? 1 : $usuario['activo'];

$stmt = mysqli_prepare($link, "UPDATE usuario SET emailVerificado = 1, activo = ?, tokenVerificacion = NULL WHERE idUsuario = ?");
mysqli_stmt_bind_param($stmt, 'ii', $activar, $usuario['idUsuario']);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if ($usuario['rol'] === 'cliente') {
    flash_set('success', '¡Email verificado! Ya podés iniciar sesión.');
} else {
    flash_set('success', '¡Email verificado! Tu cuenta está siendo revisada por un administrador.');
}

redirect('index.php?pagina=login');