<?php
session_start();
require_once "../includes/conexion.php";

// ================================
// VALIDAR SESIÓN DEL CLIENTE
// ================================
if (!isset($_SESSION["idPersona"])) {
    die("Error: No hay persona en sesión.");
}

$idPersona = $_SESSION["idPersona"];
$mensaje = "";

// ================================
// MANEJO DE ACCIONES
// ================================
if (isset($_GET["accion"])) {

    // SUMAR
    if ($_GET["accion"] === "sumar" && isset($_GET["idPlatillo"])) {
        $idPlatillo = intval($_GET["idPlatillo"]);
        $stmt = $conn->prepare("CALL SumarCantidadCarrito(?,?)");
        $stmt->bind_param("ii", $idPersona, $idPlatillo);
        $stmt->execute();
        $stmt->close();
        $conn->next_result();
        header("Location: Carrito.php");
        exit;
    }

    // RESTAR
    if ($_GET["accion"] === "restar" && isset($_GET["idPlatillo"])) {
        $idPlatillo = intval($_GET["idPlatillo"]);
        $stmt = $conn->prepare("CALL RestarCantidadCarrito(?,?)");
        $stmt->bind_param("ii", $idPersona, $idPlatillo);
        $stmt->execute();
        $stmt->close();
        $conn->next_result();
        header("Location: Carrito.php");
        exit;
    }
}

// ================================
// REALIZAR VENTA
// ================================
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["realizar_venta"])) {

    $tipoPago = $_POST["tipo_pago"];

    // Obtenemos el carrito
    $result = $conn->query("CALL ObtenerCarritoPorPersona($idPersona)");

    $detalleVenta = [];

    while ($row = $result->fetch_assoc()) {
        $detalleVenta[] = [
            "idPlatillo"     => intval($row["idPlatillo"]),
            "Cantidad"       => floatval($row["Cantidad"]),
            "PrecioUnitario" => floatval($row["PrecioUnitario"])
        ];
    }

    $jsonVenta = json_encode($detalleVenta, JSON_UNESCAPED_UNICODE);

    $conn->next_result();

    // Registrar venta
    $stmt = $conn->prepare("CALL RegistrarVenta(?,?,?)");
    $stmt->bind_param("iss", $idPersona, $tipoPago, $jsonVenta);

    if ($stmt->execute()) {
        $mensaje = "Venta realizada exitosamente.";
    } else {
        $mensaje = "Error al registrar venta: " . $stmt->error;
    }

    $stmt->close();
    $conn->next_result();
}

// ================================
// CARGAR CARRITO
// ================================
$carrito = [];
$total = 0;

$res = $conn->query("CALL ObtenerCarritoPorPersona($idPersona)");

if ($res) {
    while ($row = $res->fetch_assoc()) {
        $carrito[] = $row;
        $total += floatval($row["Total"]);
    }
}

$conn->next_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Carrito</title>
    <link rel="stylesheet" href="Carrito.css">
</head>
<body>

<div class="contenedor">

    <h2>🛒 Mi Carrito</h2>

    <?php if (!empty($mensaje)): ?>
        <div class="mensaje"><?php echo $mensaje; ?></div>
    <?php endif; ?>

    <?php if (count($carrito) === 0): ?>
        <p class="vacio">No tienes platillos en tu carrito.</p>
    <?php else: ?>

        <div class="carrito-lista">

            <?php foreach ($carrito as $item): ?>
                <div class="item">

                    <img src="ImagenesPlatillos/<?php echo $item['Imagen']; ?>" class="foto">

                    <div class="info">
                        <h3><?php echo $item["Producto"]; ?></h3>
                        <p>Precio unitario: $<?php echo number_format($item["PrecioUnitario"], 2); ?></p>
                        <p>Total: $<?php echo number_format($item["Total"], 2); ?></p>
                    </div>

                    <div class="acciones">
                        <a href="Carrito.php?accion=restar&idPlatillo=<?php echo $item['idPlatillo']; ?>" class="btn-restar">–</a>
                        <span class="cantidad"><?php echo $item["Cantidad"]; ?></span>
                        <a href="Carrito.php?accion=sumar&idPlatillo=<?php echo $item['idPlatillo']; ?>" class="btn-sumar">+</a>
                    </div>

                </div>
            <?php endforeach; ?>

        </div>

        <div class="total">
            <h3>Total a pagar: $<?php echo number_format($total, 2); ?></h3>
        </div>

        <!-- FORMULARIO PARA REALIZAR LA VENTA -->
        <form method="POST" class="venta-form">
            <label>Tipo de Pago:</label>
            <select name="tipo_pago" required>
                <option value="Efectivo">Efectivo</option>
                <option value="Tarjeta">Tarjeta</option>
                <option value="En línea">En línea</option>
            </select>

            <button type="submit" name="realizar_venta" class="btn-comprar">
                Realizar Venta
            </button>
        </form>

    <?php endif; ?>

</div>

</body>
</html>
