<?php
session_start();

// SOLO ADMIN
if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != 1) {
    header("Location: Login.php");
    exit;
}

// Conexión
require_once "../includes/conexion.php";

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $Nombre = trim($_POST["nombre"]);
    $Unidad = trim($_POST["unidad"]);
    $Cantidad = floatval($_POST["cantidad"]);
    $Costo = floatval($_POST["costo"]);

    // =======================
    //     SUBIR IMAGEN
    // =======================
    $Imagen = "";

    if (!empty($_FILES["imagen"]["name"])) {

        $carpeta = __DIR__ . "/Imagenes/";

        if (!is_dir($carpeta)) {
            mkdir($carpeta, 0777, true);
        }

        $nombreImagen = time() . "_" . basename($_FILES["imagen"]["name"]);
        $ruta = $carpeta . $nombreImagen;

        if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $ruta)) {
            $Imagen = $nombreImagen;
        } else {
            $mensaje = "Error al subir la imagen.";
        }
    }

    // =======================
    //   VALIDAR CAMPOS
    // =======================
    if ($Nombre !== "" && $Unidad !== "" && $Cantidad > 0 && $Costo > 0) {

        $stmt = $conn->prepare("CALL AgregarIngrediente(?,?,?,?,?)");
        $stmt->bind_param("ssdds", $Nombre, $Unidad, $Cantidad, $Costo, $Imagen);

        if ($stmt->execute()) {
            $mensaje = "Ingrediente registrado exitosamente.";
        } else {
            $mensaje = "Error: " . $stmt->error;
        }

    } else {
        $mensaje = "Completa todos los campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Ingrediente</title>
    <link rel="stylesheet" href="RegistrarIngrediente.css">
</head>
<body>

<div class="registro-container">
    <form class="registro-box" method="POST" enctype="multipart/form-data">

        <h2>Registrar Ingrediente</h2>

        <!-- ALERTA -->
        <?php if (!empty($mensaje)): ?>
            <div class="alerta"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <!-- SECCIÓN 1 -->
        <div class="seccion">
            <h3>Datos del Ingrediente</h3>

            <div class="fila">
                <div class="input-group">
                    <label>Nombre</label>
                    <input type="text" name="nombre" required>
                </div>

                <div class="input-group">
                    <label>Unidad de Medida</label>
                    <input type="text" name="unidad" placeholder="kg, litros, piezas..." required>
                </div>
            </div>

            <div class="fila">
                <div class="input-group">
                    <label>Cantidad Disponible</label>
                    <input type="number" step="0.01" name="cantidad" required>
                </div>

                <div class="input-group">
                    <label>Costo Unitario</label>
                    <input type="number" step="0.01" name="costo" required>
                </div>
            </div>
        </div>

        <!-- SECCIÓN 2 -->
        <div class="seccion">
            <h3>Imagen del Ingrediente</h3>

            <div class="input-group">
                <label>Subir Imagen</label>
                <input type="file" name="imagen" accept="image/*">
            </div>
        </div>

        <button type="submit" class="btn-guardar">Registrar Ingrediente</button>

        <div class="links">
            <a href="VerIngredientes.php">Volver</a>
        </div>

    </form>
</div>

</body>
</html>
