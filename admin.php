<?php
// admin.php - Versión completa con modales

// Incluir config primero para tener las funciones disponibles
require_once 'config.php';

$action = $_GET['action'] ?? 'login';

// Verificar autenticación para todas las acciones excepto login
if ($action !== 'login' && $action !== 'auth') {
    if (!isAdmin()) {
        header('Location: ' . ADMIN_URL . '?action=login');
        exit;
    }
}

// Procesar acciones
switch ($action) {
    case 'login':
        showLogin();
        break;
        
    case 'auth':
        processLogin();
        break;
        
    case 'logout':
        processLogout();
        break;
        
    case 'dashboard':
        showDashboard();
        break;
        
    case 'products':
        showProducts();
        break;
        
    case 'save_product':
        saveProduct();
        break;
        
    case 'delete_product':
        deleteProduct();
        break;
        
    case 'categories':
        showCategories();
        break;
        
    case 'sales':
        showSales();
        break;
        
    case 'users':
        showUsers();
        break;
        
    default:
        showDashboard();
        break;
}

function showLogin() {
    if (isAdmin()) {
        header('Location: ' . ADMIN_URL . '?action=dashboard');
        exit;
    }
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login - <?php echo SITE_NAME; ?></title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <link rel="stylesheet" href="css/admin.css">
    </head>
    <body class="login-body">
        <div class="login-container">
            <div class="login-card">
                <div class="text-center mb-4">
                    <h2 class="login-title">Panel de Administración</h2>
                    <p class="text-muted"><?php echo SITE_NAME; ?></p>
                </div>
                
                <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>Credenciales incorrectas
                </div>
                <?php endif; ?>
                
                <form method="POST" action="?action=auth">
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo Electrónico</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                    </button>
                </form>
                
                <div class="text-center mt-3">
                    <a href="./" class="text-decoration-none">
                        <i class="fas fa-arrow-left me-1"></i>Volver al sitio
                    </a>
                </div>

                <!-- Credenciales de prueba -->
                <div class="mt-4 p-3 bg-light rounded">
                    <small class="text-muted">
                        <strong>Credenciales de prueba:</strong><br>
                        Email: admin@motorepuestos.com<br>
                        Contraseña: password
                    </small>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
}

function processLogin() {
    global $pdo;
    
    if ($_POST) {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE correo = ? AND rol = 'admin'");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['contraseña'])) {
            $_SESSION['user'] = $user;
            header('Location: ' . ADMIN_URL . '?action=dashboard');
            exit;
        }
    }
    
    header('Location: ' . ADMIN_URL . '?action=login&error=1');
    exit;
}

function processLogout() {
    unset($_SESSION['user']);
    header('Location: ' . ADMIN_URL . '?action=login');
    exit;
}

function showDashboard() {
    global $pdo;
    
    // Estadísticas básicas
    $total_products = $pdo->query("SELECT COUNT(*) FROM productos")->fetchColumn();
    $total_sales = $pdo->query("SELECT COUNT(*) FROM ventas WHERE estado = 'completada'")->fetchColumn();
    $total_revenue = $pdo->query("SELECT COALESCE(SUM(total), 0) FROM ventas WHERE estado = 'completada'")->fetchColumn();
    $total_users = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
    
    include 'includes/admin_header.php';
    ?>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'includes/admin_sidebar.php'; ?>
            
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                </div>

                <!-- Estadísticas -->
                <div class="row">
                    <div class="col-md-3 mb-4">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-primary">
                                        <i class="fas fa-box"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h5 class="card-title"><?php echo $total_products; ?></h5>
                                        <p class="card-text">Productos</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-success">
                                        <i class="fas fa-shopping-cart"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h5 class="card-title"><?php echo $total_sales; ?></h5>
                                        <p class="card-text">Ventas</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-warning">
                                        <i class="fas fa-dollar-sign"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h5 class="card-title">$<?php echo number_format($total_revenue, 2); ?></h5>
                                        <p class="card-text">Ingresos</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-info">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h5 class="card-title"><?php echo $total_users; ?></h5>
                                        <p class="card-text">Usuarios</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mensaje de bienvenida -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Bienvenido al Panel de Administración</h5>
                                <p class="card-text">
                                    Desde aquí puedes gestionar todos los aspectos de tu tienda de repuestos.
                                    Usa el menú lateral para navegar entre las diferentes secciones.
                                </p>
                                <div class="row mt-4">
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-box text-primary fa-2x me-3"></i>
                                            <div>
                                                <h6>Gestionar Productos</h6>
                                                <small class="text-muted">Agregar, editar y eliminar productos</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-tags text-success fa-2x me-3"></i>
                                            <div>
                                                <h6>Organizar Categorías</h6>
                                                <small class="text-muted">Gestionar categorías y subcategorías</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-chart-bar text-warning fa-2x me-3"></i>
                                            <div>
                                                <h6>Ver Reportes</h6>
                                                <small class="text-muted">Analizar ventas y estadísticas</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <?php include 'includes/admin_footer.php';
}

