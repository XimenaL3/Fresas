<?php
session_start();
require_once "../includes/conexion.php";

// -------------------------------
// PROCESAR AGREGAR AL CARRITO
// -------------------------------
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["agregar"])) {
    $idPersona = $_SESSION["idPersona"] ?? 0;
    $idPlatillo = intval($_POST["agregar"]);

    if ($idPersona == 0) {
        $mensaje = "Debes iniciar sesión.";
    } else {
        $stmt = $conn->prepare("CALL AgregarPlatilloCarrito(?, ?)");
        $stmt->bind_param("ii", $idPersona, $idPlatillo);

        if ($stmt->execute()) {
            $mensaje = "Platillo agregado al carrito ✔";
        } else {
            $mensaje = "Error al agregar: " . $stmt->error;
        }
        $stmt->close();
    }
}

// -------------------------------
// OBTENER PLATILLOS
// -------------------------------
$sql = "SELECT idPlatillo, Platillo, PrecioVenta, Imagen FROM VistaPlatillos GROUP BY idPlatillo";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Platillos</title>
    <link rel="stylesheet" href="VerPlatillos.css">
</head>
<body>

<h1 class="titulo">Platillos</h1>

<div class="top-buttons">
    <a href="Carrito.php" class="btn-regresar">Ir al carrito</a>
    <a href="RegistrarPlatillo.php" class="btn-agregar">Agregar platillo</a>
    <a href="DashboardAdministradores.php" class="btn-regresar">Volver</a>
</div>

<?php if (!empty($mensaje)): ?>
    <div id="alerta" class="alerta"><?php echo $mensaje; ?></div>
<?php endif; ?>

<div class="platillos-container">
    <?php while ($row = $result->fetch_assoc()): ?>
        <form class="card" method="POST" onclick="this.submit()">
            <input type="hidden" name="agregar" value="<?php echo $row['idPlatillo']; ?>">
            <div class="img-box">
                <img src="ImagenesPlatillos/<?php echo $row['Imagen']; ?>" alt="<?php echo htmlspecialchars($row['Platillo']); ?>">
            </div>
            <h3><?php echo htmlspecialchars($row['Platillo']); ?></h3>
            <p>$<?php echo number_format($row['PrecioVenta'], 2); ?></p>
        </form>
    <?php endwhile; ?>
</div>

<script>
setTimeout(() => {
    const alerta = document.getElementById("alerta");
    if (alerta) alerta.classList.remove("alerta");
}, 2000);
</script>

</body>
</html>
