<?php
// payment_success.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';

// Verificar si hay una orden en sesión
$order_data = $_SESSION['current_order'] ?? null;

// Limpiar carrito
unset($_SESSION['cart']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago Exitoso - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/payment_success.css">
</head>
<body>
    <div class="success-container">
        <?php if ($order_data): ?>
            <div class="success-icon">✅</div>
            <h1>¡Pago Exitoso!</h1>
            <p class="success-message">Tu pedido ha sido procesado correctamente.</p>
            
            <div class="order-details">
                <h3>Detalles del Pedido</h3>
                <p><strong>Número de Pedido:</strong> <?php echo $order_data['order_id']; ?></p>
                <p><strong>Cliente:</strong> <?php echo $order_data['customer_name']; ?></p>
                <p><strong>Email:</strong> <?php echo $order_data['customer_email']; ?></p>
                <p><strong>Total:</strong> $<?php echo number_format($order_data['total'], 0, ',', '.'); ?> COP</p>
            </div>
            
            <p class="email-notice">Hemos enviado un correo de confirmación a <strong><?php echo $order_data['customer_email']; ?></strong></p>
            
            <div class="actions">
                <a href="index.php" class="btn btn-primary">Volver al Inicio</a>
                <a href="contact.php" class="btn btn-secondary">Contactar Soporte</a>
            </div>
        <?php else: ?>
            <div class="success-icon">❓</div>
            <h1>Pedido no encontrado</h1>
            <p>No se pudo encontrar la información de tu pedido.</p>
            <a href="index.php" class="btn btn-primary">Volver al Inicio</a>
        <?php endif; ?>
    </div>
</body>
</html>