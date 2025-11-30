<?php
session_start();

// Conexión
require_once "../includes/conexion.php";

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = trim($_POST["usuario"]);
    $contrasena = trim($_POST["contrasena"]);

    if ($usuario != "" && $contrasena != "") {

        $stmt = $conn->prepare("SELECT idPersona, Usuario, Contrasena, idRol, Estatus 
                                FROM Persona
                                WHERE Usuario = ?");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {

            $row = $result->fetch_assoc();

            if ($row["Estatus"] !== "Activo") {
                $mensaje = "Tu cuenta está inactiva.";
            } else {

                // Valida contraseña con hash o texto plano
                if (password_verify($contrasena, $row["Contrasena"]) || $contrasena === $row["Contrasena"]) {

                    // Guardar sesión
                    $_SESSION["idPersona"] = $row["idPersona"];
                    $_SESSION["usuario"] = $row["Usuario"];
                    $_SESSION["rol"] = $row["idRol"];

                    // Redirección por rol
                    if ($row["idRol"] == 1) { 
                        header("Location: DashboardAdministradores.php");
                        exit();
                    } 
                    else if ($row["idRol"] == 2) { 
                        header("Location: DashboardClientes.php");
                        exit();
                    } 
                    else {
                        // Si aparece otro rol desconocido
                        $mensaje = "Rol de usuario no autorizado.";
                    }

                } else {
                    $mensaje = "Contraseña incorrecta.";
                }
            }
        } else {
            $mensaje = "Usuario no encontrado.";
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
    <title>Login</title>
    <link rel="stylesheet" href="Login.css">
</head>
<body>

<div class="login-container">
    <form class="login-box" method="POST" action="">
        <h2>Iniciar Sesión</h2>

        <?php if ($mensaje != ""): ?>
            <div class="alerta"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <div class="input-group">
            <label>Usuario</label>
            <input type="text" name="usuario" required>
        </div>

        <div class="input-group">
            <label>Contraseña</label>
            <input type="password" name="contrasena" required>
        </div>

        <button type="submit" class="btn-login">Entrar</button>

        <div class="links">
            <a href="RegistroNuevoUsuario.php">Crear cuenta</a>
            <a href="RecuperaContraseña.php">¿Olvidaste tu contraseña?</a>
        </div>
    </form>
</div>

</body>
</html>
