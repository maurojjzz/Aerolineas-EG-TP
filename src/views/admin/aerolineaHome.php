<?php date_default_timezone_set('America/Argentina/Buenos_Aires');?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Administrador - Aerolínea</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

        <link rel="stylesheet" href="/public/css/admin.css">
        <link rel="stylesheet" href="/public/css/layout/headerAdmin.css">

    </head>
    <body>
        
        <div class="d-flex min-vh-100 row g-0 m-0 p-2 pe-2">

            <aside class="admin-sidebar text-white col-3 col-xxl-2 pe-1 ">
                <?php require '../layouts/sidebarAdmin.php'; ?>
            </aside>

            <div class="admin-main col-9 col-xxl-10 bg-light rounded-3 overflow-hidden ">

                <header class="admin-header">
                    <?php require '../layouts/headerAdmin.php'; ?>
                </header>

                <main class="container-fluid d-flex flex-column gap-3 p-3 admin-content ">
                    <?php require './aerolineaAlta.php'; ?>

                    
                </main>

            </div>

        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    </body>
</html>