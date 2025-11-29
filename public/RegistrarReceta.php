<?php
session_start();

// SOLO ADMIN
if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != 1) {
    header("Location: Login.php");
    exit;
}

require_once "../includes/conexion.php";

$mensaje = "";

// OBTENER INGREDIENTES PARA EL SELECT
$ingQuery = $conn->query("SELECT idIngrediente, Nombre, UnidadMedida FROM Ingrediente WHERE Estatus = 'Activo'");

// ==========================
//  PROCESAR ENVÍO DEL FORM
// ==========================
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $instrucciones = trim($_POST["instrucciones"]);
    $idPersona = $_SESSION["idPersona"];

    $ingredientesJSON = [];

    foreach ($_POST["ingrediente"] as $idx => $idIng) {
        $cantidad = floatval($_POST["cantidad"][$idx]);

        if ($idIng > 0 && $cantidad > 0) {
            $ingredientesJSON[] = [
                "idIngrediente" => intval($idIng),
                "CantidadRequerida" => $cantidad
            ];
        }
    }

    $jsonFinal = json_encode($ingredientesJSON, JSON_UNESCAPED_UNICODE);

    $stmt = $conn->prepare("CALL CrearReceta(?, ?, ?)");
    $stmt->bind_param("sis", $instrucciones, $idPersona, $jsonFinal);

    if ($stmt->execute()) {
        $mensaje = "Receta registrada exitosamente.";
    } else {
        $mensaje = "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Receta</title>
    <link rel="stylesheet" href="RegistrarReceta.css">
</head>
<body>

<div class="registro-container">
    <form class="registro-box" method="POST">

        <h2>Registrar Nueva Receta</h2>

        <?php if (!empty($mensaje)): ?>
            <div class="alerta"><?= $mensaje ?></div>
        <?php endif; ?>

        <!-- INSTRUCCIONES -->
        <div class="seccion">
            <h3>Instrucciones</h3>
            <textarea name="instrucciones" required></textarea>
        </div>

        <div class="seccion">
            <h3>Ingredientes</h3>

            <div id="contenedor-ingredientes">
                <div class="ingrediente-row">
                    <select name="ingrediente[]" required>
                        <option value="">Selecciona ingrediente...</option>
                        <?php while ($row = $ingQuery->fetch_assoc()): ?>
                            <option value="<?= $row['idIngrediente'] ?>">
                                <?= $row['Nombre'] ?> (<?= $row['UnidadMedida'] ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>

                    <input type="number" step="0.01" name="cantidad[]" placeholder="Cantidad" required>

                    <button type="button" class="btn-eliminar" onclick="eliminarFila(this)">Eliminar</button>
                </div>
            </div>

            <button type="button" class="btn-agregar" onclick="agregarIngrediente()">Agregar Ingrediente</button>
        </div>

        <button type="submit" class="btn-guardar">Guardar Receta</button>

        <div class="links">
            <a href="VerRecetas.php">Volver</a>
        </div>

    </form>
</div>

<script>
// CLONAR FILA DE INGREDIENTE
function agregarIngrediente() {
    let contenedor = document.getElementById("contenedor-ingredientes");
    let fila = contenedor.children[0];
    let copia = fila.cloneNode(true);

    copia.querySelector("select").value = "";
    copia.querySelector("input").value = "";

    contenedor.appendChild(copia);
}

// ELIMINAR FILA
function eliminarFila(btn) {
    let contenedor = document.getElementById("contenedor-ingredientes");
    if (contenedor.children.length > 1) {
        btn.parentElement.remove();
    }
}
</script>

</body>
</html>