function showProducts() {
    global $pdo;
    
    $stmt = $pdo->query("
        SELECT p.*, c.nombre as categoria_nombre, s.nombre as subcategoria_nombre 
        FROM productos p 
        LEFT JOIN categorias c ON p.id_categoria = c.id 
        LEFT JOIN subcategorias s ON p.id_subcategoria = s.id 
        ORDER BY p.fecha_creacion DESC
    ");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener categorías y subcategorías para los modales
    $categories = $pdo->query("SELECT * FROM categorias ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
    $subcategories = $pdo->query("SELECT * FROM subcategorias ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
    
    include 'includes/admin_header.php';
    ?>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'includes/admin_sidebar.php'; ?>
            
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Gestión de Productos</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                        <i class="fas fa-plus me-2"></i>Agregar Producto
                    </button>
                </div>

                <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i>Operación realizada correctamente.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($_GET['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <div class="card table-card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Producto</th>
                                        <th>Categoría</th>
                                        <th>Precio</th>
                                        <th>Stock</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td><?php echo $product['id']; ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if ($product['imagen']): ?>
                                                        <?php
                                                        $image_path = UPLOAD_PATH . $product['imagen'];
                                                        $image_url = UPLOADS_URL . $product['imagen'];
                                                        $image_exists = file_exists($image_path);
                                                        ?>
                                                        <div>
                                                            <?php if ($image_exists): ?>
                                                                <img src="<?php echo $image_url; ?>" class="product-image-admin me-3" alt="<?php echo htmlspecialchars($product['nombre']); ?>">
                                                            <?php else: ?>
                                                                <div class="text-danger small">
                                                                    <i class="fas fa-exclamation-triangle"></i> 
                                                                    Imagen no encontrada: <?php echo $product['imagen']; ?>
                                                                </div>
                                                                <div class="product-image-admin me-3 bg-light d-flex align-items-center justify-content-center">
                                                                    <i class="fas fa-image text-muted"></i>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="product-image-admin me-3 bg-light d-flex align-items-center justify-content-center">
                                                            <i class="fas fa-box text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($product['nombre']); ?></strong>
                                                        <?php if ($product['destacado']): ?>
                                                        <span class="badge bg-warning ms-1">Destacado</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                        <td>
                                            <div>
                                                <small class="text-muted"><?php echo htmlspecialchars($product['categoria_nombre']); ?></small>
                                                <br>
                                                <small><?php echo htmlspecialchars($product['subcategoria_nombre']); ?></small>
                                            </div>
                                        </td>
                                        <td>$<?php echo number_format($product['precio'], 0, ',', '.'); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $product['stock'] > 0 ? 'success' : 'danger'; ?>">
                                                <?php echo $product['stock']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($product['activo']): ?>
                                                <span class="badge bg-success">Activo</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="table-actions">
                                            <button type="button" class="btn btn-sm btn-outline-primary btn-edit" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editProductModal"
                                                    data-product-id="<?php echo $product['id']; ?>"
                                                    data-product-name="<?php echo htmlspecialchars($product['nombre']); ?>"
                                                    data-product-desc="<?php echo htmlspecialchars($product['descripcion'] ?? ''); ?>"
                                                    data-product-category="<?php echo $product['id_categoria']; ?>"
                                                    data-product-subcategory="<?php echo $product['id_subcategoria']; ?>"
                                                    data-product-price="<?php echo $product['precio']; ?>"
                                                    data-product-stock="<?php echo $product['stock']; ?>"
                                                    data-product-image="<?php echo $product['imagen'] ?? ''; ?>"
                                                    data-product-featured="<?php echo $product['destacado']; ?>"
                                                    data-product-active="<?php echo $product['activo']; ?>"
                                                    title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#deleteProductModal"
                                                    data-product-id="<?php echo $product['id']; ?>"
                                                    data-product-name="<?php echo htmlspecialchars($product['nombre']); ?>"
                                                    title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal para Agregar Producto -->
    <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProductModalLabel">Agregar Nuevo Producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="?action=save_product" enctype="multipart/form-data" id="addProductForm">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="nombre" class="form-label">Nombre del Producto *</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="descripcion" class="form-label">Descripción</label>
                                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="id_categoria" class="form-label">Categoría *</label>
                                            <select class="form-select" id="id_categoria" name="id_categoria" required>
                                                <option value="">Seleccionar categoría</option>
                                                <?php foreach ($categories as $category): ?>
                                                <option value="<?php echo $category['id']; ?>">
                                                    <?php echo htmlspecialchars($category['nombre']); ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="id_subcategoria" class="form-label">Subcategoría *</label>
                                            <select class="form-select" id="id_subcategoria" name="id_subcategoria" required>
                                                <option value="">Seleccionar subcategoría</option>
                                                <?php foreach ($subcategories as $subcategory): ?>
                                                <option value="<?php echo $subcategory['id']; ?>" data-category="<?php echo $subcategory['id_categoria']; ?>">
                                                    <?php echo htmlspecialchars($subcategory['nombre']); ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="precio" class="form-label">Precio *</label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" class="form-control" id="precio" name="precio" step="100" min="0" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="stock" class="form-label">Stock *</label>
                                            <input type="number" class="form-control" id="stock" name="stock" min="0" value="0" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="imagen" class="form-label">Imagen del Producto</label>
                                    <input type="file" class="form-control" id="imagen" name="imagen" accept="image/*" data-preview="addImagePreview">
                                    <div class="form-text">Formatos: JPG, PNG, GIF. Máx: 2MB</div>
                                    <div id="addImagePreview" class="mt-2"></div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="destacado" name="destacado" value="1">
                                        <label class="form-check-label" for="destacado">Producto Destacado</label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="activo" name="activo" value="1" checked>
                                        <label class="form-check-label" for="activo">Producto Activo</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Producto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Editar Producto -->
    <div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProductModalLabel">Editar Producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="?action=save_product" enctype="multipart/form-data" id="editProductForm">
                    <input type="hidden" id="edit_product_id" name="id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="edit_nombre" class="form-label">Nombre del Producto *</label>
                                    <input type="text" class="form-control" id="edit_nombre" name="nombre" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="edit_descripcion" class="form-label">Descripción</label>
                                    <textarea class="form-control" id="edit_descripcion" name="descripcion" rows="3"></textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="edit_id_categoria" class="form-label">Categoría *</label>
                                            <select class="form-select" id="edit_id_categoria" name="id_categoria" required>
                                                <option value="">Seleccionar categoría</option>
                                                <?php foreach ($categories as $category): ?>
                                                <option value="<?php echo $category['id']; ?>">
                                                    <?php echo htmlspecialchars($category['nombre']); ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="edit_id_subcategoria" class="form-label">Subcategoría *</label>
                                            <select class="form-select" id="edit_id_subcategoria" name="id_subcategoria" required>
                                                <option value="">Seleccionar subcategoría</option>
                                                <?php foreach ($subcategories as $subcategory): ?>
                                                <option value="<?php echo $subcategory['id']; ?>" data-category="<?php echo $subcategory['id_categoria']; ?>">
                                                    <?php echo htmlspecialchars($subcategory['nombre']); ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="edit_precio" class="form-label">Precio *</label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" class="form-control" id="edit_precio" name="precio" step="0.01" min="0" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="edit_stock" class="form-label">Stock *</label>
                                            <input type="number" class="form-control" id="edit_stock" name="stock" min="0" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="edit_imagen" class="form-label">Imagen del Producto</label>
                                    <input type="file" class="form-control" id="edit_imagen" name="imagen" accept="image/*" data-preview="editImagePreview">
                                    <div class="form-text">Formatos: JPG, PNG, GIF. Máx: 2MB</div>
                                    
                                    <div id="currentImage" class="mt-2">
                                        <p class="mb-1">Imagen actual:</p>
                                        <div id="currentImageContainer"></div>
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" id="edit_eliminar_imagen" name="eliminar_imagen" value="1">
                                            <label class="form-check-label" for="edit_eliminar_imagen">Eliminar imagen actual</label>
                                        </div>
                                    </div>
                                    
                                    <div id="editImagePreview" class="mt-2"></div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="edit_destacado" name="destacado" value="1">
                                        <label class="form-check-label" for="edit_destacado">Producto Destacado</label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="edit_activo" name="activo" value="1">
                                        <label class="form-check-label" for="edit_activo">Producto Activo</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Actualizar Producto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Eliminar Producto -->
    <div class="modal fade" id="deleteProductModal" tabindex="-1" aria-labelledby="deleteProductModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteProductModalLabel">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que quieres eliminar el producto <strong id="deleteProductName"></strong>?</p>
                    <p class="text-danger"><small>Esta acción no se puede deshacer.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" action="?action=delete_product" id="deleteProductForm" style="display: inline;">
                        <input type="hidden" id="delete_product_id" name="id">
                        <button type="submit" class="btn btn-danger">Eliminar Producto</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/admin_footer.php';
}

function saveProduct() {
    global $pdo;
    
    $product_id = $_POST['id'] ?? null;
    
    if ($_POST) {
        try {
            $nombre = trim($_POST['nombre']);
            $descripcion = trim($_POST['descripcion']);
            $id_categoria = (int)$_POST['id_categoria'];
            $id_subcategoria = (int)$_POST['id_subcategoria'];
            $precio = (float)$_POST['precio'];
            $stock = (int)$_POST['stock'];
            $destacado = isset($_POST['destacado']) ? 1 : 0;
            $activo = isset($_POST['activo']) ? 1 : 0;
            
            // Validaciones básicas
            if (empty($nombre) || $id_categoria <= 0 || $id_subcategoria <= 0 || $precio <= 0) {
                throw new Exception("Datos incompletos o inválidos");
            }
            
            if ($product_id) {
                // Editar producto existente
                $stmt = $pdo->prepare("SELECT imagen FROM productos WHERE id = ?");
                $stmt->execute([$product_id]);
                $current_product = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $imagen = $current_product['imagen'];
                
                // Procesar eliminación de imagen
                if (isset($_POST['eliminar_imagen']) && $_POST['eliminar_imagen'] == '1' && $imagen) {
                    if (file_exists(UPLOAD_PATH . $imagen)) {
                        unlink(UPLOAD_PATH . $imagen);
                    }
                    $imagen = null;
                }
                
                // Procesar nueva imagen
                if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                    // Eliminar imagen anterior si existe
                    if ($imagen && file_exists(UPLOAD_PATH . $imagen)) {
                        unlink(UPLOAD_PATH . $imagen);
                    }
                    
                    $imagen = processImageUpload($_FILES['imagen']);
                }
                
                $stmt = $pdo->prepare("
                    UPDATE productos 
                    SET nombre = ?, descripcion = ?, id_categoria = ?, id_subcategoria = ?, 
                        precio = ?, stock = ?, imagen = ?, destacado = ?, activo = ? 
                    WHERE id = ?
                ");
                
                $stmt->execute([
                    $nombre, $descripcion, $id_categoria, $id_subcategoria, 
                    $precio, $stock, $imagen, $destacado, $activo, $product_id
                ]);
                
            } else {
                // Nuevo producto
                $imagen = null;
                
                // Procesar imagen
                if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                    $imagen = processImageUpload($_FILES['imagen']);
                }
                
                $stmt = $pdo->prepare("
                    INSERT INTO productos (nombre, descripcion, id_categoria, id_subcategoria, precio, stock, imagen, destacado, activo) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $nombre, $descripcion, $id_categoria, $id_subcategoria, 
                    $precio, $stock, $imagen, $destacado, $activo
                ]);
            }
            
            header('Location: ' . ADMIN_URL . '?action=products&success=1');
            exit;
            
        } catch (Exception $e) {
            header('Location: ' . ADMIN_URL . '?action=products&error=' . urlencode($e->getMessage()));
            exit;
        }
    }
    
    header('Location: ' . ADMIN_URL . '?action=products');
    exit;
}

function processImageUpload($file) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $fileType = $file['type'];
    
    if (!in_array($fileType, $allowedTypes)) {
        throw new Exception("Tipo de archivo no permitido. Use JPG, PNG o GIF.");
    }
    
    if ($file['size'] > 2 * 1024 * 1024) {
        throw new Exception("El archivo es demasiado grande. Máximo 2MB.");
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $imagen = 'producto_' . time() . '.' . $extension;
    $uploadPath = UPLOAD_PATH . $imagen;
    
    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        throw new Exception("Error al subir la imagen.");
    }
    
    return $imagen;
}

function deleteProduct() {
    global $pdo;
    
    $product_id = $_POST['id'] ?? 0;
    
    if ($product_id) {
        try {
            // Verificar si el producto existe
            $stmt = $pdo->prepare("SELECT imagen FROM productos WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($product) {
                // Eliminar imagen si existe
                if ($product['imagen'] && file_exists(UPLOAD_PATH . $product['imagen'])) {
                    unlink(UPLOAD_PATH . $product['imagen']);
                }
                
                // Eliminar producto
                $stmt = $pdo->prepare("DELETE FROM productos WHERE id = ?");
                $stmt->execute([$product_id]);
            }
            
            header('Location: ' . ADMIN_URL . '?action=products&success=1');
            exit;
            
        } catch (Exception $e) {
            header('Location: ' . ADMIN_URL . '?action=products&error=' . urlencode($e->getMessage()));
            exit;
        }
    }
    
    header('Location: ' . ADMIN_URL . '?action=products');
    exit;
}

// Funciones placeholder para las otras secciones
function showCategories() {
    include 'includes/admin_header.php';
    ?>
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/admin_sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Gestión de Categorías</h1>
                </div>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Sección de categorías en desarrollo.
                </div>
            </main>
        </div>
    </div>
    <?php include 'includes/admin_footer.php';
}

function showSales() {
    include 'includes/admin_header.php';
    ?>
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/admin_sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Gestión de Ventas</h1>
                </div>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Sección de ventas en desarrollo.
                </div>
            </main>
        </div>
    </div>
    <?php include 'includes/admin_footer.php';
}

function showUsers() {
    include 'includes/admin_header.php';
    ?>
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/admin_sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Gestión de Usuarios</h1>
                </div>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Sección de usuarios en desarrollo.
                </div>
            </main>
        </div>
    </div>
    <?php include 'includes/admin_footer.php';
}
?>