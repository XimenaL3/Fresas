<?php
session_start();
require_once "../includes/conexion.php";

// Obtener usuarios desde la vista
$sql = "SELECT * FROM VistaUsuarios";
$resultado = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Usuarios Registrados</title>
    <link rel="stylesheet" href="VerUsuarios.css">
</head>
<body>

<h2 class="titulo">Lista de Usuarios Registrados</h2>

<div class="top-buttons">
    <a href="DashboardAdministradores.php" class="btn-regresar">Volver</a>
</div>

<div class="tabla-contenedor">
    <?php if ($resultado->num_rows > 0): ?>
        <table class="tabla">
            <thead>
                <tr>
                    <th>Imagen</th>
                    <th>Nombre Completo</th>
                    <th>Teléfono</th>
                    <th>Email</th>
                    <th>Usuario</th>
                    <th>Rol</th>
                    <th>Estatus</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
                <?php while($fila = $resultado->fetch_assoc()): ?>
                <tr>
                    <td>
                        <img src="Imagenes/<?php echo !empty($fila['Imagen']) ? $fila['Imagen'] : 'user_default.png'; ?>" alt="Usuario">
                    </td>

                    <td><?php echo htmlspecialchars($fila['NombreCompleto']); ?></td>
                    <td><?php echo htmlspecialchars($fila['Telefono']); ?></td>
                    <td><?php echo htmlspecialchars($fila['Email']); ?></td>
                    <td><?php echo htmlspecialchars($fila['Usuario']); ?></td>
                    <td><?php echo htmlspecialchars($fila['Rol']); ?></td>

                    <td>
                        <span class="status <?php echo ($fila['Estatus'] == 1 ? 'activo' : 'inactivo'); ?>">
                            <?php echo ($fila['Estatus'] == 1 ? 'Activo' : 'Inactivo'); ?>
                        </span>
                    </td>

                    <td class="acciones">
                        <a href="EditarUsuario.php?id=<?php echo $fila['idPersona']; ?>" class="btn-editar" title="Editar">
                            ✏️
                        </a>
                        <a href="EliminarUsuario.php?id=<?php echo $fila['idPersona']; ?>" class="btn-eliminar"
                        onclick="return confirm('¿Seguro que deseas eliminar este usuario?');" title="Eliminar">
                            🗑️
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="vacio">No hay usuarios registrados.</p>
    <?php endif; ?>
</div>

</body>
</html>
