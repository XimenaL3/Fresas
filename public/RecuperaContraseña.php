<?php
session_start();
require_once "../includes/conexion.php";

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = trim($_POST["usuario"]);
    $nueva = trim($_POST["nueva"]);

    if ($usuario != "" && $nueva != "") {

        // Buscar persona
        $stmt = $conn->prepare("SELECT idPersona FROM Persona WHERE Usuario = ? OR Email = ?");
        $stmt->bind_param("ss", $usuario, $usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $idPersona = $row["idPersona"];

            $ConNueva = password_hash($nueva, PASSWORD_DEFAULT);

            $stmt2 = $conn->prepare("CALL EditarContrasenaPersona(?,?)");
            $stmt2->bind_param("is", $idPersona, $ConNueva);

            if ($stmt2->execute()) {
                $mensaje = "Contraseña actualizada correctamente.";
            } else {
                $mensaje = "Error al actualizar.";
            }

        } else {
            $mensaje = "No se encontró el usuario o correo.";
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
    <title>Recuperar Contraseña</title>
    <link rel="stylesheet" href="RecuperaContraseña.css">
</head>
<body>

<div class="recuperar-container">
    <form class="recuperar-box" method="POST">
        <h2>Recuperar Contraseña</h2>

        <?php if ($mensaje != ""): ?>
            <div class="alerta"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <div class="input-group">
            <label>Usuario o Correo</label>
            <input type="text" name="usuario" required>
        </div>

        <div class="input-group">
            <label>Nueva Contraseña</label>
            <input type="password" name="nueva" required>
        </div>

        <button type="submit" class="btn-login">Actualizar</button>

        <div class="links">
            <a href="Login.php">Volver al Login</a>
        </div>

    </form>
</div>

</body>
</html>
