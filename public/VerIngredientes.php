<?php
session_start();

// SOLO ADMIN
if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != 1) {
    header("Location: Login.php");
    exit;
}

// Conexión
require_once "../includes/conexion.php";

// ============================
//   CONSULTAR VISTA
// ============================
$sql = "SELECT * FROM VistaIngredientes";
$resultado = $conn->query($sql);

$mensaje = "";
if (!$resultado) {
    $mensaje = "Error al cargar ingredientes: " . $conn->error;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ingredientes</title>
    <link rel="stylesheet" href="VerIngredientes.css">
</head>
<body>

<h1 class="titulo">Ingredientes</h1>

<div class="top-buttons">
    <a href="RegistrarIngrediente.php" class="btn-regresar">Agregar Ingrediente</a>
    <a href="DashboardAdministradores.php" class="btn-regresar">Volver</a>
</div>

<?php if (!empty($mensaje)): ?>
    <div class="alerta"><?php echo $mensaje; ?></div>
<?php endif; ?>

<!-- ============================
        TABLA DE INGREDIENTES
============================= -->
<div class="tabla-contenedor">
<table class="tabla">
    <thead>
        <tr>
            <th>Imagen</th>
            <th>Nombre</th>
            <th>Unidad</th>
            <th>Cantidad</th>
            <th>Costo</th>
            <th>Estatus</th>
            <th>Acciones</th>
        </tr>
    </thead>

    <tbody>
        <?php while ($row = $resultado->fetch_assoc()): ?>
            <tr>
                <td>
                    <img src="Imagenes/<?php echo $row['Imagen']; ?>" 
                         onerror="this.src='Imagenes/default.png';">
                </td>

                <td><?php echo $row['Nombre']; ?></td>
                <td><?php echo $row['UnidadMedida']; ?></td>
                <td><?php echo $row['CantidadDisponible']; ?></td>
                <td>$<?php echo number_format($row['CostoUnitario'], 2); ?></td>

                <td>
                    <span class="status <?php echo ($row['Estatus'] == 'Activo' ? 'activo' : 'inactivo'); ?>">
                        <?php echo $row['Estatus']; ?>
                    </span>
                </td>

                <td class="acciones">
                    <a href="EditarIngrediente.php?id=<?php echo $row['idIngrediente']; ?>" class="btn-editar">✏️ Editar</a>

                    <a href="EliminarIngrediente.php?id=<?php echo $row['idIngrediente']; ?>" 
                       class="btn-eliminar"
                       onclick="return confirm('¿Seguro que deseas eliminar este ingrediente?');">
                       🗑️ Eliminar
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>
</div>

</body>
</html>
