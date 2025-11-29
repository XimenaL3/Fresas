<?php
session_start();
require_once "../includes/conexion.php";

// Fecha por defecto = día actual
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date("Y-m-d");

$mensaje = "";
$pedidos = [];

// Consulta a la vista VistaPedidos
$sql = "SELECT * FROM VistaPedidos WHERE DATE(Fecha) = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $fecha);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        $pedidos[] = $fila;
    }
} else {
    $mensaje = "No hay pedidos para esta fecha";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pedidos Registrados</title>
    <link rel="stylesheet" href="VerPedidos.css">
</head>
<body>

<h2 class="titulo">Consulta de Pedidos</h2>

<div class="top-buttons">
    <a href="DashboardAdministradores.php" class="btn-regresar">← Regresar</a>
</div>

<div class="tabla-contenedor">

    <!-- FORMULARIO FECHA -->
    <form method="GET" class="form-fecha">
        <label><strong>Seleccione una fecha:</strong></label>
        <input type="date" name="fecha" value="<?= $fecha ?>" required>
        <button type="submit" class="btn-buscar">Buscar</button>
    </form>

    <!-- MENSAJE SI NO HAY DATOS -->
    <?php if (!empty($mensaje)) : ?>
        <div class="alerta"><?= $mensaje ?></div>
    <?php endif; ?>

    <!-- TABLA -->
    <?php if (count($pedidos) > 0) : ?>
        <table class="tabla-pedidos">
            <thead>
                <tr>
                    <th>ID Pedido</th>
                    <th>Fecha</th>
                    <th>Fecha Entrega</th>
                    <th>Cliente</th>
                    <th>Platillo</th>
                    <th>Cantidad</th>
                    <th>Precio Unitario</th>
                    <th>Total</th>
                    <th>Estatus</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pedidos as $p) : ?>
                <tr>
                    <td><?= $p["idPedido"] ?></td>
                    <td><?= $p["Fecha"] ?></td>
                    <td><?= $p["FechaEntrega"] ?></td>
                    <td><?= $p["Cliente"] ?></td>
                    <td><?= $p["Platillo"] ?></td>
                    <td><?= $p["Cantidad"] ?></td>
                    <td>$<?= number_format($p["PrecioUnitario"], 2) ?></td>
                    <td>$<?= number_format($p["Total"], 2) ?></td>
                    <td><?= $p["Estatus"] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

</div>

</body>
</html>
