docker-compose.yml
www/
   index.php
   conexion.php

<?php
$conexion = new mysqli("mysql", "root", "12345", "mi_base");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
?>
