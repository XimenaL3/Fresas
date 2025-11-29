<?php
session_start();
require_once "../includes/conexion.php";

// Fecha por defecto — fecha actual
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date("Y-m-d");

$mensaje = "";
$ventas = [];

// Consulta a la vista VistaVentas
$sql = "SELECT * FROM VistaVentas WHERE DATE(Fecha) = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $fecha);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        $ventas[] = $fila;
    }
} else {
    $mensaje = "No hay ventas para esta fecha";
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ventas del Día</title>
    <link rel="stylesheet" href="Verventas.css">
</head>
<body>

<div class="registro-container">
<div class="registro-box">

    <h2>Consulta de Ventas</h2>

    <!-- Botón atrás -->
    <div class="links">
        <a href="DashboardAdministradores.php" class="btn-atras">← Regresar</a>
    </div>

    <!-- FORMULARIO DE FECHA -->
    <form method="GET" class="form-fecha" style="margin-top: 15px;">
        <label><strong>Seleccione una fecha:</strong></label>
        <input type="date" name="fecha" value="<?= $fecha ?>" required>
        <button type="submit" class="btn-guardar">Buscar</button>
    </form>

    <!-- MENSAJE SI NO HAY VENTAS -->
    <?php if (!empty($mensaje)) : ?>
        <div class="alerta"><?= $mensaje ?></div>
    <?php endif; ?>

    <!-- TABLA DE RESULTADOS -->
    <?php if (count($ventas) > 0) : ?>
        <table class="tabla-ventas">
            <thead>
                <tr>
                    <th>ID Venta</th>
                    <th>Fecha</th>
                    <th>Cajero</th>
                    <th>Platillo</th>
                    <th>Cantidad</th>
                    <th>Precio Unitario</th>
                    <th>Total</th>
                    <th>Tipo Pago</th>
                    <th>Estatus</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ventas as $v) : ?>
                <tr>
                    <td><?= $v["idVenta"] ?></td>
                    <td><?= $v["Fecha"] ?></td>
                    <td><?= $v["Cajero"] ?></td>
                    <td><?= $v["Platillo"] ?></td>
                    <td><?= $v["Cantidad"] ?></td>
                    <td>$<?= number_format($v["PrecioUnitario"], 2) ?></td>
                    <td>$<?= number_format($v["Total"], 2) ?></td>
                    <td><?= $v["TipoPago"] ?></td>
                    <td><?= $v["Estatus"] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

</div>
</div>

</body>
</html>
