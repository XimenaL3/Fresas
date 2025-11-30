<?php
// DashboardClientes.php
session_start();

// Solo admins
if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != 2) {
    header("Location: Login.php");
    exit;
}

require_once "../includes/conexion.php"; // tu conexión mysqli en $conn

$idPersona = $_SESSION["idPersona"];

// ------------------------
// Parámetros de visualización
// ------------------------
$por_paso = 6; // cuántos mostrar cada "ver más"
$mostrar = isset($_GET['show']) ? max(6, intval($_GET['show'])) : 6; // empieza en 6
$categoriaSeleccionada = isset($_GET['cat']) ? intval($_GET['cat']) : 0;

// Mensajes flash
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// ------------------------
// Obtener categorías (VistaCategorias)
// ------------------------
$categorias = [];
$sqlCats = "SELECT idCategoria, Nombre, Descripcion, Imagen, TotalPlatillos FROM VistaCategorias ORDER BY Nombre";
if ($resCats = $conn->query($sqlCats)) {
    while ($r = $resCats->fetch_assoc()) $categorias[] = $r;
    $resCats->free();
} else {
    die("Error al obtener categorías: " . $conn->error);
}

// ------------------------
// Contar total de platillos (según filtro de categoría)
// ------------------------
$params = [];
$where = "";
if ($categoriaSeleccionada > 0) {
    $where = " WHERE idCategoria = ? ";
    $params[] = $categoriaSeleccionada;
}

$sqlCount = "SELECT COUNT(DISTINCT idPlatillo) AS total FROM VistaPlatillos" . $where;
$stmtCount = $conn->prepare($sqlCount);
if ($params) {
    $stmtCount->bind_param(str_repeat("i", count($params)), ...$params);
}
if (!$stmtCount->execute()) {
    die("Error al contar platillos: " . $stmtCount->error);
}
$resCount = $stmtCount->get_result();
$rowCount = $resCount->fetch_assoc();
$totalPlatillos = (int) ($rowCount['total'] ?? 0);
$stmtCount->close();

// ------------------------
// Obtener platillos
// ------------------------
$sqlPlatillos = "SELECT idPlatillo, Platillo AS Nombre, Descripcion, Imagen, Categoria, PrecioVenta, idCategoria
                 FROM VistaPlatillos
                 " . $where . "
                 GROUP BY idPlatillo, Platillo, Descripcion, Imagen, Categoria, PrecioVenta, idCategoria
                 ORDER BY idPlatillo ASC
                 LIMIT ?";

$stmtPlat = $conn->prepare($sqlPlatillos);
if ($categoriaSeleccionada > 0) {
    $limit = $mostrar;
    $stmtPlat->bind_param("ii", $categoriaSeleccionada, $limit);
} else {
    $limit = $mostrar;
    $stmtPlat->bind_param("i", $limit);
}

if (!$stmtPlat->execute()) {
    die("Error al obtener platillos: " . $stmtPlat->error);
}

$resPlat = $stmtPlat->get_result();
$platillos = $resPlat->fetch_all(MYSQLI_ASSOC);
$stmtPlat->close();

// ------------------------
// Manejo de "Agregar a la bolsa"
// ------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar'])) {
    $idPlatillo = isset($_POST['idPlatillo']) ? intval($_POST['idPlatillo']) : 0;
    if ($idPlatillo <= 0) {
        $_SESSION['flash'] = "Platillo inválido.";
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }

    if (!empty($_SESSION['idPersona'])) {
        $idPersona = intval($_SESSION['idPersona']);

        $stmt = $conn->prepare("CALL AgregarPlatilloCarrito(?, ?)");
        if ($stmt) {
            $stmt->bind_param("ii", $idPersona, $idPlatillo);
            if ($stmt->execute()) {
                $_SESSION['flash'] = "Se agregó el platillo al carrito (guardado en tu cuenta).";
            } else {
                $_SESSION['flash'] = "Error al agregar al carrito en BD: " . $stmt->error . " — Se agregó localmente.";
                $_SESSION['cart'][$idPlatillo] = ($_SESSION['cart'][$idPlatillo] ?? 0) + 1;
            }
            do { $stmt->store_result(); } while ($conn->more_results() && $conn->next_result());
            $stmt->close();
        } else {
            $_SESSION['flash'] = "Error al preparar llamada al procedimiento: " . $conn->error;
        }
    } else {
        $_SESSION['cart'][$idPlatillo] = ($_SESSION['cart'][$idPlatillo] ?? 0) + 1;
        $_SESSION['flash'] = "Se agregó al carrito en esta sesión. Inicia sesión para guardar tu carrito.";
    }

    $query = $_GET ? '?' . http_build_query($_GET) : '';
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . $query);
    exit;
}

