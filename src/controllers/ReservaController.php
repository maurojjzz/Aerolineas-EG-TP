<?php

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/Reserva.php';

class ReservaController {

    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // ── Crear reserva ─────────────────────────────────────────────────────────
    public function crearReserva(): void {
        $idVuelo      = (int)($_POST['idVuelo']      ?? 0);
        $cantPasajeros = (int)($_POST['cantPasajeros'] ?? 1);
        $idUsuario    = (int)($_SESSION['usuario']['idUsuario'] ?? 0);

        if (!$idVuelo || !$idUsuario || $cantPasajeros < 1) {
            flash_set('error', 'Datos inválidos.');
            redirect('index.php?pagina=cliente');
        }

        // Obtener vuelo y verificar disponibilidad
        $stmt = mysqli_prepare($this->conexion,
            "SELECT * FROM vuelo WHERE idVuelo = ? AND asientosDisponibles >= ? AND fechaHoraSalidaVuelo > NOW()");
        mysqli_stmt_bind_param($stmt, 'ii', $idVuelo, $cantPasajeros);
        mysqli_stmt_execute($stmt);
        $vuelo = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if (!$vuelo) {
            flash_set('error', 'El vuelo no está disponible o no tiene suficientes asientos.');
            redirect('index.php?pagina=buscar-vuelos');
        }

        $precioTotal = (float)$vuelo['precioVuelo'] * $cantPasajeros;

        // Verificar que el usuario no tenga ya una reserva activa para este vuelo
        $stmt = mysqli_prepare($this->conexion,
            "SELECT idReserva FROM reserva WHERE idUsuario = ? AND idVuelo = ? AND estadoReserva != 'cancelada'");
        mysqli_stmt_bind_param($stmt, 'ii', $idUsuario, $idVuelo);
        mysqli_stmt_execute($stmt);
        $existente = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if ($existente) {
            flash_set('error', 'Ya tenés una reserva activa para este vuelo.');
            redirect('index.php?pagina=mis-reservas');
        }

        // Iniciar transacción
        mysqli_begin_transaction($this->conexion);

        try {
            // Insertar reserva
            $stmt = mysqli_prepare($this->conexion,
                "INSERT INTO reserva (idUsuario, idVuelo, cantPasajeros, precioTotal, estadoReserva)
                 VALUES (?, ?, ?, ?, 'pendiente de pago')");
            mysqli_stmt_bind_param($stmt, 'iiid', $idUsuario, $idVuelo, $cantPasajeros, $precioTotal);
            mysqli_stmt_execute($stmt);
            $idReserva = mysqli_insert_id($this->conexion);
            mysqli_stmt_close($stmt);

            // Decrementar asientos
            $stmt = mysqli_prepare($this->conexion,
                "UPDATE vuelo SET asientosDisponibles = asientosDisponibles - ? WHERE idVuelo = ?");
            mysqli_stmt_bind_param($stmt, 'ii', $cantPasajeros, $idVuelo);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            mysqli_commit($this->conexion);

            flash_set('success', '¡Reserva creada! Confirmá el pago para asegurar tu lugar.');
            redirect('index.php?pagina=mis-reservas');

        } catch (Exception $e) {
            mysqli_rollback($this->conexion);
            flash_set('error', 'Error al crear la reserva. Intentá de nuevo.');
            redirect('index.php?pagina=buscar-vuelos');
        }
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

    // ── Listar reservas del usuario ───────────────────────────────────────────
    public function listarMisReservas(int $idUsuario): array {
        $stmt = mysqli_prepare($this->conexion,
            "SELECT r.*, v.origenVuelo, v.destinoVuelo, v.fechaHoraSalidaVuelo,
                    v.precioVuelo, a.nombreAerolinea, a.codigoIATA, a.logoUrl
             FROM reserva r
             JOIN vuelo v ON r.idVuelo = v.idVuelo
             JOIN aerolinea a ON v.idAerolinea = a.idAerolinea
             WHERE r.idUsuario = ?
             ORDER BY r.fechaReserva DESC");
        mysqli_stmt_bind_param($stmt, 'i', $idUsuario);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $filas = [];
        while ($row = mysqli_fetch_assoc($result)) $filas[] = $row;
        mysqli_stmt_close($stmt);
        return $filas;
    }

    // ── Obtener reserva por id (para pantalla de reservar) ────────────────────
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
}