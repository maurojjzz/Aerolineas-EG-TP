<?php
date_default_timezone_set('America/Argentina/Buenos_Aires');
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../controllers/ReservaController.php';

$idReserva = (int)($_GET['idReserva'] ?? 0);
$idUsuario = (int)($_SESSION['usuario']['idUsuario'] ?? 0);

if (!$idReserva) redirect('index.php?pagina=mis-reservas');

// Obtener reserva con datos del vuelo
$stmt = mysqli_prepare($link,
    "SELECT r.*, v.origenVuelo, v.codigoOrigen, v.destinoVuelo, v.codigoDestino,
            v.fechaHoraSalidaVuelo, v.fechaHoraLlegadaVuelo,
            a.nombreAerolinea, a.codigoIATA, a.logoUrl
     FROM reserva r
     JOIN vuelo v ON r.idVuelo = v.idVuelo
     JOIN aerolinea a ON v.idAerolinea = a.idAerolinea
     WHERE r.idReserva = ? AND r.idUsuario = ? AND r.estadoReserva = 'pendiente de pago'");
mysqli_stmt_bind_param($stmt, 'ii', $idReserva, $idUsuario);
mysqli_stmt_execute($stmt);
$reserva = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$reserva) {
    flash_set('error', 'Reserva no encontrada o ya procesada.');
    redirect('index.php?pagina=mis-reservas');
}