// ------------------------
// Helper imágenes
// ------------------------
function img_or_placeholder($ruta) {
    $base = "imagenes/";
    if (!$ruta) return $base . "placeholder.png";
    if (file_exists(__DIR__ . "/imagenes/" . $ruta)) return $base . $ruta;
    return $base . "placeholder.png";
}

// ------------------------
// CONTADOR REAL DEL CARRITO
// ------------------------
$totalCarrito = 0;
if (isset($_SESSION['idPersona'])) {
    $idP = intval($_SESSION['idPersona']);

    if ($resC = $conn->query("CALL ObtenerCarritoPorPersona($idP)")) {
        while ($fila = $resC->fetch_assoc()) {
            $totalCarrito += intval($fila['Cantidad']);
        }
    }
    $conn->next_result();
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PinkberryDelights - Catálogo</title>
    <link rel="stylesheet" href="DashboardClientes.css">
</head>
<body>

<header class="navbar">
    <div class="logo">PinkberryDelights</div>
    <nav>
        <ul>
            <li><a href="DashboardClientes.php">Inicio</a></li>

            <!-- CONTADOR CORREGIDO Y FUNCIONAL -->
            <li><a href="CarritoClientes.php">Mi bolsa (<?php echo $totalCarrito; ?>)</a></li>
        </ul>
    </nav>
</header>

<main>
    <?php if ($flash): ?>
        <div class="flash"><?php echo htmlspecialchars($flash); ?></div>
    <?php endif; ?>

    <!-- CATEGORÍAS -->
    <section class="categories">
        <div class="container">
            <h3>Categorías</h3>
            <div class="cat-list">
                <a class="cat-btn <?php echo $categoriaSeleccionada==0 ? 'active' : ''; ?>" href="DashboardClientes.php?show=6">Todas</a>
                <?php foreach ($categorias as $cat): ?>
                    <a class="cat-btn <?php echo ($categoriaSeleccionada == $cat['idCategoria']) ? 'active' : ''; ?>"
                       href="DashboardClientes.php?cat=<?php echo $cat['idCategoria']; ?>&show=6">
                        <?php echo htmlspecialchars($cat['Nombre']); ?>
                        <span class="count">(<?php echo (int)$cat['TotalPlatillos']; ?>)</span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- PLATILLOS -->
    <section class="section-products">
        <div class="container">
            <h2><?php echo $categoriaSeleccionada ? ("Platillos: " . htmlspecialchars(array_values(array_filter($categorias, function($c) use ($categoriaSeleccionada){ return $c['idCategoria']==$categoriaSeleccionada; }))[0]['Nombre'] ?? '')) : "Nuestros Postres"; ?></h2>

            <div class="product-grid">
                <?php if (empty($platillos)): ?>
                    <p>No hay platillos para mostrar.</p>
                <?php else: ?>
                    <?php foreach ($platillos as $p): ?>
                        <article class="product-card">
                            <img src="<?php echo img_or_placeholder($p['Imagen']); ?>" alt="<?php echo htmlspecialchars($p['Nombre']); ?>">
                            <h3><?php echo htmlspecialchars($p['Nombre']); ?></h3>
                            <p class="categoria"><?php echo htmlspecialchars($p['Categoria']); ?></p>
                            <p class="descripcion"><?php echo nl2br(htmlspecialchars($p['Descripcion'])); ?></p>
                            <p class="precio">$ <?php echo number_format($p['PrecioVenta'], 2); ?></p>

                            <form method="POST" class="form-agregar">
                                <input type="hidden" name="idPlatillo" value="<?php echo (int)$p['idPlatillo']; ?>">
                                <button type="submit" name="agregar" class="btn-agregar">Agregar a la bolsa</button>
                            </form>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- BOTÓN VER MÁS -->
            <div class="ver-mas">
                <?php if ($mostrar < $totalPlatillos): 
                    $nextShow = $mostrar + $por_paso;
                    $query = [];
                    if ($categoriaSeleccionada) $query['cat'] = $categoriaSeleccionada;
                    $query['show'] = $nextShow;
                    $url = 'DashboardClientes.php?' . http_build_query($query);
                ?>
                    <a class="btn-vermas" href="<?php echo $url; ?>">Ver más</a>
                <?php endif; ?>
            </div>

            <p class="contador">
                Mostrando <?php echo min($mostrar, $totalPlatillos); ?> de <?php echo $totalPlatillos; ?> platillo(s)
            </p>
        </div>
    </section>

    <footer class="footer">
        <div class="container footer-grid">

            <div class="footer-box">
                <h4>Contacto</h4>
                <p>Dirección: Av. Las Rosas #123, Col. Jardines, CDMX</p>
            </div>

            <div class="footer-box">
                <h4>Horario</h4>
                <p>Lun - Dom 9:00 AM - 8:00 PM</p>
            </div>
        </div>
    </footer>
</main>

</body>
</html>
