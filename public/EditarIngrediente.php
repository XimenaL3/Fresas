<?php
session_start();

// SOLO ADMIN
if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != 1) {
    header("Location: Login.php");
    exit;
}

require_once "../includes/conexion.php";

$mensaje = "";

// ===============================
//  VERIFICAR QUE VIENE EL ID
// ===============================
if (!isset($_GET["id"])) {
    header("Location: VerIngredientes.php");
    exit;
}

$idIngrediente = intval($_GET["id"]);

// ===============================
//   Cargar datos del ingrediente
// ===============================
$stmt = $conn->prepare("SELECT * FROM Ingrediente WHERE idIngrediente = ?");
$stmt->bind_param("i", $idIngrediente);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows == 0) {
    die("Ingrediente no encontrado.");
}

$ingrediente = $resultado->fetch_assoc();

// ===============================
//  Procesar actualización
// ===============================
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $Nombre = trim($_POST["nombre"]);
    $Unidad = trim($_POST["unidad"]);
    $Cantidad = floatval($_POST["cantidad"]);
    $Costo = floatval($_POST["costo"]);
    $Estatus = $_POST["estatus"];

    // === Imagen ===
    $ImagenActual = $ingrediente["Imagen"];
    $ImagenNueva = $ImagenActual;

    if (!empty($_FILES["imagen"]["name"])) {

        $carpeta = __DIR__ . "/Imagenes/";

        if (!is_dir($carpeta)) {
            mkdir($carpeta, 0777, true);
        }

        $nombreImagen = time() . "_" . basename($_FILES["imagen"]["name"]);
        $ruta = $carpeta . $nombreImagen;

        if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $ruta)) {
            $ImagenNueva = $nombreImagen;
        } else {
            $mensaje = "Error al subir la imagen.";
        }
    }

    // === Llamar SP ===
    $stmt2 = $conn->prepare("CALL EditarIngrediente(?,?,?,?,?,?,?)");
    $stmt2->bind_param(
        "issddss",
        $idIngrediente,
        $Nombre,
        $Unidad,
        $Cantidad,
        $Costo,
        $Estatus,
        $ImagenNueva
    );

    if ($stmt2->execute()) {
        $mensaje = "Ingrediente actualizado correctamente.";
        // Actualizar datos mostrados
        $ingrediente["Nombre"] = $Nombre;
        $ingrediente["UnidadMedida"] = $Unidad;
        $ingrediente["CantidadDisponible"] = $Cantidad;
        $ingrediente["CostoUnitario"] = $Costo;
        $ingrediente["Estatus"] = $Estatus;
        $ingrediente["Imagen"] = $ImagenNueva;
    } else {
        $mensaje = "Error: " . $stmt2->error;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Ingrediente</title>
    <link rel="stylesheet" href="EditarIngrediente.css">
</head>
<body>

<div class="registro-container">
    <form class="registro-box" method="POST" enctype="multipart/form-data">

        <h2>Editar Ingrediente</h2>

        <?php if (!empty($mensaje)): ?>
            <div class="alerta"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <!-- Sección 1: Datos -->
        <div class="seccion">
            <h3>Datos del Ingrediente</h3>

            <div class="fila">
                <div class="input-group">
                    <label>Nombre</label>
                    <input type="text" name="nombre" value="<?php echo $ingrediente['Nombre']; ?>" required>
                </div>

                <div class="input-group">
                    <label>Unidad de Medida</label>
                    <input type="text" name="unidad" value="<?php echo $ingrediente['UnidadMedida']; ?>" required>
                </div>
            </div>

            <div class="fila">
                <div class="input-group">
                    <label>Cantidad Disponible</label>
                    <input type="number" step="0.01" name="cantidad" value="<?php echo $ingrediente['CantidadDisponible']; ?>" required>
                </div>

                <div class="input-group">
                    <label>Costo Unitario</label>
                    <input type="number" step="0.01" name="costo" value="<?php echo $ingrediente['CostoUnitario']; ?>" required>
                </div>
            </div>

            <div class="input-group">
                <label>Estatus</label>
                <select name="estatus" required>
                    <option value="Activo" <?php if ($ingrediente["Estatus"] == "Activo") echo "selected"; ?>>Activo</option>
                    <option value="Inactivo" <?php if ($ingrediente["Estatus"] == "Inactivo") echo "selected"; ?>>Inactivo</option>
                </select>
            </div>
        </div>

        <!-- Sección imagen -->
        <div class="seccion">
            <h3>Imagen del Ingrediente</h3>

            <p>Imagen actual:</p>
            <img src="Imagenes/<?php echo $ingrediente['Imagen']; ?>" class="preview">

            <div class="input-group">
                <label>Cambiar Imagen</label>
                <input type="file" name="imagen" accept="image/*">
            </div>
        </div>

        <button type="submit" class="btn-guardar">Guardar Cambios</button>

        <div class="links">
            <a href="VerIngredientes.php">Volver</a>
        </div>

    </form>
</div>

</body>
</html>
