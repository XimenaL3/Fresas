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

<div class="contenedor">

    <h2>Lista de Usuarios Registrados</h2>

    <a href="DashboardAdministradores.php" class="btn-atras">← Volver atrás</a>

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
                        <?php if (!empty($fila['Imagen'])): ?>
                            <img src="Imagenes/<?php echo $fila['Imagen']; ?>" class="foto">
                        <?php else: ?>
                            <img src="Imagenes/user_default.png" class="foto">
                        <?php endif; ?>
                    </td>

                    <td><?php echo $fila['NombreCompleto']; ?></td>
                    <td><?php echo $fila['Telefono']; ?></td>
                    <td><?php echo $fila['Email']; ?></td>
                    <td><?php echo $fila['Usuario']; ?></td>
                    <td><?php echo $fila['Rol']; ?></td>

                    <td>
                        <?php if ($fila['Estatus'] == 1): ?>
                            <span class="activo">Activo</span>
                        <?php else: ?>
                            <span class="inactivo">Inactivo</span>
                        <?php endif; ?>
                    </td>

                    <td class="acciones">
                        <a href="EditarUsuario.php?id=<?php echo $fila['idPersona']; ?>" class="btn-editar">Editar</a>
                        <a href="EliminarUsuario.php?id=<?php echo $fila['idPersona']; ?>" class="btn-eliminar"
                           onclick="return confirm('¿Seguro que deseas eliminar este usuario?');">
                           Eliminar
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
