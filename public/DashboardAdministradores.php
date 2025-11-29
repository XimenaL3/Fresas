<?php
session_start();

// Solo admins
if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != 1) {
    header("Location: Login.php");
    exit;
}

// OPCIONES DEL MENÚ CON ARCHIVOS .PHP
$opciones = [
    ["titulo" => "Platillos",     "img" => "imagenes/platillos.png",     "url" => "VerPlatillos.php"],
    ["titulo" => "Ingredientes",  "img" => "imagenes/ingredientes.png",  "url" => "VerIngredientes.php"],
    ["titulo" => "Recetas",       "img" => "imagenes/recetas.png",       "url" => "VerRecetas.php"],
    ["titulo" => "Clientes",      "img" => "imagenes/clientes.png",      "url" => "VerUsuarios.php"],
    ["titulo" => "Ventas",        "img" => "imagenes/ventas.png",        "url" => "Verventas.php"],
    ["titulo" => "Pedidos",       "img" => "imagenes/pedidos.png",       "url" => "Pedidos.php"]
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>PinkberryDelights - Panel Administrador</title>
    <link rel="stylesheet" href="DashboardAdministradores.css">
</head>
<body>

<h1 class="titulo">PinkberryDelights • Panel de Administración</h1>

<div class="menu-grid">
    <?php foreach ($opciones as $op): ?>
        <a href="<?php echo $op['url']; ?>" class="card no-link">
            <div class="img-box">
                <img src="<?php echo $op['img']; ?>" alt="Imagen <?php echo $op['titulo']; ?>">
            </div>
            <h3><?php echo $op['titulo']; ?></h3>
        </a>
    <?php endforeach; ?>
</div>

</body>
</html>
