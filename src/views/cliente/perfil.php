<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../controllers/UsuarioController.php';

// Asegurar que solo un cliente logueado acceda
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'cliente') {
    redirect('index.php?pagina=login');
}

$ctrl = new UsuarioController($link);
$idUsuario = (int)$_SESSION['usuario']['idUsuario'];
$usuario = $ctrl->obtenerUsuarioPorId($idUsuario);

if (!$usuario) {
    flash_set('error', 'No se encontró la información del usuario.');
    redirect('index.php?pagina=cliente');
}

$tiposDoc = ['DNI', 'Pasaporte', 'LC', 'LE'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil — Vuela sin límites</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= url('public/css/admin.css') ?>">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-7">
            
            <?php require __DIR__ . '/../components/alertToast.php'; ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold m-0">Mi Perfil</h2>
                    <p class="text-muted m-0">Actualizá tu información personal y de contacto.</p>
                </div>
                <a href="<?= url('index.php?pagina=cliente') ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Volver al Inicio
                </a>
            </div>

            <div class="card shadow border-0 rounded-3 p-4 bg-white">
                <form action="<?= url('src/routes/usuarios.php?accion=editarPerfilCliente') ?>" method="POST" class="d-flex flex-column gap-3">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <input type="hidden" name="id" value="<?= $idUsuario ?>">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold" style="font-size:0.9rem;">Nombre *</label>
                            <input type="text" name="nombre" class="form-control" required minlength="2" maxlength="50"
                                   value="<?= htmlspecialchars($usuario['nombre']) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold" style="font-size:0.9rem;">Apellido *</label>
                            <input type="text" name="apellido" class="form-control" required minlength="2" maxlength="50"
                                   value="<?= htmlspecialchars($usuario['apellido']) ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold" style="font-size:0.9rem;">Tipo de Documento *</label>
                            <select name="tipoDocumento" class="form-select" required>
                                <?php foreach ($tiposDoc as $td): ?>
                                    <option value="<?= $td ?>" <?= (strcasecmp(trim($usuario['tipoDocumento'] ?? ''), $td) === 0) ? 'selected' : '' ?>>
                                        <?= $td ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold" style="font-size:0.9rem;">Número de Documento *</label>
                            <input type="text" name="nroDocumento" class="form-control" required minlength="6" maxlength="15"
                                   value="<?= htmlspecialchars($usuario['nroDocumento']) ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:0.9rem;">Correo Electrónico *</label>
                        <input type="email" name="email" class="form-control" required
                               value="<?= htmlspecialchars($usuario['email']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:0.9rem;">Teléfono de Contacto</label>
                        <input type="text" name="telefono" class="form-control" placeholder="Ej. +54 341 1234567"
                               value="<?= htmlspecialchars($usuario['telefono'] ?? '') ?>">
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <a href="<?= url('index.php?pagina=cliente') ?>" class="btn btn-outline-danger">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>