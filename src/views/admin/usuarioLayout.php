<?php 
date_default_timezone_set('America/Argentina/Buenos_Aires');
$seccion = $_GET['seccion'] ?? 'listado';

$vistas = [
    'listado' => __DIR__ . '/usuarioListado.php',
    'editar'  => __DIR__ . '/usuarioEditar.php',
    'detalle' => __DIR__ . '/usuarioDetalle.php',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrador - Usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= url('public/css/admin.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/layout/headerAdmin.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/components/tablaLista.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/bootstrap-icons.css') ?>">
</head>
<body>
    <div class="d-flex min-vh-100 row g-0 m-0 p-2 pe-2">
        <aside class="admin-sidebar text-white d-none d-md-block col-md-3 col-xxl-2 pe-1">
            <?php require __DIR__ . '/../layouts/sidebarAdmin.php'; ?>
        </aside>

        <div class="admin-main col-12 col-md-9 col-xxl-10 bg-light rounded-3 overflow-hidden">
            <header class="admin-header">
                <?php require __DIR__ . '/../layouts/headerAdmin.php'; ?>
            </header>

            <main class="container-fluid d-flex flex-column gap-3 p-2 admin-content">
                <?php 
                    if (!isset($vistas[$seccion])) {
                        http_response_code(404);
                        echo '<h1>Sección no encontrada</h1>';
                    } else {
                        require $vistas[$seccion];
                    }
                ?>
            </main>
        </div>
    </div>

    <?php require __DIR__ . '/../layouts/footer.php'; ?>
    <?php require __DIR__ . '/../components/menuMobileAdmin.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= url('public/js/admin/sidebar.js') ?>"></script>
</body>
</html>