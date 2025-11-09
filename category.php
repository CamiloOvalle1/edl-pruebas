<?php
// category.php
require_once 'config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Obtener parámetros
$subcategory_name = $_GET['subcategory'] ?? '';
$ofertas = isset($_GET['ofertas']);

// Consultar productos según filtro
if ($ofertas) {
    $sql = "SELECT p.*, c.nombre as categoria_nombre, s.nombre as subcategoria_nombre 
            FROM productos p 
            LEFT JOIN categorias c ON p.id_categoria = c.id 
            LEFT JOIN subcategorias s ON p.id_subcategoria = s.id 
            WHERE p.destacado = 1 AND p.activo = 1";
    $page_title = "OFERTAS ESPECIALES";
} elseif ($subcategory_name) {
    $sql = "SELECT p.*, c.nombre as categoria_nombre, s.nombre as subcategoria_nombre 
            FROM productos p 
            LEFT JOIN categorias c ON p.id_categoria = c.id 
            LEFT JOIN subcategorias s ON p.id_subcategoria = s.id 
            WHERE s.nombre = ? AND p.activo = 1";
    $page_title = $subcategory_name;
} else {
    $sql = "SELECT p.*, c.nombre as categoria_nombre, s.nombre as subcategoria_nombre 
            FROM productos p 
            LEFT JOIN categorias c ON p.id_categoria = c.id 
            LEFT JOIN subcategorias s ON p.id_subcategoria = s.id 
            WHERE p.activo = 1 
            LIMIT 20";
    $page_title = "TODOS LOS PRODUCTOS";
}

$stmt = $pdo->prepare($sql);
if ($subcategory_name) {
    $stmt->execute([$subcategory_name]);
} else {
    $stmt->execute();
}
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - REPUESTOS ORIGINALES</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- Menú de Navegación con Submenús -->
    <?php include 'includes/navigation.php'; ?>

    <!-- Contenido de la Categoría -->
    <section class="category-products py-5">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h1 class="section-title mb-4"><?php echo $page_title; ?></h1>
                    <p class="text-muted mb-4"><?php echo count($products); ?> productos encontrados</p>
                </div>
            </div>

            <div class="products-grid">
                <?php if (empty($products)): ?>
                    <div class="col-12 text-center">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            No se encontraron productos en esta categoría.
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach($products as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php if ($product['imagen']): ?>
                                <img src="<?php echo UPLOADS_URL . $product['imagen']; ?>" alt="<?php echo htmlspecialchars($product['nombre']); ?>">
                            <?php else: ?>
                                <div class="image-placeholder">
                                    <i class="fas fa-motorcycle"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h3 class="product-name"><?php echo htmlspecialchars($product['nombre']); ?></h3>
                            <p class="product-category">
                                <small class="text-muted">
                                    <?php echo htmlspecialchars($product['categoria_nombre']); ?> › 
                                    <?php echo htmlspecialchars($product['subcategoria_nombre']); ?>
                                </small>
                            </p>
                            <p class="product-description"><?php echo substr($product['descripcion'] ?? '', 0, 80) . '...'; ?></p>
                            <div class="product-price">$<?php echo number_format($product['precio'], 0, ',', '.'); ?></div>
                            <button class="btn-add-cart" data-product-id="<?php echo $product['id']; ?>">
                                <i class="fas fa-cart-plus"></i> Agregar al Carrito
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Carrito Lateral -->
    <?php include 'includes/cart-sidebar.php'; ?>

    <!-- Modal de Finalizar Compra -->
    <?php include 'includes/checkout-modal.php'; ?>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>