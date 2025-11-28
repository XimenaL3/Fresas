<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Interfaz de Productos</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            background: linear-gradient(135deg, #ff9ec4, #ff6f91, #ff9671);
            min-height: 100vh;
            padding: 20px;
            color: #fff;
        }
        .container {
            max-width: 900px;
            margin: auto;
            background: rgba(255, 255, 255, 0.15);
            padding: 30px;
            border-radius: 20px;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }
        h2 {
            text-align: center;
            font-size: 28px;
            margin-bottom: 20px;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 30px;
        }
        input {
            padding: 12px;
            border-radius: 10px;
            border: none;
            font-size: 16px;
        }
        button {
            padding: 12px;
            background: #ff4f79;
            border: none;
            color: #fff;
            border-radius: 12px;
            cursor: pointer;
            font-size: 18px;
            transition: 0.3s;
        }
        button:hover {
            background: #ff2e63;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255,255,255,0.2);
            border-radius: 15px;
            overflow: hidden;
        }
        th, td {
            padding: 14px;
            text-align: center;
            color: #fff;
        }
        th {
            background: rgba(0,0,0,0.2);
            font-size: 18px;
        }
        tr:nth-child(even){
            background: rgba(255,255,255,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Agregar Producto</h2>

        <?php include "conexion.php"; ?>

        <?php
        if (isset($_POST["guardar"])) {
            $nombre = $_POST["nombre"];
            $precio = $_POST["precio"];
            $sql = "INSERT INTO productos (nombre, precio) VALUES ('$nombre', '$precio')";
            $conexion->query($sql);
        }
        ?>

        <form method="POST">
            <input type="text" name="nombre" placeholder="Nombre del producto" required />
            <input type="number" step="0.01" name="precio" placeholder="Precio" required />
            <button type="submit" name="guardar">Guardar</button>
        </form>

        <h2>Productos Registrados</h2>

        <table>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Precio</th>
            </tr>

            <?php
            $resultado = $conexion->query("SELECT * FROM productos");
            while ($fila = $resultado->fetch_assoc()) {
                echo "<tr>";
                echo "<td>{$fila['id']}</td>";
                echo "<td>{$fila['nombre']}</td>";
                echo "<td>$ {$fila['precio']}</td>";
                echo "</tr>";
            }
            ?>
        </table>
    </div>
</body>
</html>

