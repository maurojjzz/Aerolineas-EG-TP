<?php

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/Reserva.php';
require_once __DIR__ . '/PromocionController.php';

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
                
                // Guardar IDs de promos como JSON
                $idPromocionAplicada = !empty($promosIds) ? json_encode($promosIds) : null;
                $precioTotal = (float)$vuelo['precioVuelo'] * $cantPasajeros * (1 - $descuentoTotal / 100);

                $stmt = mysqli_prepare($this->conexion,
                    "INSERT INTO reserva (idUsuario, idVuelo, cantPasajeros, precioTotal, estadoReserva, idPromocion)
                     VALUES (?, ?, ?, ?, 'pendiente de pago', ?)");
                mysqli_stmt_bind_param($stmt, 'iiids', $idUsuario, $id, $cantPasajeros, $precioTotal, $idPromocionAplicada);
                mysqli_stmt_execute($stmt);
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

        $stmt = mysqli_prepare($this->conexion,
            "SELECT * FROM reserva WHERE idReserva = ? AND idUsuario = ? AND estadoReserva = 'pendiente de pago'");
        mysqli_stmt_bind_param($stmt, 'ii', $idReserva, $idUsuario);
        mysqli_stmt_execute($stmt);
        $reserva = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if (!$reserva) {
            flash_set('error', 'No se puede confirmar esta reserva.');
            redirect('index.php?pagina=mis-reservas');
        }

        $stmt = mysqli_prepare($this->conexion,
            "UPDATE reserva SET estadoReserva = 'confirmada' WHERE idReserva = ?");
        mysqli_stmt_bind_param($stmt, 'i', $idReserva);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        flash_set('success', '¡Pago confirmado! Tu reserva está asegurada.');
        redirect('index.php?pagina=mis-reservas');
    }

    // ── Cancelar reserva ──────────────────────────────────────────────────────
    public function cancelarReserva(): void {
        $idReserva = (int)($_POST['idReserva'] ?? 0);
        $idUsuario = (int)($_SESSION['usuario']['idUsuario'] ?? 0);

        // Obtener reserva con datos del vuelo
        $stmt = mysqli_prepare($this->conexion,
            "SELECT r.*, v.fechaHoraSalidaVuelo, r.cantPasajeros
             FROM reserva r
             JOIN vuelo v ON r.idVuelo = v.idVuelo
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
            // Decodificar promos aplicadas (JSON array)
            $promosData = [];
            if (!empty($row['idPromocion'])) {
                $ids = json_decode($row['idPromocion'], true);
                if (is_array($ids)) {
                    $placeholders = implode(',', array_fill(0, count($ids), '?'));
                    $stmtP = mysqli_prepare($this->conexion,
                        "SELECT idPromocion, nombre, descuentoPorcentaje FROM promocion WHERE idPromocion IN ($placeholders)");
                    $types = str_repeat('i', count($ids));
                    mysqli_stmt_bind_param($stmtP, $types, ...$ids);
                    mysqli_stmt_execute($stmtP);
                    $resP = mysqli_stmt_get_result($stmtP);
                    while ($p = mysqli_fetch_assoc($resP)) $promosData[] = $p;
                    mysqli_stmt_close($stmtP);
                }
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
        
        mysqli_begin_transaction($this->conexion);
        try {
            // Obtener las que van a cancelarse para devolver asientos
            $stmt = mysqli_prepare($this->conexion,
                "SELECT idVuelo, cantPasajeros FROM reserva
                WHERE estadoReserva = 'pendiente de pago'
                AND fechaReserva < NOW() - INTERVAL 3 DAY");
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $vencidas = [];
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
        } catch (Exception $e) {
            mysqli_rollback($this->conexion);
        }
    }

    public function obtenerReservaPorId(int $idReserva, int $idUsuario): ?array {
        $stmt = mysqli_prepare($this->conexion,
            "SELECT r.*, v.origenVuelo, v.codigoOrigen, v.destinoVuelo, v.codigoDestino,
                    v.fechaHoraSalidaVuelo, v.fechaHoraLlegadaVuelo, v.asientosTotales,
                    v.precioVuelo, a.nombreAerolinea, a.codigoIATA, a.logoUrl
            FROM reserva r
            JOIN vuelo v ON r.idVuelo = v.idVuelo
            JOIN aerolinea a ON v.idAerolinea = a.idAerolinea
            WHERE r.idReserva = ? AND r.idUsuario = ?");
        mysqli_stmt_bind_param($stmt, 'ii', $idReserva, $idUsuario);
        mysqli_stmt_execute($stmt);
        $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        
        if (!$fila) return null;
        
        // Decodificar promos aplicadas (JSON array)
        $promosData = [];
        if (!empty($fila['idPromocion'])) {
            $ids = json_decode($fila['idPromocion'], true);
            if (is_array($ids)) {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $stmtP = mysqli_prepare($this->conexion,
                    "SELECT idPromocion, nombre, descuentoPorcentaje FROM promocion WHERE idPromocion IN ($placeholders)");
                $types = str_repeat('i', count($ids));
                mysqli_stmt_bind_param($stmtP, $types, ...$ids);
                mysqli_stmt_execute($stmtP);
                $resP = mysqli_stmt_get_result($stmtP);
                while ($p = mysqli_fetch_assoc($resP)) $promosData[] = $p;
                mysqli_stmt_close($stmtP);
            }
        }
        $fila['promos_aplicadas'] = $promosData;
        
        return $fila;
    }
    
}