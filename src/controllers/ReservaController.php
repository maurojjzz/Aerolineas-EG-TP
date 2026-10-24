<?php

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/Reserva.php';
require_once __DIR__ . '/PromocionController.php';
require_once __DIR__ . '/../services/EmailService.php';

class ReservaController {

    private $conexion;
    private $promoCtrl;

    public function __construct($conexion) {
        $this->conexion = $conexion;
        $this->promoCtrl = new PromocionController($conexion);
    }

    // ── Crear reserva ─────────────────────────────────────────────────────────
    public function crearReserva(): void {
        $idVuelo       = (int)($_POST['idVuelo'] ?? 0);
        $idVueloVuelta = (int)($_POST['idVueloVuelta'] ?? 0);
        $cantPasajeros = (int)($_POST['cantPasajeros'] ?? 1);
        $idUsuario     = (int)($_SESSION['usuario']['idUsuario'] ?? 0);

        if (!$idVuelo || !$idUsuario || $cantPasajeros < 1) {
            flash_set('error', 'Datos inválidos.');
            redirect('index.php?pagina=cliente');
        }

        if ($idVueloVuelta > 0 && $idVueloVuelta === $idVuelo) {
            flash_set('error', 'Los vuelos de ida y vuelta deben ser distintos.');
            redirect('index.php?pagina=buscar-vuelos');
        }

        $ids = [$idVuelo];
        if ($idVueloVuelta > 0) {
            $ids[] = $idVueloVuelta;
        }

        $reservasCreadas = [];

        mysqli_begin_transaction($this->conexion);

        try {
            // Bloquear las filas de los vuelos para evitar sobreventa entre requests.
            $vuelos = [];
            foreach ($ids as $id) {
                $stmt = mysqli_prepare($this->conexion,
                    "SELECT * FROM vuelo
                     WHERE idVuelo = ? AND fechaHoraSalidaVuelo > NOW()
                     FOR UPDATE");
                mysqli_stmt_bind_param($stmt, 'i', $id);
                mysqli_stmt_execute($stmt);
                $vuelo = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
                mysqli_stmt_close($stmt);

                if (!$vuelo) {
                    throw new DomainException('El vuelo no está disponible.');
                }

                if ((int)$vuelo['asientosDisponibles'] < $cantPasajeros) {
                    throw new DomainException('No hay suficientes asientos disponibles para uno de los vuelos seleccionados.');
                }

                $vuelos[] = $vuelo;
            }

            if (count($vuelos) === 2) {
                $errorVuelta = $this->validarVueloVuelta($vuelos[0], $vuelos[1]);
                if ($errorVuelta !== null) {
                    throw new DomainException($errorVuelta);
                }
            }

            // Verificar que el usuario no tenga ya una reserva activa para ninguno de los vuelos.
            foreach ($vuelos as $vuelo) {
                $id = (int)$vuelo['idVuelo'];
                $stmt = mysqli_prepare($this->conexion,
                    "SELECT idReserva FROM reserva
                     WHERE idUsuario = ? AND idVuelo = ? AND estadoReserva != 'cancelada'
                     LIMIT 1");
                mysqli_stmt_bind_param($stmt, 'ii', $idUsuario, $id);
                mysqli_stmt_execute($stmt);
                $existente = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
                mysqli_stmt_close($stmt);

                if ($existente) {
                    throw new DomainException('Ya tenés una reserva activa para uno de los vuelos seleccionados.');
                }
            }

            // Insertar una reserva por cada tramo y descontar sus asientos.
            foreach ($vuelos as $vuelo) {
                $id = (int)$vuelo['idVuelo'];

                // Obtener TODAS las promos aplicables (stacking)
                $promos = $this->promoCtrl->obtenerPromosAplicables(
                    $id,
                    (int)$vuelo['idAerolinea'],
                    $vuelo['codigoOrigen'] ?? '',
                    $vuelo['codigoDestino'] ?? ''
                );

                $descuentoTotal = 0;
                $promosIds = [];
                foreach ($promos as $p) {
                    if ($p['acumulable']) {
                        $descuentoTotal += (float)$p['descuentoPorcentaje'];
                        $promosIds[] = (int)$p['idPromocion'];
                    } else {
                        $descuentoTotal = (float)$p['descuentoPorcentaje'];
                        $promosIds = [(int)$p['idPromocion']];
                        break;
                    }
                }
                $descuentoTotal = min($descuentoTotal, 100);
                
                // reserva.idPromocion es una FK INT: guarda la promoción de
                // mayor prioridad que respaldó el precio de la reserva.
                $idPromocionAplicada = !empty($promosIds) ? (string)$promosIds[0] : null;
                $precioTotal = (float)$vuelo['precioVuelo'] * $cantPasajeros * (1 - $descuentoTotal / 100);

                $stmt = mysqli_prepare($this->conexion,
                    "INSERT INTO reserva (idUsuario, idVuelo, cantPasajeros, precioTotal, estadoReserva, idPromocion)
                     VALUES (?, ?, ?, ?, 'pendiente de pago', ?)");
                mysqli_stmt_bind_param($stmt, 'iiids', $idUsuario, $id, $cantPasajeros, $precioTotal, $idPromocionAplicada);
                mysqli_stmt_execute($stmt);
                $reservasCreadas[] = [
                    'idReserva'     => (int)mysqli_insert_id($this->conexion),
                    'vuelo'         => $vuelo,
                    'cantPasajeros' => $cantPasajeros,
                    'precioTotal'   => $precioTotal,
                ];
                mysqli_stmt_close($stmt);

                $stmt = mysqli_prepare($this->conexion,
                    "UPDATE vuelo
                     SET asientosDisponibles = asientosDisponibles - ?
                     WHERE idVuelo = ? AND asientosDisponibles >= ?");
                mysqli_stmt_bind_param($stmt, 'iii', $cantPasajeros, $id, $cantPasajeros);
                mysqli_stmt_execute($stmt);
                $asientosActualizados = mysqli_affected_rows($this->conexion);
                mysqli_stmt_close($stmt);

                if ($asientosActualizados !== 1) {
                    throw new DomainException('No hay suficientes asientos disponibles para uno de los vuelos seleccionados.');
                }
            }

            mysqli_commit($this->conexion);

            $this->notificarSeguro(fn() => $this->enviarMailsReservasCreadas($idUsuario, $reservasCreadas));

            $mensaje = count($vuelos) > 1
                ? '¡Reservas creadas! Se generaron dos reservas independientes (ida y vuelta). Pagalas por separado desde Mis Reservas.'
                : '¡Reserva creada! Confirmá el pago para asegurar tu lugar.';

            flash_set('success', $mensaje);
            redirect('index.php?pagina=mis-reservas&tab=pendientes');

        } catch (DomainException $e) {
            mysqli_rollback($this->conexion);
            flash_set('error', $e->getMessage());
            redirect('index.php?pagina=buscar-vuelos');
        } catch (Throwable $e) {
            mysqli_rollback($this->conexion);
            flash_set('error', 'Error al crear la reserva. Intentá de nuevo.');
            redirect('index.php?pagina=buscar-vuelos');
        }
    }

    // ── Validar que un vuelo sea realmente la vuelta del otro ─────────────────
    private function mismoValor(?string $a, ?string $b): bool {
        $a = trim((string)$a);
        $b = trim((string)$b);
        return $a !== '' && $b !== '' && strcasecmp($a, $b) === 0;
    }

    public function validarVueloVuelta(array $vueloIda, array $vueloVuelta): ?string {
        $rutaOk = $this->mismoValor($vueloVuelta['codigoOrigen'] ?? null, $vueloIda['codigoDestino'] ?? null)
               && $this->mismoValor($vueloVuelta['codigoDestino'] ?? null, $vueloIda['codigoOrigen'] ?? null);

        // Fallback por si algún vuelo viejo no tiene códigos IATA cargados.
        if (!$rutaOk) {
            $rutaOk = $this->mismoValor($vueloVuelta['origenVuelo'] ?? null, $vueloIda['destinoVuelo'] ?? null)
                   && $this->mismoValor($vueloVuelta['destinoVuelo'] ?? null, $vueloIda['origenVuelo'] ?? null);
        }

        if (!$rutaOk) {
            return 'El vuelo de vuelta no corresponde a la ruta inversa del vuelo de ida.';
        }

        if (empty($vueloIda['fechaHoraLlegadaVuelo']) || empty($vueloVuelta['fechaHoraSalidaVuelo'])) {
            return 'No se pudieron validar los horarios de los vuelos seleccionados.';
        }

        if (strtotime($vueloVuelta['fechaHoraSalidaVuelo']) <= strtotime($vueloIda['fechaHoraLlegadaVuelo'])) {
            return 'El vuelo de vuelta debe salir después de la llegada del vuelo de ida.';
        }

        return null;
    }

    // ── Confirmar pago ────────────────────────────────────────────────────────
    public function confirmarPago(): void {
        $idReserva = (int)($_POST['idReserva'] ?? 0);
        $idUsuario = (int)($_SESSION['usuario']['idUsuario'] ?? 0);

        mysqli_begin_transaction($this->conexion);

        try {
            // 1. Obtener reserva con lock para verificar integridad
            $stmt = mysqli_prepare($this->conexion,
                "SELECT r.*, v.idVuelo, v.asientosDisponibles, v.asientosTotales
                 FROM reserva r
                 JOIN vuelo v ON r.idVuelo = v.idVuelo
                 WHERE r.idReserva = ? AND r.idUsuario = ?
                 FOR UPDATE");
            mysqli_stmt_bind_param($stmt, 'ii', $idReserva, $idUsuario);
            mysqli_stmt_execute($stmt);
            $reserva = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            mysqli_stmt_close($stmt);

            if (!$reserva) {
                throw new DomainException('No se puede confirmar esta reserva.');
            }

            if ($reserva['estadoReserva'] === 'cancelada') {
                throw new DomainException('Esta reserva fue cancelada y sus asientos fueron liberados. Creá una nueva reserva para volver a comprar.');
            }

            if ($reserva['estadoReserva'] !== 'pendiente de pago') {
                throw new DomainException('Esta reserva ya se encuentra confirmada.');
            }

            // 2. Verificación de integridad: los asientos ya se descontaron al crear la reserva.
            // Comparamos los asientos efectivamente ocupados del vuelo contra la suma de
            // reservas activas. Si hay más reservas activas que asientos descontados, la
            // reserva quedó desactualizada (por ejemplo, venció y los asientos se liberaron)
            // y confirmarla produciría una sobreventa.
            $stmt = mysqli_prepare($this->conexion,
                "SELECT COALESCE(SUM(cantPasajeros), 0) AS reservados
                 FROM reserva
                 WHERE idVuelo = ? AND estadoReserva IN ('pendiente de pago', 'confirmada')");
            mysqli_stmt_bind_param($stmt, 'i', $reserva['idVuelo']);
            mysqli_stmt_execute($stmt);
            $activas = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            mysqli_stmt_close($stmt);

            $asientosOcupados = (int)$reserva['asientosTotales'] - (int)$reserva['asientosDisponibles'];
            $asientosReservados = (int)($activas['reservados'] ?? 0);

            if ($asientosReservados > $asientosOcupados) {
                throw new DomainException('Los asientos de este vuelo ya no están disponibles. No pudimos confirmar el pago; la reserva sigue pendiente o fue cancelada por vencimiento.');
            }

            // 3. Confirmar pago: cambiar estado a 'confirmada'
            // Los asientos YA se descontaron al crear la reserva (crearReserva), 
            // así que solo cambiamos el estado.
            $stmt = mysqli_prepare($this->conexion,
                "UPDATE reserva SET estadoReserva = 'confirmada' WHERE idReserva = ?");
            mysqli_stmt_bind_param($stmt, 'i', $idReserva);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            mysqli_commit($this->conexion);

            $this->notificarSeguro(fn() => $this->notificarPagoConfirmado($idReserva, $idUsuario));

            flash_set('success', '¡Pago confirmado! Tu reserva está asegurada.');
            redirect('index.php?pagina=mis-reservas');

        } catch (DomainException $e) {
            mysqli_rollback($this->conexion);
            flash_set('error', $e->getMessage());
            redirect('index.php?pagina=mis-reservas');
        } catch (Throwable $e) {
            mysqli_rollback($this->conexion);
            flash_set('error', 'Error al confirmar el pago. Intentá de nuevo.');
            redirect('index.php?pagina=mis-reservas');
        }
    }

    // ── Cancelar reserva ──────────────────────────────────────────────────────
    public function cancelarReserva(): void {
        $idReserva = (int)($_POST['idReserva'] ?? 0);
        $idUsuario = (int)($_SESSION['usuario']['idUsuario'] ?? 0);

        // Obtener reserva con datos del vuelo
        $stmt = mysqli_prepare($this->conexion,
            "SELECT r.*,
                    v.origenVuelo, v.destinoVuelo,
                    v.fechaHoraSalidaVuelo, v.fechaHoraLlegadaVuelo,
                    a.nombreAerolinea
             FROM reserva r
             JOIN vuelo v ON r.idVuelo = v.idVuelo
             JOIN aerolinea a ON v.idAerolinea = a.idAerolinea
             WHERE r.idReserva = ? AND r.idUsuario = ? AND r.estadoReserva != 'cancelada'");
        mysqli_stmt_bind_param($stmt, 'ii', $idReserva, $idUsuario);
        mysqli_stmt_execute($stmt);
        $reserva = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if (!$reserva) {
            flash_set('error', 'No se puede cancelar esta reserva.');
            redirect('index.php?pagina=mis-reservas');
        }

        // Verificar 72 horas
        $horasRestantes = (strtotime($reserva['fechaHoraSalidaVuelo']) - time()) / 3600;
        if ($horasRestantes < 72) {
            flash_set('error', 'No podés cancelar con menos de 72 horas de anticipación.');
            redirect('index.php?pagina=mis-reservas');
        }

        mysqli_begin_transaction($this->conexion);

        try {
            $stmt = mysqli_prepare($this->conexion,
                "UPDATE reserva SET estadoReserva = 'cancelada' WHERE idReserva = ?");
            mysqli_stmt_bind_param($stmt, 'i', $idReserva);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            // Devolver asientos
            $cant = (int)$reserva['cantPasajeros'];
            $idVuelo = (int)$reserva['idVuelo'];
            $stmt = mysqli_prepare($this->conexion,
                "UPDATE vuelo SET asientosDisponibles = asientosDisponibles + ? WHERE idVuelo = ?");
            mysqli_stmt_bind_param($stmt, 'ii', $cant, $idVuelo);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            mysqli_commit($this->conexion);

            $this->notificarSeguro(fn() => $this->notificarReservaCancelada($reserva, $idUsuario));

            flash_set('success', 'Reserva cancelada correctamente.');

        } catch (Exception $e) {
            mysqli_rollback($this->conexion);
            flash_set('error', 'Error al cancelar la reserva.');
        }

        redirect('index.php?pagina=mis-reservas');
    }

    public function listarMisReservas(int $idUsuario): array {
        $stmt = mysqli_prepare($this->conexion,
            "SELECT r.*, v.origenVuelo, v.codigoOrigen, v.destinoVuelo, v.codigoDestino,
                    v.fechaHoraSalidaVuelo, v.fechaHoraLlegadaVuelo,
                    v.precioVuelo, a.idAerolinea, a.nombreAerolinea, a.codigoIATA, a.logoUrl
            FROM reserva r
            JOIN vuelo v ON r.idVuelo = v.idVuelo
            JOIN aerolinea a ON v.idAerolinea = a.idAerolinea
            WHERE r.idUsuario = ?
            ORDER BY r.fechaReserva DESC");
        mysqli_stmt_bind_param($stmt, 'i', $idUsuario);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $filas = [];
        while ($row = mysqli_fetch_assoc($result)) {
            // reserva.idPromocion es una FK INT.
            $promosData = [];
            $idPromocion = (int)($row['idPromocion'] ?? 0);
            if ($idPromocion > 0) {
                $stmtP = mysqli_prepare($this->conexion,
                    "SELECT idPromocion, nombre, descuentoPorcentaje
                     FROM promocion WHERE idPromocion = ?");
                mysqli_stmt_bind_param($stmtP, 'i', $idPromocion);
                mysqli_stmt_execute($stmtP);
                $resP = mysqli_stmt_get_result($stmtP);
                while ($p = mysqli_fetch_assoc($resP)) $promosData[] = $p;
                mysqli_stmt_close($stmtP);
            }
            $row['promos_aplicadas'] = $promosData;
            $filas[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $filas;
    }

    // Obtener reserva por id (para pantalla de reservar)
    public function obtenerVueloParaReservar(int $idVuelo): ?array {
        $stmt = mysqli_prepare($this->conexion,
            "SELECT v.*, a.nombreAerolinea, a.codigoIATA, a.logoUrl
            FROM vuelo v
            JOIN aerolinea a ON v.idAerolinea = a.idAerolinea
            WHERE v.idVuelo = ? AND v.asientosDisponibles > 0 AND v.fechaHoraSalidaVuelo > NOW()");
        mysqli_stmt_bind_param($stmt, 'i', $idVuelo);
        mysqli_stmt_execute($stmt);
        $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        return $fila ?: null;
    }



    // Validar y redirigir a pantalla de pago 
    
    public function irAPago(): void {
        $idReserva = (int)($_POST['idReserva'] ?? 0);
        $idUsuario = (int)($_SESSION['usuario']['idUsuario'] ?? 0);

        if (!$idReserva) {
            flash_set('error', 'Reserva inválida.');
            redirect('index.php?pagina=mis-reservas');
        }

        $stmt = mysqli_prepare($this->conexion,
            "SELECT idReserva FROM reserva
            WHERE idReserva = ? AND idUsuario = ? AND estadoReserva = 'pendiente de pago'");
        mysqli_stmt_bind_param($stmt, 'ii', $idReserva, $idUsuario);
        mysqli_stmt_execute($stmt);
        $existe = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if (!$existe) {
            flash_set('error', 'Reserva no encontrada o ya procesada.');
            redirect('index.php?pagina=mis-reservas');
        }

        redirect('index.php?pagina=pago&idReserva=' . $idReserva);
    }

    // Cancelar reservas pendientes con más de 3 días sin pagar y devolver los asientos en una sola transacción

    public function limpiarReservasVencidas(): void {
        $vencidas  = [];
        $commitOk  = false;

        mysqli_begin_transaction($this->conexion);
        try {
            // Bloqueamos las reservas vencidas para que dos requests simultáneos
            // no las cancelen (ni envíen los mails) dos veces.
            $stmt = mysqli_prepare($this->conexion,
                "SELECT r.idReserva, r.idUsuario, r.idVuelo, r.cantPasajeros, r.precioTotal, r.fechaReserva,
                        v.origenVuelo, v.destinoVuelo,
                        v.fechaHoraSalidaVuelo, v.fechaHoraLlegadaVuelo,
                        a.nombreAerolinea,
                        u.nombre, u.apellido, u.email
                 FROM reserva r
                 JOIN vuelo v ON r.idVuelo = v.idVuelo
                 JOIN aerolinea a ON v.idAerolinea = a.idAerolinea
                 JOIN usuario u ON r.idUsuario = u.idUsuario
                 WHERE r.estadoReserva = 'pendiente de pago'
                   AND r.fechaReserva < NOW() - INTERVAL 3 DAY
                 FOR UPDATE");
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($result)) $vencidas[] = $row;
            mysqli_stmt_close($stmt);

            if (!empty($vencidas)) {
                // Cancelar
                $stmt = mysqli_prepare($this->conexion,
                    "UPDATE reserva SET estadoReserva = 'cancelada'
                    WHERE estadoReserva = 'pendiente de pago'
                    AND fechaReserva < NOW() - INTERVAL 3 DAY");
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);

                // Devolver asientos por cada vuelo afectado
                foreach ($vencidas as $v) {
                    $stmt = mysqli_prepare($this->conexion,
                        "UPDATE vuelo SET asientosDisponibles = asientosDisponibles + ?
                        WHERE idVuelo = ?");
                    mysqli_stmt_bind_param($stmt, 'ii', $v['cantPasajeros'], $v['idVuelo']);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                }
            }

            mysqli_commit($this->conexion);
            $commitOk = true;
        } catch (Exception $e) {
            mysqli_rollback($this->conexion);
            error_log('Error al limpiar reservas vencidas: ' . $e->getMessage());
        }

        if ($commitOk && !empty($vencidas)) {
            $this->notificarSeguro(fn() => $this->enviarMailsReservasVencidas($vencidas));
        }
    }

    public function obtenerReservaPorId(int $idReserva, int $idUsuario): ?array {
        $stmt = mysqli_prepare($this->conexion,
            "SELECT r.*, v.origenVuelo, v.codigoOrigen, v.destinoVuelo, v.codigoDestino,
                    v.fechaHoraSalidaVuelo, v.fechaHoraLlegadaVuelo, v.asientosTotales,
                    v.precioVuelo, a.idAerolinea, a.nombreAerolinea, a.codigoIATA, a.logoUrl
            FROM reserva r
            JOIN vuelo v ON r.idVuelo = v.idVuelo
            JOIN aerolinea a ON v.idAerolinea = a.idAerolinea
            WHERE r.idReserva = ? AND r.idUsuario = ?");
        mysqli_stmt_bind_param($stmt, 'ii', $idReserva, $idUsuario);
        mysqli_stmt_execute($stmt);
        $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        
        if (!$fila) return null;
        
        // reserva.idPromocion es una FK INT.
        $promosData = [];
        $idPromocion = (int)($fila['idPromocion'] ?? 0);
        if ($idPromocion > 0) {
            $stmtP = mysqli_prepare($this->conexion,
                "SELECT idPromocion, nombre, descuentoPorcentaje
                 FROM promocion WHERE idPromocion = ?");
            mysqli_stmt_bind_param($stmtP, 'i', $idPromocion);
            mysqli_stmt_execute($stmtP);
            $resP = mysqli_stmt_get_result($stmtP);
            while ($p = mysqli_fetch_assoc($resP)) $promosData[] = $p;
            mysqli_stmt_close($stmtP);
        }
        $fila['promos_aplicadas'] = $promosData;
        
        return $fila;
    }

    // ── Notificaciones por mail ───────────────────────────────────────────────

    private function enviarMailsReservasCreadas(int $idUsuario, array $reservasCreadas): void {
        if (empty($reservasCreadas)) {
            return;
        }

        $usuario = $this->obtenerUsuarioParaMail($idUsuario);
        if (!$usuario || empty($usuario['email'])) {
            return;
        }

        $nombre      = $this->nombreCompleto($usuario);
        $vencimiento = date('Y-m-d H:i:s', strtotime('+3 days'));

        foreach ($reservasCreadas as $item) {
            $vuelo = $item['vuelo'] ?? [];
            $datos = [
                'idReserva'        => (int)($item['idReserva'] ?? 0),
                'origen'           => $vuelo['origenVuelo'] ?? '',
                'destino'          => $vuelo['destinoVuelo'] ?? '',
                'fechaSalida'      => $vuelo['fechaHoraSalidaVuelo'] ?? null,
                'fechaLlegada'     => $vuelo['fechaHoraLlegadaVuelo'] ?? null,
                'cantPasajeros'    => (int)($item['cantPasajeros'] ?? 1),
                'precioTotal'      => (float)($item['precioTotal'] ?? 0),
                'fechaVencimiento' => $vencimiento,
            ];

            try {
                EmailService::enviarReservaCreada($usuario['email'], $nombre, $datos);
            } catch (Throwable $e) {
                error_log('Error al notificar la reserva creada #' . $datos['idReserva'] . ': ' . $e->getMessage());
            }
        }
    }

    private function notificarPagoConfirmado(int $idReserva, int $idUsuario): void {
        $reserva = $this->obtenerReservaPorId($idReserva, $idUsuario);
        if (!$reserva) {
            return;
        }

        $usuario = $this->obtenerUsuarioParaMail($idUsuario);
        if (!$usuario || empty($usuario['email'])) {
            return;
        }

        $nombre = $this->nombreCompleto($usuario);
        $datos  = $this->datosReservaDesdeFila($reserva);

        try {
            EmailService::enviarPagoConfirmado($usuario['email'], $nombre, $datos);
        } catch (Throwable $e) {
            error_log('Error al notificar el pago de la reserva #' . $idReserva . ': ' . $e->getMessage());
        }

        $idAerolinea = (int)($reserva['idAerolinea'] ?? 0);

        foreach ($this->obtenerCEOsAerolinea($idAerolinea) as $ceo) {
            if (empty($ceo['email'])) {
                continue;
            }

            try {
                EmailService::enviarVentaCEO(
                    $ceo['email'],
                    $this->nombreCompleto($ceo),
                    $datos,
                    $nombre
                );
            } catch (Throwable $e) {
                error_log('Error al notificar la venta al CEO de la reserva #' . $idReserva . ': ' . $e->getMessage());
            }
        }
    }

    private function notificarReservaCancelada(array $reserva, int $idUsuario): void {
        $usuario = $this->obtenerUsuarioParaMail($idUsuario);
        if (!$usuario || empty($usuario['email'])) {
            return;
        }

        try {
            EmailService::enviarReservaCancelada(
                $usuario['email'],
                $this->nombreCompleto($usuario),
                $this->datosReservaDesdeFila($reserva)
            );
        } catch (Throwable $e) {
            error_log('Error al notificar la cancelación de la reserva #' . ($reserva['idReserva'] ?? 0) . ': ' . $e->getMessage());
        }
    }

    private function enviarMailsReservasVencidas(array $vencidas): void {
        $inicio = microtime(true);

        foreach ($vencidas as $reserva) {
            // Si hay muchas reservas vencidas, no bloqueamos la página
            // más de 15 segundos enviando correos.
            if ((microtime(true) - $inicio) > 15) {
                error_log('Se cortó el envío de mails de reservas vencidas por límite de tiempo. Total: ' . count($vencidas));
                break;
            }

            if (empty($reserva['email'])) {
                continue;
            }

            try {
                EmailService::enviarReservaVencida(
                    (string)$reserva['email'],
                    $this->nombreCompleto($reserva),
                    $this->datosReservaDesdeFila($reserva)
                );
            } catch (Throwable $e) {
                error_log('Error al notificar la reserva vencida #' . ($reserva['idReserva'] ?? 0) . ': ' . $e->getMessage());
            }
        }
    }

    private function datosReservaDesdeFila(array $reserva): array {
        return [
            'idReserva'     => (int)($reserva['idReserva'] ?? 0),
            'origen'        => $reserva['origenVuelo'] ?? '',
            'destino'       => $reserva['destinoVuelo'] ?? '',
            'fechaSalida'   => $reserva['fechaHoraSalidaVuelo'] ?? null,
            'fechaLlegada'  => $reserva['fechaHoraLlegadaVuelo'] ?? null,
            'cantPasajeros' => (int)($reserva['cantPasajeros'] ?? 1),
            'precioTotal'   => (float)($reserva['precioTotal'] ?? 0),
            'aerolinea'     => $reserva['nombreAerolinea'] ?? '',
        ];
    }

    private function obtenerUsuarioParaMail(int $idUsuario): ?array {
        if ($idUsuario <= 0) {
            return null;
        }

        $stmt = mysqli_prepare($this->conexion,
            "SELECT nombre, apellido, email FROM usuario WHERE idUsuario = ?");
        if (!$stmt) {
            return null;
        }

        mysqli_stmt_bind_param($stmt, 'i', $idUsuario);
        mysqli_stmt_execute($stmt);
        $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        return $fila ?: null;
    }

    private function obtenerCEOsAerolinea(int $idAerolinea): array {
        if ($idAerolinea <= 0) {
            return [];
        }

        $stmt = mysqli_prepare($this->conexion,
            "SELECT nombre, apellido, email
             FROM usuario
             WHERE rol = 'ceo' AND idAerolinea = ? AND activo = 1 AND email <> ''
             ORDER BY idUsuario");
        if (!$stmt) {
            return [];
        }

        mysqli_stmt_bind_param($stmt, 'i', $idAerolinea);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $ceos = [];
        while ($fila = mysqli_fetch_assoc($result)) {
            $ceos[] = $fila;
        }
        mysqli_stmt_close($stmt);

        return $ceos;
    }

    private function nombreCompleto(array $usuario): string {
        $nombre = trim(
            (string)($usuario['nombre'] ?? '') . ' ' . (string)($usuario['apellido'] ?? '')
        );

        return $nombre !== '' ? $nombre : 'pasajero';
    }

    // Ejecuta una notificación sin permitir que un error de correo
    // interrumpa el flujo de reserva, pago o cancelación.
    private function notificarSeguro(callable $callback): void {
        try {
            $callback();
        } catch (Throwable $e) {
            error_log('Error al enviar notificaciones por mail: ' . $e->getMessage());
        }
    }
    
}
