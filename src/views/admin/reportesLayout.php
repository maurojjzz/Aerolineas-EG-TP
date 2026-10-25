<?php 
date_default_timezone_set('America/Argentina/Buenos_Aires');
requireRol('admin');

$seccion = $_GET['seccion'] ?? 'listado';

$vistas = [
    'listado' => __DIR__ . '/reportesListado.php',
];
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Reportes — Administración — Volaré</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        
        <link rel="stylesheet" href="<?= url('public/css/admin.css') ?>?v=<?= time() ?>">
        <link rel="stylesheet" href="<?= url('public/css/layout/headerAdmin.css') ?>">
        <link rel="stylesheet" href="<?= url('public/css/components/tablaLista.css') ?>?v=<?= time() ?>">
        <link rel="stylesheet" href="<?= url('public/css/bootstrap-icons.css') ?>">
            <link rel="stylesheet" href="<?= url('public/css/accesibilidad.css') ?>">
        <style>
            .reporte-portada, .reporte-grafico { display: none; }
            .barra-fila { display: grid; grid-template-columns: 115px 1fr 80px; gap: 8px; align-items: center; margin: 7px 0; font-size: 10px; }
            .barra-base { height: 10px; background: #e8edf3; border-radius: 6px; overflow: hidden; }
            .barra-valor { height: 100%; background: #2878d7; border-radius: 6px; }
            @media print {
                @page { size: A4 landscape; margin: 14mm; }
                body { background: #fff !important; color: #172033 !important; }
                .admin-sidebar, .admin-header, .breadcrumbCont, #panelFiltrosReportes,
                #btnImprimirReporte, [data-bs-toggle="collapse"], .pagination,
                .reportes-navegacion, .reportes-titulo,
                .tablaContenedor > div:first-child { display: none !important; }
                .admin-main { width: 100% !important; margin: 0 !important; }
                .admin-content { padding: 0 !important; }
                .reporte-portada { display: flex; justify-content: space-between; align-items: flex-start;
                    border-bottom: 2px solid #123d72; margin-bottom: 12px; padding-bottom: 10px; }
                .reporte-portada h1 { color: #123d72; font-size: 22px; margin: 0; }
                .reporte-portada p { margin: 2px 0 0; font-size: 11px; color: #596579; }
                .reporte-meta { text-align: right; font-size: 10px; color: #596579; }
                .card, .tablaContenedor { box-shadow: none !important; border: 1px solid #dbe2ea !important; }
                .table-responsive { overflow: visible !important; }
                table { font-size: 10px; }
                th { background: #eaf1f8 !important; color: #123d72 !important; }
                tr { break-inside: avoid; }
                .reporte-grafico { display: block; border: 1px solid #dbe2ea; padding: 10px 14px; margin: 0 0 12px; break-inside: avoid; }
                .reporte-grafico h2 { color: #123d72; font-size: 13px; margin: 0 0 8px; }
            }
        </style>
    </head>
    <body>
        <?php require __DIR__ . '/../layouts/skipLink.php'; ?>
        <div class="d-flex min-vh-100 row g-0 m-0 p-2 pe-2">

            <aside class="admin-sidebar text-white d-none d-md-block col-md-3 col-xxl-2 pe-1">
                <?php require __DIR__ . '/../layouts/sidebarAdmin.php'; ?>
            </aside>

            <div class="admin-main col-12 col-md-9 col-xxl-10 bg-light rounded-3 overflow-hidden ">

                <header class="admin-header">
                    <?php require __DIR__ . '/../layouts/headerAdmin.php'; ?>
                </header>

                <main id="contenido" tabindex="-1" class="container-fluid d-flex flex-column gap-3 p-2 admin-content ">
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

        <?php require_once __DIR__ . '/../layouts/footer.php'; ?>

        <?php require __DIR__ . '/../components/menuMobileAdmin.php' ?>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
        <script src="<?= url('public/js/admin/sidebar.js') ?>"></script>
        <script src="<?= url('public/js/admin/reportesListado.js') ?>"></script>
    </body>
</html>