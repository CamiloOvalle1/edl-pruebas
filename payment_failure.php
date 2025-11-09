<?php
session_start();
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago Fallido - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/top-bar.php'; ?>
    <?php include 'includes/header.php'; ?>
    
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <div class="card border-danger shadow">
                    <div class="card-body py-5">
                        <i class="fas fa-times-circle text-danger mb-4" style="font-size: 5rem;"></i>
                        <h1 class="text-danger mb-3">Pago Fallido</h1>
                        <p class="lead mb-4">Lo sentimos, hubo un problema al procesar tu pago.</p>
                        <p class="text-muted mb-4">Por favor intenta nuevamente o contacta con nuestro soporte.</p>
                        <div class="mt-4">
                            <a href="./" class="btn btn-primary btn-lg me-3">
                                <i class="fas fa-home me-2"></i>Volver al Inicio
                            </a>
                            <a href="carrito.php" class="btn btn-outline-primary btn-lg">
                                <i class="fas fa-shopping-cart me-2"></i>Reintentar Pago
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>