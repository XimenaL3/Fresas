<?php
session_start();

// SOLO ADMIN
if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != 1) {
    header("Location: Login.php");
    exit;
}

// Conexión
require_once "../includes/conexion.php";

$mensaje = "";

// =======================
// OBTENER ID
// =======================
if (!isset($_GET["id"])) {
    die("Usuario no especificado.");
}

$idPersona = intval($_GET["id"]);

$sql = "SELECT * FROM VistaUsuarios WHERE idPersona = $idPersona";
$res = $conn->query($sql);

if ($res->num_rows == 0) {
    die("Usuario no encontrado.");
}

$u = $res->fetch_assoc();

// =======================
// GUARDAR CAMBIOS
// =======================
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $Nombre = trim($_POST["nombre"]);
    $ApellidoP = trim($_POST["apellido_p"]);
    $ApellidoM = trim($_POST["apellido_m"]);
    $Telefono = trim($_POST["telefono"]);
    $Email = trim($_POST["email"]);
    $Edad = intval($_POST["edad"]);
    $Sexo = $_POST["sexo"];
    $Estatus = $_POST["estatus"];
    $Usuario = trim($_POST["usuario"]);
    $idRol = intval($_POST["rol"]);

    // Imagen
    $Imagen = $u["Imagen"];

    if (!empty($_FILES["imagen"]["name"])) {

        $carpeta = __DIR__ . "/Imagenes/";

        if (!is_dir($carpeta)) {
            mkdir($carpeta, 0777, true);
        }

        $nombreImg = time() . "_" . basename($_FILES["imagen"]["name"]);
        $ruta = $carpeta . $nombreImg;

        if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $ruta)) {
            $Imagen = $nombreImg;
        }
    }

    // Ejecutar SP
    $stmt = $conn->prepare("CALL EditarPersona(?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param(
        "isssssissssi",
        $idPersona,
        $Nombre,
        $ApellidoP,
        $ApellidoM,
        $Telefono,
        $Email,
        $Edad,
        $Sexo,
        $Estatus,
        $Usuario,
        $Imagen,
        $idRol
    );

    if ($stmt->execute()) {
        $mensaje = "Usuario actualizado correctamente.";
    } else {
        $mensaje = "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuario</title>
    <link rel="stylesheet" href="EditarUsuario.css">
</head>
<body>

<div class="registro-container">
    <form class="registro-box" method="POST" enctype="multipart/form-data">

        <h2>Editar Usuario</h2>

        <!-- MENSAJE -->
        <?php if (!empty($mensaje)): ?>
            <div class="alerta"><?= $mensaje ?></div>
        <?php endif; ?>

        <!-- SECCIÓN 1 -->
        <div class="seccion">
            <h3>Datos Personales</h3>

            <div class="fila">
                <div class="input-group">
                    <label>Nombre</label>
                    <input type="text" name="nombre" value="<?= $u['NombreCompleto'] ?>" required>
                </div>

                <div class="input-group">
                    <label>Usuario</label>
                    <input type="text" name="usuario" value="<?= $u['Usuario'] ?>" required>
                </div>
            </div>

            <div class="fila">
                <div class="input-group">
                    <label>Apellido Paterno</label>
                    <input type="text" name="apellido_p" value="<?= $u['ApellidoPaterno'] ?? '' ?>" required>
                </div>

                <div class="input-group">
                    <label>Apellido Materno</label>
                    <input type="text" name="apellido_m" value="<?= $u['ApellidoMaterno'] ?? '' ?>" required>
                </div>
            </div>

            <div class="fila">
                <div class="input-group">
                    <label>Teléfono</label>
                    <input type="text" name="telefono" value="<?= $u['Telefono'] ?>" required>
                </div>

                <div class="input-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= $u['Email'] ?>" required>
                </div>
            </div>
        </div>

        <!-- SECCIÓN 2 -->
        <div class="seccion">
            <h3>Información del Usuario</h3>

            <div class="fila">
                <div class="input-group">
                    <label>Edad</label>
                    <input type="number" name="edad" value="<?= $u['Edad'] ?? 0 ?>" required>
                </div>

                <div class="input-group">
                    <label>Sexo</label>
                    <select name="sexo">
                        <option <?= $u["Sexo"] == "Masculino" ? "selected" : "" ?>>Masculino</option>
                        <option <?= $u["Sexo"] == "Femenino" ? "selected" : "" ?>>Femenino</option>
                        <option <?= $u["Sexo"] == "Otro" ? "selected" : "" ?>>Otro</option>
                    </select>
                </div>
            </div>

            <div class="fila">
                <div class="input-group">
                    <label>Estatus</label>
                    <select name="estatus">
                        <option <?= $u["Estatus"] == "Activo" ? "selected" : "" ?>>Activo</option>
                        <option <?= $u["Estatus"] == "Inactivo" ? "selected" : "" ?>>Inactivo</option>
                    </select>
                </div>

                <div class="input-group">
                    <label>Rol</label>
                    <select name="rol">
                        <?php
                        $roles = $conn->query("SELECT * FROM Rol");
                        while ($r = $roles->fetch_assoc()):
                        ?>
                            <option value="<?= $r['idRol'] ?>"
                                <?= ($u["Rol"] == $r["NombreRol"]) ? "selected" : "" ?>>
                                <?= $r['NombreRol'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- SECCIÓN 3 -->
        <div class="seccion">
            <h3>Imagen de Perfil</h3>

            <div class="input-group">
                <label>Subir Imagen</label>
                <input type="file" name="imagen" accept="image/*">
            </div>

            <?php if ($u["Imagen"]): ?>
                <img src="Imagenes/<?= $u['Imagen'] ?>" class="preview">
            <?php endif; ?>
        </div>

        <!-- BOTONES -->
        <button type="submit" class="btn-guardar">Guardar Cambios</button>

        <div class="links">
            <a href="VerUsuarios.php">⟵ Regresar</a>
        </div>
    </form>
</div>

</body>
</html>
