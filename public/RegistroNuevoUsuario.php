<?php
session_start();

// Conexión
require_once "../includes/conexion.php";

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $Nombre = trim($_POST["nombre"]);
    $ApellidoP = trim($_POST["apellido_p"]);
    $ApellidoM = trim($_POST["apellido_m"]);
    $Telefono = trim($_POST["telefono"]);
    $Email = trim($_POST["email"]);
    $Edad = intval($_POST["edad"]);
    $Sexo = $_POST["sexo"];
    $Usuario = trim($_POST["usuario"]);
    $Contrasena = password_hash($_POST["contrasena"], PASSWORD_DEFAULT);

    // Rol por defecto = Cliente (3)
    $Rol = 2;

    // =======================
    //      MANEJO IMAGEN
    // =======================
    $Imagen = "";

    if (!empty($_FILES["imagen"]["name"])) {

        // Carpeta absoluta donde se guardará la imagen
        $carpeta = __DIR__ . "/Imagenes/";

        // Crear carpeta si no existe
        if (!is_dir($carpeta)) {
            mkdir($carpeta, 0777, true);
        }

        $nombreImagen = basename($_FILES["imagen"]["name"]);
        $ruta = $carpeta . $nombreImagen;

        if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $ruta)) {
            $Imagen = $nombreImagen;
        } else {
            $mensaje = "Error al subir la imagen.";
        }
    }

    // =======================
    //   VALIDACIÓN BÁSICA
    // =======================
    if ($Nombre != "" && $ApellidoP != "" && $Usuario != "" && $Email != "") {

        $stmt = $conn->prepare("CALL RegistrarPersona(?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param(
            "sssssissssi",
            $Nombre,
            $ApellidoP,
            $ApellidoM,
            $Telefono,
            $Email,
            $Edad,
            $Sexo,
            $Usuario,
            $Contrasena,
            $Imagen,
            $Rol
        );

        if ($stmt->execute()) {
            $mensaje = "Registro exitoso. Ahora puedes iniciar sesión.";
        } else {
            // Mostrar error real del SP
            $mensaje = "Error al registrar: " . $stmt->error;
        }
    } else {
        $mensaje = "Completa todos los campos obligatorios.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro</title>
    <link rel="stylesheet" href="RegistroNuevoUsuario.css">
</head>
<body>

<div class="registro-container">
    <form class="registro-box" method="POST" enctype="multipart/form-data">

        <h2>Registro de Usuario</h2>

        <!-- ALERTA -->
        <?php if (!empty($mensaje)): ?>
            <div class="alerta"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <!-- SECCIÓN 1: DATOS PERSONALES -->
        <div class="seccion">
            <h3>Datos Personales</h3>

            <div class="fila">
                <div class="input-group">
                    <label>Nombre</label>
                    <input type="text" name="nombre" required>
                </div>

                <div class="input-group">
                    <label>Apellido Paterno</label>
                    <input type="text" name="apellido_p" required>
                </div>
            </div>

            <div class="fila">
                <div class="input-group">
                    <label>Apellido Materno</label>
                    <input type="text" name="apellido_m" required>
                </div>

                <div class="input-group">
                    <label>Edad</label>
                    <input type="number" name="edad" min="1" required>
                </div>
            </div>

            <div class="fila">
                <div class="input-group">
                    <label>Sexo</label>
                    <select name="sexo" required>
                        <option value="">Selecciona...</option>
                        <option value="Masculino">Masculino</option>
                        <option value="Femenino">Femenino</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>

                <div class="input-group">
                    <label>Teléfono</label>
                    <input type="text" name="telefono" required>
                </div>
            </div>
        </div>

        <!-- SECCIÓN 2: CUENTA -->
        <div class="seccion">
            <h3>Cuenta de Usuario</h3>

            <div class="fila">
                <div class="input-group">
                    <label>Correo Electrónico</label>
                    <input type="email" name="email" required>
                </div>

                <div class="input-group">
                    <label>Usuario</label>
                    <input type="text" name="usuario" required>
                </div>
            </div>

            <div class="fila">
                <div class="input-group">
                    <label>Contraseña</label>
                    <input type="password" name="contrasena" required>
                </div>
            </div>
        </div>

        <!-- SECCIÓN 3: FOTO -->
        <div class="seccion">
            <h3>Imagen de Perfil</h3>

            <div class="input-group">
                <label>Subir Imagen</label>
                <input type="file" name="imagen" accept="image/*">
            </div>
        </div>

        <button type="submit" class="btn-login">Registrar</button>

        <div class="links">
            <a href="Login.php">¿Ya tienes cuenta? Inicia sesión</a>
        </div>

    </form>
</div>

</body>
</html>
