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
        // Llamar procedimiento
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
$sql = "SELECT idPlatillo, Platillo, PrecioVenta, Imagen 
        FROM VistaPlatillos 
        GROUP BY idPlatillo";
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

<!-- BOTONES SUPERIORES -->
<div class="top-bar">
    <a href="Carrito.php" class="btn-carrito">Ir al carrito 🛒</a>
    <a href="RegistrarPlatillo.php" class="btn-agregar">Agregar platillo ➕</a>
</div>

<!-- ALERTA -->
<?php if (!empty($mensaje)): ?>
    <div id="alerta" class="alerta-activa"><?php echo $mensaje; ?></div>
<?php endif; ?>

<!-- CONTENEDOR DE TARJETAS -->
<div class="platillos-container">

<?php while ($row = $result->fetch_assoc()): ?>
    <form class="card" method="POST">
        <img src="Imagenes/<?php echo $row['Imagen']; ?>" alt="Platillo">

        <h4><?php echo $row['Platillo']; ?></h4>
        <p>$<?php echo number_format($row['PrecioVenta'], 2); ?></p>

        <button type="submit" name="agregar" value="<?php echo $row['idPlatillo']; ?>" class="boton-add">
            Agregar al carrito
        </button>
    </form>
<?php endwhile; ?>

</div>

<script>
// Ocultar alerta automáticamente
setTimeout(() => {
    let a = document.getElementById("alerta");
    if (a) a.classList.remove("alerta-activa");
}, 2000);
</script>

</body>
</html>
