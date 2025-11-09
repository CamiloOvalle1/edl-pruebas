<?php
require_once 'config.php';

$search_term = $_GET['q'] ?? '';
$results = [];

if (!empty($search_term)) {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $stmt = $pdo->prepare("
            SELECT p.*, c.nombre as categoria_nombre 
            FROM productos p 
            LEFT JOIN categorias c ON p.id_categoria = c.id 
            WHERE (p.nombre LIKE ? OR p.descripcion LIKE ?) AND p.activo = 1
            ORDER BY p.destacado DESC, p.nombre ASC
        ");
        $search_like = "%$search_term%";
        $stmt->execute([$search_like, $search_like]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // Manejar error
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar: <?php echo htmlspecialchars($search_term); ?> - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/top-bar.php'; ?>
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/navigation.php'; ?>
    
    <section class="search-results py-5">
        <div class="container">
            <h1 class="mb-4">Resultados de búsqueda</h1>
            
            <?php if (!empty($search_term)): ?>
                <p class="lead">Buscando: "<strong><?php echo htmlspecialchars($search_term); ?></strong>"</p>
                
                <?php if (empty($results)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        No se encontraron productos que coincidan con tu búsqueda.
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach($results as $product): ?>
                        <div class="col-md-3 mb-4">
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
                                    <div class="product-price">$<?php echo number_format($product['precio'], 2); ?></div>
                                    <button class="btn-add-cart" data-product-id="<?php echo $product['id']; ?>">
                                        <i class="fas fa-cart-plus"></i> Agregar al Carrito
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Por favor ingresa un término de búsqueda.
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <?php include 'includes/cart-sidebar.php'; ?>
    <?php include 'includes/checkout-modal.php'; ?>
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>