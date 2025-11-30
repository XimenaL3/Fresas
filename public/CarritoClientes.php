<?php
session_start();
require_once "../includes/conexion.php";

// ================================
// VALIDAR SESIÓN DEL CLIENTE
// ================================
if (!isset($_SESSION["idPersona"])) {
    die("Error: No hay una sesión activa de cliente.");
}

$idPersona = $_SESSION["idPersona"];
$mensaje = "";

// ================================
// SUMAR / RESTAR CANTIDADES
// ================================
if (isset($_GET["accion"]) && isset($_GET["idPlatillo"])) {

    $idPlatillo = intval($_GET["idPlatillo"]);

    if ($_GET["accion"] === "sumar") {
        $stmt = $conn->prepare("CALL SumarCantidadCarrito(?,?)");
    } elseif ($_GET["accion"] === "restar") {
        $stmt = $conn->prepare("CALL RestarCantidadCarrito(?,?)");
    }

    if (isset($stmt)) {
        $stmt->bind_param("ii", $idPersona, $idPlatillo);
        $stmt->execute();
        $stmt->close();
        $conn->next_result();
    }

    header("Location: CarritoClientes.php");
    exit;
}

// ================================
// CREAR PEDIDO (NO VENTA)
// ================================
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["crear_pedido"])) {

    // FECHA ACTUAL AUTOMÁTICA
    $fechaEntrega = date("Y-m-d");

    // Obtener carrito del usuario
    $result = $conn->query("CALL ObtenerCarritoPorPersona($idPersona)");
    $detallePedido = [];

    while ($row = $result->fetch_assoc()) {
        $detallePedido[] = [
            "idPlatillo"     => intval($row["idPlatillo"]),
            "Cantidad"       => floatval($row["Cantidad"]),
            "PrecioUnitario" => floatval($row["PrecioUnitario"])
        ];
    }

    $conn->next_result();

    // Convertir a JSON
    $jsonPedido = json_encode($detallePedido, JSON_UNESCAPED_UNICODE);

    // Ejecutar el procedimiento CrearPedido
    $stmt = $conn->prepare("CALL CrearPedido(?,?,?)");
    $stmt->bind_param("iss", $idPersona, $fechaEntrega, $jsonPedido);

    if ($stmt->execute()) {
        $mensaje = "Pedido creado correctamente. 🎉";
    } else {
        $mensaje = "Error al crear pedido: " . $stmt->error;
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
    <title>Mi Pedido</title>
    <link rel="stylesheet" href="CarritoClientes.css">
</head>
<body>

<div class="contenedor">

    <h2>Mi Pedido</h2>

    <?php if (!empty($mensaje)): ?>
        <div class="mensaje"><?php echo $mensaje; ?></div>
    <?php endif; ?>

    <!-- BOTÓN ATRÁS -->
    <div style="text-align: left; margin-bottom: 15px;">
        <a href="DashboardClientes.php" class="btn-atras">Volver</a>
    </div>

    <?php if (count($carrito) === 0): ?>
        <p class="vacio">No tienes productos en tu pedido.</p>
    <?php else: ?>

        <div class="carrito-lista">

            <?php foreach ($carrito as $item): ?>
                <div class="item">

                    <img src="ImagenesPlatillos/<?php echo $item['Imagen']; ?>" class="foto">

                    <div class="info">
                        <h3><?php echo $item["Producto"]; ?></h3>
                        <p>Precio: $<?php echo number_format($item["PrecioUnitario"], 2); ?></p>
                        <p>Total: $<?php echo number_format($item["Total"], 2); ?></p>
                    </div>

                    <div class="acciones">
                        <a href="CarritoClientes.php?accion=restar&idPlatillo=<?php echo $item['idPlatillo']; ?>" class="btn-restar">–</a>
                        <span class="cantidad"><?php echo $item["Cantidad"]; ?></span>
                        <a href="CarritoClientes.php?accion=sumar&idPlatillo=<?php echo $item['idPlatillo']; ?>" class="btn-sumar">+</a>
                    </div>

                </div>
            <?php endforeach; ?>

        </div>

        <div class="total">
            <h3>Total del pedido: $<?php echo number_format($total, 2); ?></h3>
        </div>

        <!-- FORMULARIO SIN FECHA (FECHA AUTOMÁTICA) -->
        <form method="POST" class="venta-form">
            <button type="submit" name="crear_pedido" class="btn-comprar">
                Crear Pedido
            </button>
        </form>

    <?php endif; ?>

</div>

</body>
</html>
