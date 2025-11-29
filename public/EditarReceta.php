<?php
session_start();

// SOLO ADMIN
if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != 1) {
    header("Location: Login.php");
    exit;
}

require_once "../includes/conexion.php";

if (!isset($_GET["id"])) {
    header("Location: VerRecetas.php");
    exit;
}

$idReceta = intval($_GET["id"]);
$mensaje = "";

// ==================
// CARGAR RECETA
// ==================
$stmt = $conn->prepare("SELECT Instrucciones FROM Receta WHERE idReceta = ?");
$stmt->bind_param("i", $idReceta);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("La receta no existe");
}

$receta = $result->fetch_assoc();

// ==================
// CARGAR INGREDIENTES ACTUALES
// ==================
$ingredientesReceta = $conn->query("
    SELECT dr.idIngrediente, dr.CantidadRequerida, i.Nombre, i.UnidadMedida
    FROM DetalleReceta dr
    INNER JOIN Ingrediente i ON dr.idIngrediente = i.idIngrediente
    WHERE dr.idReceta = $idReceta
");

// ==================
// INGREDIENTES DISPONIBLES
// ==================
$listaIngredientes = $conn->query("SELECT idIngrediente, Nombre, UnidadMedida FROM Ingrediente WHERE Estatus = 'Activo'");

// ==================
// PROCESAR EDICIÓN
// ==================
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $instrucciones = trim($_POST["instrucciones"]);

    $ingredientesJSON = [];

    foreach ($_POST["ingrediente"] as $i => $idIng) {
        $cantidad = floatval($_POST["cantidad"][$i]);

        if ($idIng > 0 && $cantidad > 0) {
            $ingredientesJSON[] = [
                "idIngrediente" => intval($idIng),
                "CantidadRequerida" => $cantidad
            ];
        }
    }

    $jsonFinal = json_encode($ingredientesJSON, JSON_UNESCAPED_UNICODE);

    $stmt = $conn->prepare("CALL EditarReceta(?, ?, ?)");
    $stmt->bind_param("iss", $idReceta, $instrucciones, $jsonFinal);

    if ($stmt->execute()) {
        $mensaje = "Receta actualizada correctamente.";
    } else {
        $mensaje = "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Receta</title>
    <link rel="stylesheet" href="EditarReceta.css">
</head>
<body>

<div class="registro-container">
    <form class="registro-box" method="POST">

        <h2>Editar Receta</h2>

        <?php if (!empty($mensaje)): ?>
            <div class="alerta"><?= $mensaje ?></div>
        <?php endif; ?>

        <!-- INSTRUCCIONES -->
        <div class="seccion">
            <h3>Instrucciones</h3>
            <textarea name="instrucciones" required><?= $receta["Instrucciones"] ?></textarea>
        </div>

        <!-- INGREDIENTES -->
        <div class="seccion">
            <h3>Ingredientes</h3>

            <div id="contenedor-ingredientes">
                <?php while ($ing = $ingredientesReceta->fetch_assoc()): ?>
                <div class="ingrediente-row">
                    <select name="ingrediente[]" required>
                        <option value="">Selecciona...</option>
                        <?php
                        $listaIngredientes->data_seek(0); 
                        while ($opt = $listaIngredientes->fetch_assoc()):
                        ?>
                            <option value="<?= $opt['idIngrediente'] ?>"
                                <?= ($opt['idIngrediente'] == $ing['idIngrediente']) ? 'selected' : '' ?>>
                                <?= $opt['Nombre'] ?> (<?= $opt['UnidadMedida'] ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>

                    <input type="number" step="0.01" name="cantidad[]" value="<?= $ing["CantidadRequerida"] ?>" required>

                    <button type="button" class="btn-eliminar" onclick="eliminarFila(this)">Eliminar</button>
                </div>
                <?php endwhile; ?>
            </div>

            <button type="button" class="btn-agregar" onclick="agregarIngrediente()">Agregar Ingrediente</button>
        </div>

        <button type="submit" class="btn-guardar">Guardar Cambios</button>

        <div class="links">
            <a href="VerRecetas.php">Volver</a>
        </div>

    </form>
</div>

<script>
function agregarIngrediente() {
    let contenedor = document.getElementById("contenedor-ingredientes");
    let filaOriginal = contenedor.children[0];
    let copia = filaOriginal.cloneNode(true);

    copia.querySelector("select").value = "";
    copia.querySelector("input").value = "";

    contenedor.appendChild(copia);
}

function eliminarFila(btn) {
    let contenedor = document.getElementById("contenedor-ingredientes");
    if (contenedor.children.length > 1) {
        btn.parentElement.remove();
    }
}
</script>

</body>
</html>
