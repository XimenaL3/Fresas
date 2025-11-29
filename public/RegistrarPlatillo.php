<?php
session_start();
require_once "../includes/conexion.php";

$mensaje = "";

// ============================================
// CARGAR CATEGORÍAS
// ============================================
$categorias = [];
$resCat = $conn->query("SELECT idCategoria, Nombre FROM Categoria");

if ($resCat) {
    while ($row = $resCat->fetch_assoc()) {
        $categorias[] = $row;
    }
} else {
    die("Error al cargar categorías: " . $conn->error);
}

// ============================================
// CARGAR RECETAS (Receta NO tiene Nombre)
// ============================================
$recetas = [];
$resRec = $conn->query("SELECT idReceta, Instrucciones FROM Receta");

if ($resRec) {
    while ($row = $resRec->fetch_assoc()) {
        $recetas[] = $row;
    }
} else {
    die("Error al cargar recetas: " . $conn->error);
}

// ============================================
// PROCESAR FORMULARIO
// ============================================
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $Nombre = trim($_POST["nombre"]);
    $Descripcion = trim($_POST["descripcion"]);
    $PrecioVenta = floatval($_POST["precio"]);
    $idCategoria = intval($_POST["categoria"]);
    $idReceta = intval($_POST["receta"]);
    $Cantidad = floatval($_POST["cantidad"]);
    $CantidadDisponible = floatval($_POST["cantidad_disponible"]);

    // =======================
    // MANEJO DE IMAGEN
    // =======================
    $Imagen = "";

    if (!empty($_FILES["imagen"]["name"])) {

        $carpeta = __DIR__ . "/ImagenesPlatillos/";

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

    // Validación mínima
    if ($Nombre != "" && $PrecioVenta > 0 && $idCategoria > 0 && $idReceta > 0) {

        // Preparar llamada al procedimiento
        $stmt = $conn->prepare("CALL AgregarPlatillo(?,?,?,?,?,?,?,?)");

        $stmt->bind_param(
            "ssdsiidd",  // tipos
            $Nombre,
            $Descripcion,
            $PrecioVenta,
            $Imagen,
            $idCategoria,
            $idReceta,
            $Cantidad,
            $CantidadDisponible
        );

        if ($stmt->execute()) {
            $mensaje = "Platillo registrado exitosamente.";
        } else {
            $mensaje = "Error: " . $stmt->error;
        }

        // Muy importante en procedimientos
        $stmt->close();
        $conn->next_result();

    } else {
        $mensaje = "Completa todos los campos obligatorios.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Platillo</title>
    <link rel="stylesheet" href="RegistrarPlatillo.css">
</head>
<body>

<div class="registro-container">
    <form class="registro-box" method="POST" enctype="multipart/form-data">

        <h2>Registrar Nuevo Platillo</h2>

        <?php if (!empty($mensaje)): ?>
            <div class="alerta"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <!-- SECCIÓN 1 -->
        <div class="seccion">
            <h3>Información del Platillo</h3>

            <div class="fila">
                <div class="input-group">
                    <label>Nombre</label>
                    <input type="text" name="nombre" required>
                </div>

                <div class="input-group">
                    <label>Precio Venta</label>
                    <input type="number" step="0.01" name="precio" required>
                </div>
            </div>

            <div class="fila">
                <div class="input-group">
                    <label>Categoría</label>
                    <select name="categoria" required>
                        <option value="">Selecciona...</option>
                        <?php foreach($categorias as $c): ?>
                            <option value="<?php echo $c['idCategoria']; ?>">
                                <?php echo $c['Nombre']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="input-group">
                    <label>Receta</label>
                    <select name="receta" required>
                        <option value="">Selecciona...</option>
                        <?php foreach($recetas as $r): ?>
                            <option value="<?php echo $r['idReceta']; ?>">
                                Receta #<?php echo $r['idReceta']; ?> —
                                <?php echo substr($r['Instrucciones'], 0, 40) . "..."; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="input-group">
                <label>Descripción</label>
                <textarea name="descripcion" rows="3"></textarea>
            </div>
        </div>

        <!-- SECCIÓN 2 -->
        <div class="seccion">
            <h3>Existencias</h3>

            <div class="fila">
                <div class="input-group">
                    <label>Cantidad</label>
                    <input type="number" step="0.01" name="cantidad" required>
                </div>

                <div class="input-group">
                    <label>Cantidad Disponible</label>
                    <input type="number" step="0.01" name="cantidad_disponible" required>
                </div>
            </div>
        </div>

        <!-- SECCIÓN FOTO -->
        <div class="seccion">
            <h3>Imagen</h3>

            <div class="input-group">
                <label>Subir Imagen</label>
                <input type="file" name="imagen" accept="image/*">
            </div>
        </div>

        <button type="submit" class="btn-login">Registrar Platillo</button>

        <div class="links">
            <a href="DashboardAdministradores.php">Volver al Panel</a>
        </div>

    </form>
</div>

</body>
</html>
