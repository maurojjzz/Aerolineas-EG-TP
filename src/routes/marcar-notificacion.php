<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/conexion.php';

// Validar que sea admin
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    http_response_code(403);
    exit;
}

$tipo = $_GET['tipo'] ?? '';
$id   = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    if ($tipo === 'ceo') {
        $stmt = mysqli_prepare($link, "UPDATE usuario SET notificacion_vista = 1 WHERE idUsuario = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } elseif ($tipo === 'promocion') {
        $stmt = mysqli_prepare($link, "UPDATE promocion SET notificacion_vista = 1 WHERE idPromocion = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

echo json_encode(['success' => true]);