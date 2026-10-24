<?php
$alertType    = $alertType    ?? null;
$alertTitle   = $alertTitle   ?? null;
$alertMessage = $alertMessage ?? null;

if ($alertType === null && $alertMessage === null) {
    $successMsg = flash_get('success');
    $errorMsg   = flash_get('error');

    if ($successMsg) {
        $alertType    = 'success';
        $alertTitle   = '¡Éxito!';
        $alertMessage = $successMsg;
    } elseif ($errorMsg) {
        $alertType    = 'danger';
        $alertTitle   = 'Error...';
        $alertMessage = $errorMsg;
    }
}

if (!$alertType || !$alertMessage) {
    return;
}

$titulosPorDefecto = [
    'success' => '¡Éxito!',
    'danger'  => 'Error...',
    'warning' => 'Atención',
    'info'    => 'Información',
];

$alertTitle = $alertTitle ?: ($titulosPorDefecto[$alertType] ?? '');
?>

<div class="position-absolute top-0 start-0 end-0 mt-3 px-3 d-flex flex-column align-items-center" style="z-index: 1085;">
    <div class="alert alert-<?= htmlspecialchars($alertType) ?> alert-dismissible fade show" role="alert" style="width: 100%; max-width: 600px;">
        <?php if ($alertTitle): ?>
            <p class="alert-heading h4"><?= htmlspecialchars($alertTitle) ?></p>
        <?php endif; ?>
        <p class="mb-0"><?= htmlspecialchars($alertMessage) ?></p>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
</div>

<script>
    // El mensaje se cierra solo, pero el tiempo se pausa mientras el usuario
    // lo lee con el mouse o el teclado.
    (function () {
        const iniciar = function () {
            const alertas = document.querySelectorAll('.alert-dismissible');
            if (!alertas.length || typeof bootstrap === 'undefined') return;

            alertas.forEach(function (alerta) {
                let restante = 12000;
                let inicio    = Date.now();
                let temporizador = null;

                const cerrar = function () {
                    bootstrap.Alert.getOrCreateInstance(alerta).close();
                };

                const programar = function () {
                    inicio = Date.now();
                    temporizador = window.setTimeout(cerrar, restante);
                };

                const pausar = function () {
                    window.clearTimeout(temporizador);
                    restante -= Date.now() - inicio;
                };

                programar();

                alerta.addEventListener('mouseenter', pausar);
                alerta.addEventListener('focusin', pausar);

                alerta.addEventListener('mouseleave', function () {
                    if (restante > 0) programar();
                });
                alerta.addEventListener('focusout', function (event) {
                    if (!alerta.contains(event.relatedTarget) && restante > 0) {
                        programar();
                    }
                });
            });
        };

        if (document.readyState === 'complete') {
            iniciar();
        } else {
            window.addEventListener('load', iniciar);
        }
    })();
</script>