$u = usuarioActual();
$horaSalida  = date('H:i',   strtotime($reserva['fechaHoraSalidaVuelo']));
$fechaSalida = date('d/m/Y', strtotime($reserva['fechaHoraSalidaVuelo']));
$horaLlegada = date('H:i',   strtotime($reserva['fechaHoraLlegadaVuelo']));
$fechaLlegada = date('d/m/Y', strtotime($reserva['fechaHoraLlegadaVuelo']));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago de reserva</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= url('public/css/layout/header.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/components/menuMobile.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/layout/reservas.css') ?>">
    <style>
        .step-circle {
            width:32px;height:32px;border-radius:50%;
            display:flex;align-items:center;justify-content:center;
            font-weight:700;font-size:0.9rem;flex-shrink:0;
        }
        .step-line { flex:1;height:2px;background:#dee2e6;margin:0 8px; }
        .step-line.done { background:#0d6efd; }

        /* Tarjeta de crédito visual */
        .card-preview {
            background: linear-gradient(135deg, #0d3262 0%, #1a5fa8 60%, #2066ba 100%);
            border-radius: 16px;
            padding: 24px;
            color: white;
            position: relative;
            min-height: 180px;
            box-shadow: 0 8px 24px rgba(13,50,98,0.35);
            overflow: hidden;
        }
        .card-preview::before {
            content:'';
            position:absolute;top:-40px;right:-40px;
            width:160px;height:160px;border-radius:50%;
            background:rgba(255,255,255,0.07);
        }
        .card-preview::after {
            content:'';
            position:absolute;bottom:-50px;right:30px;
            width:130px;height:130px;border-radius:50%;
            background:rgba(255,255,255,0.05);
        }
        .card-chip {
            width:42px;height:30px;background:#f0c040;
            border-radius:6px;margin-bottom:20px;
        }
        .card-number-display {
            font-size:1.1rem;letter-spacing:4px;font-family:monospace;
            margin-bottom:16px;
        }
        .card-brand {
            position:absolute;top:20px;right:22px;
            font-size:1.4rem;font-weight:900;
            letter-spacing:-1px;
        }

        /* Inputs del form */
        .pago-input {
            border:1px solid #dee2e6;border-radius:10px;
            padding:10px 14px;font-size:0.95rem;width:100%;
            transition:border-color 0.2s;outline:none;
        }
        .pago-input:focus { border-color:#0d6efd;box-shadow:0 0 0 3px rgba(13,110,253,0.12); }
        .pago-label {
            font-size:0.75rem;font-weight:600;color:#6c757d;
            text-transform:uppercase;letter-spacing:0.5px;
            margin-bottom:4px;display:block;
        }
        .metodo-btn {
            border:2px solid #dee2e6;border-radius:10px;
            padding:10px 18px;cursor:pointer;
            transition:all 0.15s;background:white;
            display:flex;align-items:center;gap:8px;
            font-weight:500;font-size:0.9rem;
        }
        .metodo-btn.selected {
            border-color:#0d6efd;background:#f0f4ff;color:#0d3262;
        }
        .resumen-mini {
            background:#f8f9fa;border-radius:12px;padding:16px;font-size:0.85rem;
        }
        .linea-vuelo-pago {
            height:1px;background:#dee2e6;flex:1;position:relative;margin:0 10px;
        }
        .linea-vuelo-pago::after {
            content:"✈";position:absolute;
            top:-10px;left:50%;transform:translateX(-50%);
            font-size:0.9rem;color:#0d6efd;
        }
        .seguro-badge {
            display:inline-flex;align-items:center;gap:6px;
            background:#e8f5e9;color:#1b5e20;
            border-radius:20px;padding:4px 12px;
            font-size:0.78rem;font-weight:600;
        }
        /* Pantalla de éxito */
        #pantallaExito { display:none; }
        .success-circle {
            width:80px;height:80px;border-radius:50%;
            background:linear-gradient(135deg,#22c55e,#16a34a);
            display:flex;align-items:center;justify-content:center;
            font-size:2rem;margin:0 auto 16px;
            box-shadow:0 4px 20px rgba(34,197,94,0.4);
        }
        @keyframes checkIn {
            0%{transform:scale(0);opacity:0}
            70%{transform:scale(1.15)}
            100%{transform:scale(1);opacity:1}
        }
        .success-circle { animation: checkIn 0.5s ease forwards; }
    </style>
</head>
<body class="container-fluid overflow-x-hidden p-0 m-0">

<?php require './src/views/layouts/header.php'; ?>

<div style="min-height:100vh;">
<div class="container-xxl py-4 px-3 px-lg-5">

    <!-- Título -->
    <div class="text-center mb-4">
        <p class="text-white text-uppercase fw-semibold" style="letter-spacing:3px;font-size:0.85rem;">Pago seguro</p>
        <h1 class="fw-bold text-white" style="font-size:2rem;">COMPLETÁ TU PAGO</h1>
        <p class="text-white-50">Todos los datos son simulados. Ningún cobro real se efectuará.</p>
    </div>

    <!-- Stepper -->
    <div class="d-flex align-items-center justify-content-center mb-4 px-2">
        <div class="d-flex align-items-center gap-1" style="max-width:500px;width:100%;">
            <div class="d-flex flex-column align-items-center">
                <div class="step-circle bg-primary text-white">1</div>
                <small class="text-white mt-1" style="font-size:0.7rem;white-space:nowrap;">Selección</small>
            </div>
            <div class="step-line done"></div>
            <div class="d-flex flex-column align-items-center">
                <div class="step-circle bg-primary text-white">2</div>
                <small class="text-white mt-1" style="font-size:0.7rem;white-space:nowrap;">Tus datos</small>
            </div>
            <div class="step-line done"></div>
            <div class="d-flex flex-column align-items-center">
                <div class="step-circle bg-primary text-white">3</div>
                <small class="text-white mt-1" style="font-size:0.7rem;white-space:nowrap;">Pago</small>
            </div>
        </div>
    </div>

    <!-- Layout -->
    <div class="row g-4 justify-content-center" id="pantallaForm">

        <!-- Columna izquierda: formulario de pago -->
        <div class="col-12 col-lg-7">
            <div class="bg-white rounded-4 shadow p-4">

                <!-- Método de pago -->
                <h5 class="fw-bold mb-3">Método de pago</h5>
                <div class="d-flex gap-2 flex-wrap mb-4">
                    <button class="metodo-btn selected" onclick="seleccionarMetodo(this,'tarjeta')" type="button">
                        💳 Tarjeta de crédito
                    </button>
                    <button class="metodo-btn" onclick="seleccionarMetodo(this,'debito')" type="button">
                        🏦 Tarjeta de débito
                    </button>
                    <button class="metodo-btn" onclick="seleccionarMetodo(this,'transferencia')" type="button">
                        📲 Transferencia
                    </button>
                </div>

                <!-- Vista previa tarjeta -->
                <div class="card-preview mb-4" id="cardPreview">
                    <div class="card-brand" id="cardBrand">VISA</div>
                    <div class="card-chip"></div>
                    <div class="card-number-display" id="cardNumberDisplay">
                        •••• •••• •••• ••••
                    </div>
                    <div class="d-flex justify-content-between align-items-end">
                        <div>
                            <div style="font-size:0.65rem;opacity:0.7;text-transform:uppercase;letter-spacing:1px;">Titular</div>
                            <div class="fw-semibold" id="cardNameDisplay" style="font-size:0.9rem;letter-spacing:1px;">
                                <?= strtoupper(htmlspecialchars($u['nombre'] . ' ' . $u['apellido'])) ?>
                            </div>
                        </div>
                        <div class="text-end">
                            <div style="font-size:0.65rem;opacity:0.7;text-transform:uppercase;letter-spacing:1px;">Vence</div>
                            <div id="cardExpDisplay" style="font-size:0.9rem;font-family:monospace;">MM/AA</div>
                        </div>
                    </div>
                </div>

                <!-- Campos tarjeta -->
                <div id="seccionTarjeta">
                    <div class="mb-3">
                        <label class="pago-label">Número de tarjeta</label>
                        <input type="text" id="numeroTarjeta" class="pago-input"
                               placeholder="1234 5678 9012 3456" maxlength="19"
                               oninput="formatearTarjeta(this)">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="pago-label">Fecha de vencimiento</label>
                            <input type="text" id="vencimiento" class="pago-input"
                                   placeholder="MM/AA" maxlength="5"
                                   oninput="formatearVencimiento(this)">
                        </div>
                        <div class="col-6">
                            <label class="pago-label">CVV</label>
                            <input type="text" id="cvv" class="pago-input"
                                   placeholder="•••" maxlength="4"
                                   oninput="this.value=this.value.replace(/\D/g,'')">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="pago-label">Nombre en la tarjeta</label>
                        <input type="text" id="nombreTarjeta" class="pago-input"
                               value="<?= strtoupper(htmlspecialchars($u['nombre'] . ' ' . $u['apellido'])) ?>"
                               oninput="document.getElementById('cardNameDisplay').textContent=this.value.toUpperCase()||'TITULAR'">
                    </div>
                    <div class="mb-4">
                        <label class="pago-label">Cuotas</label>
                        <select class="pago-input" id="cuotas" style="cursor:pointer;">
                            <option value="1">1 cuota sin interés</option>
                            <option value="3">3 cuotas sin interés</option>
                            <option value="6">6 cuotas sin interés</option>
                            <option value="12">12 cuotas</option>
                        </select>
                    </div>
                </div>

                <!-- Sección transferencia (oculta por defecto) -->
                <div id="seccionTransferencia" style="display:none;">
                    <div class="resumen-mini mb-4">
                        <p class="fw-semibold mb-2">Datos para transferencia bancaria</p>
                        <p class="m-0"><strong>CBU:</strong> 0720000088000000123456</p>
                        <p class="m-0"><strong>Alias:</strong> VUELA.SIN.LIMITES</p>
                        <p class="m-0"><strong>Titular:</strong> Volare S.A.</p>
                        <p class="m-0"><strong>Banco:</strong> Banco Nación Argentina</p>
                        <hr class="my-2">
                        <p class="text-muted m-0" style="font-size:0.8rem;">
                            Una vez realizada la transferencia, presioná "Confirmar pago" y tu reserva pasará a estado confirmada.
                        </p>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
                    <span class="seguro-badge">🔒 Pago 100% seguro · Simulación</span>
                </div>

                <!-- Botón pagar -->
                <button class="btn btn-primary w-100 fw-bold py-3 mb-2"
                        style="font-size:1.05rem;border-radius:12px;"
                        onclick="procesarPago()"
                        id="btnPagar">
                    💳 Pagar $ <?= number_format($reserva['precioTotal'], 2, ',', '.') ?>
                </button>
                <a href="<?= url('index.php?pagina=mis-reservas') ?>"
                   class="btn btn-outline-secondary w-100" style="border-radius:12px;">
                    Volver a mis reservas
                </a>

            </div>
        </div>

        <!-- Columna derecha: resumen -->
        <div class="col-12 col-lg-5">
            <div class="bg-white rounded-4 shadow p-4" style="position:sticky;top:20px;">

                <h5 class="fw-bold mb-3">🧾 Resumen del pedido</h5>

                <!-- Aerolínea -->
                <div class="d-flex align-items-center gap-2 mb-3">
                    <?php if (!empty($reserva['logoUrl']) && $reserva['logoUrl'] !== '0'): ?>
                        <img src="<?= htmlspecialchars($reserva['logoUrl']) ?>"
                             style="width:36px;height:36px;object-fit:contain;" alt="">
                    <?php else: ?>
                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center"
                             style="width:36px;height:36px;">
                            <span class="text-white fw-bold" style="font-size:0.65rem;">
                                <?= substr($reserva['codigoIATA'],0,2) ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    <div>
                        <p class="fw-semibold m-0" style="font-size:0.85rem;"><?= htmlspecialchars($reserva['nombreAerolinea']) ?></p>
                        <p class="text-muted m-0" style="font-size:0.75rem;"><?= htmlspecialchars($reserva['codigoIATA']) ?></p>
                    </div>
                </div>

                <!-- Ruta -->
                <div class="d-flex align-items-center mb-3">
                    <div>
                        <p class="fw-bold fs-5 m-0"><?= $horaSalida ?></p>
                        <p class="fw-semibold m-0" style="font-size:0.82rem;">
                            <?= htmlspecialchars($reserva['origenVuelo']) ?>
                        </p>
                        <p class="text-muted m-0" style="font-size:0.75rem;"><?= $fechaSalida ?></p>
                    </div>
                    <div class="linea-vuelo-pago"></div>
                    <div class="text-end">
                        <p class="fw-bold fs-5 m-0"><?= $horaLlegada ?></p>
                        <p class="fw-semibold m-0" style="font-size:0.82rem;">
                            <?= htmlspecialchars($reserva['destinoVuelo']) ?>
                        </p>
                        <p class="text-muted m-0" style="font-size:0.75rem;"><?= $fechaLlegada ?></p>

                    </div>
                </div>

                <hr>

                <!-- Detalle precio -->
                <div class="d-flex flex-column gap-2 mb-3" style="font-size:0.85rem;">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Reserva #<?= str_pad($reserva['idReserva'],6,'0',STR_PAD_LEFT) ?></span>
                        <span class="text-muted">—</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Pasajeros</span>
                        <span><?= $reserva['cantPasajeros'] ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Tarifa base</span>
                        <span>$ <?= number_format($reserva['precioTotal'] / $reserva['cantPasajeros'], 2, ',', '.') ?> c/u</span>
                    </div>
                </div>

                <hr>

                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold fs-5">TOTAL</span>
                    <span class="fw-bold fs-5" style="color:#0d3262;">
                        $ <?= number_format($reserva['precioTotal'], 2, ',', '.') ?>
                    </span>
                </div>

                <div class="mt-3 p-2 rounded-2 text-center" style="background:#f0f4ff;font-size:0.78rem;color:#444;">
                    Al confirmar el pago tu reserva pasará a estado <strong>Confirmada</strong>
                    y recibirás un email de confirmación.
                </div>

            </div>
        </div>

    </div>

    <!-- Pantalla de éxito (oculta hasta que se pague) -->
    <div id="pantallaExito" class="row justify-content-center">
        <div class="col-12 col-md-6">
            <div class="bg-white rounded-4 shadow p-5 text-center">
                <div class="success-circle">✓</div>
                <h2 class="fw-bold mb-2" style="color:#0d3262;">¡Pago confirmado!</h2>
                <p class="text-muted mb-1">Tu reserva fue confirmada exitosamente.</p>
                <p class="text-muted mb-4" style="font-size:0.85rem;">
                    Reserva <strong>#<?= str_pad($reserva['idReserva'],6,'0',STR_PAD_LEFT) ?></strong> ·
                    <?= htmlspecialchars($reserva['origenVuelo']) ?> → <?= htmlspecialchars($reserva['destinoVuelo']) ?>
                </p>
                <div class="d-flex flex-column gap-2">
                    <a href="<?= url('index.php?pagina=mis-reservas&tab=confirmadas') ?>"
                       class="btn btn-primary py-2 fw-semibold">
                        Ver mis reservas confirmadas
                    </a>
                    <a href="<?= url('index.php?pagina=buscar-vuelos') ?>"
                       class="btn btn-outline-secondary py-2">
                        Buscar más vuelos
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>
</div>

<!-- Form oculto que hace el POST real -->
<form method="POST" action="<?= url('src/routes/reserva.php?accion=confirmar') ?>" id="formConfirmarPago">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
    <input type="hidden" name="idReserva"  value="<?= $reserva['idReserva'] ?>">
</form>

<?php require_once './src/views/layouts/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
let metodoActual = 'tarjeta';

function seleccionarMetodo(btn, metodo) {
    document.querySelectorAll('.metodo-btn').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
    metodoActual = metodo;

    const secTarjeta       = document.getElementById('seccionTarjeta');
    const secTransferencia = document.getElementById('seccionTransferencia');
    const cardPreview      = document.getElementById('cardPreview');

    if (metodo === 'transferencia') {
        secTarjeta.style.display       = 'none';
        secTransferencia.style.display = 'block';
        cardPreview.style.display      = 'none';
    } else {
        secTarjeta.style.display       = 'block';
        secTransferencia.style.display = 'none';
        cardPreview.style.display      = 'block';
        document.getElementById('cardBrand').textContent = metodo === 'debito' ? 'DÉBITO' : 'VISA';
    }
}

function formatearTarjeta(input) {
    let val = input.value.replace(/\D/g, '').substring(0, 16);
    val = val.replace(/(.{4})/g, '$1 ').trim();
    input.value = val;

    const display = document.getElementById('cardNumberDisplay');
    const partes  = val.split(' ');
    display.textContent = partes.map((p, i) =>
        i < partes.length - 1 ? '••••' : p.padEnd(4, '•')
    ).join(' ') || '•••• •••• •••• ••••';

    // Detectar marca
    const raw = val.replace(/\s/g,'');
    const brand = document.getElementById('cardBrand');
    if (/^4/.test(raw))       brand.textContent = 'VISA';
    else if (/^5[1-5]/.test(raw)) brand.textContent = 'MASTER';
    else if (/^3[47]/.test(raw))  brand.textContent = 'AMEX';
    else brand.textContent = metodoActual === 'debito' ? 'DÉBITO' : 'VISA';
}

function formatearVencimiento(input) {
    let val = input.value.replace(/\D/g,'').substring(0,4);
    if (val.length >= 2) val = val.substring(0,2) + '/' + val.substring(2);
    input.value = val;
    document.getElementById('cardExpDisplay').textContent = val || 'MM/AA';
}

function validarFormulario() {
    if (metodoActual === 'transferencia') return true;

    const num = document.getElementById('numeroTarjeta').value.replace(/\s/g,'');
    const exp = document.getElementById('vencimiento').value;
    const cvv = document.getElementById('cvv').value;

    if (num.length < 16) {
        alert('Ingresá un número de tarjeta válido (16 dígitos).');
        return false;
    }
    if (!/^\d{2}\/\d{2}$/.test(exp)) {
        alert('Ingresá una fecha de vencimiento válida (MM/AA).');
        return false;
    }
    if (cvv.length < 3) {
        alert('Ingresá el CVV de tu tarjeta.');
        return false;
    }
    return true;
}

function procesarPago() {
    if (!validarFormulario()) return;

    const btn = document.getElementById('btnPagar');
    btn.disabled = true;
    btn.innerHTML = `
        <span class="spinner-border spinner-border-sm me-2"></span>
        Procesando pago...
    `;

    // Simular delay de procesamiento (2 segundos)
    setTimeout(() => {
        // Hacer el POST real al backend
        document.getElementById('formConfirmarPago').submit();
    }, 2000);
}
</script>
</body>
</html>