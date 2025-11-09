<?php
// index.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';

// Conexión a la base de datos
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}

// Obtener productos destacados
$featured_products = $pdo->query("
    SELECT p.*, c.nombre as categoria_nombre 
    FROM productos p 
    LEFT JOIN categorias c ON p.id_categoria = c.id 
    WHERE p.destacado = 1 AND p.activo = 1 
    LIMIT 8
")->fetchAll(PDO::FETCH_ASSOC);

// Obtener categorías para el menú
require_once 'includes/categories_menu.php';

$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REPUESTOS ORIGINALES - Tienda Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
   
    <!-- Menú de Navegación con Submenús -->
    <?php include 'includes/navigation.php'; ?>
    
    <!-- Banner con Slider Automático -->
    <section class="hero-slider">
        <div class="slider-container">
            <div class="slider-wrapper">
                <div class="slide active">
                    <div class="slide-content">
                        <h1>REPUESTOS ORIGINALES</h1>
                        <p>¡Envíos a todo el país!</p>
                        <a href="#productos" class="btn-slider">COMPRA AQUÍ</a>
                    </div>
                </div>
                <div class="slide">
                    <div class="slide-content">
                        <h1>OFERTAS ESPECIALES</h1>
                        <p>Hasta 50% de descuento</p>
                        <a href="#productos" class="btn-slider">VER OFERTAS</a>
                    </div>
                </div>
                <div class="slide">
                    <div class="slide-content">
                        <h1>CALIDAD GARANTIZADA</h1>
                        <p>Repuestos 100% originales</p>
                        <a href="#productos" class="btn-slider">SABER MÁS</a>
                    </div>
                </div>
            </div>
            <!-- Controles del Slider -->
            <button class="slider-prev">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="slider-next">
                <i class="fas fa-chevron-right"></i>
            </button>
            <!-- Indicadores -->
            <div class="slider-indicators">
                <span class="indicator active" data-slide="0"></span>
                <span class="indicator" data-slide="1"></span>
                <span class="indicator" data-slide="2"></span>
            </div>
        </div>
    </section>

    <!-- Productos Destacados -->
    <section id="productos" class="featured-products">
        <div class="container">
            <h2 class="section-title">PRODUCTOS DESTACADOS</h2>
            <div class="products-grid">
                <?php foreach($featured_products as $product): ?>
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
                        <p class="product-description"><?php echo substr($product['descripcion'] ?? '', 0, 80) . '...'; ?></p>
                        <div class="product-price">$<?php echo number_format($product['precio'], 0, ',', '.'); ?></div>
                        <button class="btn-add-cart" data-product-id="<?php echo $product['id']; ?>">
                            <i class="fas fa-cart-plus"></i> Agregar al Carrito
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
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