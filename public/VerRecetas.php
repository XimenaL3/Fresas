<?php
session_start();

// SOLO ADMIN
if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != 1) {
    header("Location: Login.php");
    exit;
}

require_once "../includes/conexion.php";

// =============================
//  OBTENER RECETAS AGRUPADAS
// =============================
$sql = "SELECT * FROM VistaRecetas ORDER BY idReceta";
$result = $conn->query($sql);

// Agrupar recetas
$recetas = [];

while ($row = $result->fetch_assoc()) {
    $id = $row["idReceta"];

    if (!isset($recetas[$id])) {
        $recetas[$id] = [
            "idReceta"       => $row["idReceta"],
            "Creador"        => $row["Creador"],
            "FechaCreacion"  => $row["FechaCreacion"],
            "Instrucciones"  => $row["Instrucciones"],
            "Ingredientes"   => []
        ];
    }

    $recetas[$id]["Ingredientes"][] = [
        "Ingrediente"       => $row["Ingrediente"],
        "CantidadRequerida" => $row["CantidadRequerida"],
        "UnidadMedida"      => $row["UnidadMedida"]
    ];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recetas</title>
    <link rel="stylesheet" href="VerRecetas.css">
</head>
<body>

<!-- TÍTULO PRINCIPAL -->
<h1 class="titulo">Recetas</h1>

<!-- BOTONES SUPERIORES -->
<div class="top-buttons">
    <a href="RegistrarReceta.php" class="btn-agregar">Agregar Nueva Receta</a>
    <a href="DashboardAdministradores.php" class="btn-regresar">Volver</a>
</div>

<!-- CONTENEDOR DE TARJETAS -->
<div class="contenedor">
    <?php foreach ($recetas as $receta): ?>
        <div class="card">
            <h2>Receta #<?= $receta["idReceta"] ?></h2>

            <p><strong>Creador:</strong> <?= $receta["Creador"] ?></p>
            <p><strong>Fecha:</strong> <?= $receta["FechaCreacion"] ?></p>

            <div class="acciones">
                <button class="btn-ver" onclick="abrirModal(<?= $receta['idReceta'] ?>)">Ver Detalles</button>
                <a href="EditarReceta.php?id=<?= $receta['idReceta'] ?>" class="btn-editar">Editar</a>
                <a href="EliminarReceta.php?id=<?= $receta['idReceta'] ?>" class="btn-eliminar"
                   onclick="return confirm('¿Eliminar esta receta?');">Eliminar</a>
            </div>
        </div>

        <!-- MODAL -->
        <div id="modal<?= $receta['idReceta'] ?>" class="modal">
            <div class="modal-content">
                <span class="cerrar" onclick="cerrarModal(<?= $receta['idReceta'] ?>)">&times;</span>

                <h2>Receta #<?= $receta["idReceta"] ?></h2>

                <h3>Creador</h3>
                <p><?= $receta["Creador"] ?></p>

                <h3>Fecha de creación</h3>
                <p><?= $receta["FechaCreacion"] ?></p>

                <h3>Instrucciones</h3>
                <p class="instrucciones"><?= nl2br($receta["Instrucciones"]) ?></p>

                <h3>Ingredientes</h3>
                <ul>
                    <?php foreach ($receta["Ingredientes"] as $ing): ?>
                        <li>
                            <?= $ing["Ingrediente"] ?> - <?= $ing["CantidadRequerida"] . " " . $ing["UnidadMedida"] ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
// Abrir modal
function abrirModal(id) {
    document.getElementById("modal" + id).style.display = "flex";
}

// Cerrar modal
function cerrarModal(id) {
    document.getElementById("modal" + id).style.display = "none";
}
</script>

</body>
</html>
