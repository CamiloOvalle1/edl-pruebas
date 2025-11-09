<?php
// config.php - CONFIGURACIÓN COMPATIBLE CON TODO EL PROYECTO

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ========================
// CONFIGURACIÓN EXISTENTE (para index.php, admin.php, etc.)
// ========================

// Configuración de la base de datos (NECESARIA para index.php)
define('DB_HOST', 'localhost');
define('DB_NAME', 'edl_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configuración de la aplicación - COMPATIBLE CON INDEX.PHP
define('SITE_NAME', 'REPUESTOS ORIGINALES');
define('SITE_PHONE', '+1 234 567 890');
define('SITE_EMAIL', 'info@repuestosoriginales.com');
define('SITE_ADDRESS', 'Av. Principal 123, Ciudad');
define('SITE_URL', 'https://edl-distribuidor.page.gd/');
define('ADMIN_URL', SITE_URL . 'admin.php');
define('UPLOAD_PATH', __DIR__ . '/uploads/');
define('UPLOADS_URL', SITE_URL . 'uploads/');

// Configuración de MercadoPago
define('MP_ACCESS_TOKEN', 'APP_USR-3534336244263219-110821-46607c45042eacb8a072d53e802745db-2926162241');

// ========================
// NUEVA CONFIGURACIÓN PARA CORREOS
// ========================

// CONFIGURACIÓN GMAIL SMTP (CAMBIAR ESTOS DATOS)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'jocalle97@gmail.com'); // TU EMAIL
define('SMTP_PASSWORD', 'Kmilo123*'); // CONTRASEÑA DE APLICACIÓN
define('SMTP_FROM_EMAIL', 'jocalle97@gmail.com');
define('SMTP_FROM_NAME', 'EDL Distribuidor');
define('EMAIL_ADMIN', 'jocalle97@gmail.com');

// Configuración adicional
define('ORDERS_DIR', 'orders/');

// Inicializar variables de sesión si no existen
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Manejo de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ========================
// CONEXIÓN A BASE DE DATOS (para archivos existentes)
// ========================

// Incluir la clase Database si existe
if (file_exists('includes/database.php')) {
    require_once 'includes/database.php';
    
    try {
        $database = new Database();
        $pdo = $database->getConnection();
    } catch(PDOException $e) {
        error_log("Error de conexión a la base de datos: " . $e->getMessage());
    }
}

// ========================
// FUNCIONES EXISTENTES
// ========================

// Funciones específicas del admin (solo se usan en admin.php)
function isAdmin() {
    return isset($_SESSION['user']) && $_SESSION['user']['rol'] === 'admin';
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: ' . ADMIN_URL . '?action=login');
        exit;
    }
}

// ========================
// CONFIGURACIÓN DE CORREOS (PHPMailer)
// ========================

/**
 * Función para enviar correos con PHPMailer
 * Solo funciona si PHPMailer está disponible
 */
function enviarCorreo($destinatario, $asunto, $cuerpoHTML, $cuerpoTexto = '') {
    // Verificar si PHPMailer está disponible
    if (!file_exists(__DIR__ . '/phpmailer/PHPMailer.php')) {
        error_log("❌ PHPMailer no disponible - No se puede enviar correo a: {$destinatario}");
        return false;
    }
    
    // Incluir PHPMailer solo cuando se necesite
    require_once __DIR__ . '/phpmailer/PHPMailer.php';
    require_once __DIR__ . '/phpmailer/SMTP.php';
    require_once __DIR__ . '/phpmailer/Exception.php';
    
    // Usar PHPMailer con namespace completo para evitar "use"
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // Configuración del servidor SMTP
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';
        
        // Configuración de debug
        $mail->SMTPDebug = 0;
        
        // Remitente y destinatario
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($destinatario);
        $mail->addReplyTo(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        
        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body    = $cuerpoHTML;
        $mail->AltBody = $cuerpoTexto ?: strip_tags($cuerpoHTML);
        
        // Enviar correo
        $mail->send();
        error_log("✅ Correo enviado a: {$destinatario}");
        return true;
        
    } catch (Exception $e) {
        error_log("❌ Error PHPMailer: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Función de respaldo que usa mail() si PHPMailer falla
 */
function enviarCorreoRespaldo($destinatario, $asunto, $cuerpoHTML, $cuerpoTexto = '') {
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">" . "\r\n";
    $headers .= "Reply-To: " . SMTP_FROM_EMAIL . "\r\n";
    
    $resultado = mail($destinatario, $asunto, $cuerpoHTML, $headers);
    error_log("📧 Correo enviado (respaldo) a: {$destinatario} - Resultado: " . ($resultado ? 'Éxito' : 'Falló'));
    
    return $resultado;
}

/**
 * Función inteligente que intenta PHPMailer primero y luego respaldo
 */
function enviarCorreoInteligente($destinatario, $asunto, $cuerpoHTML, $cuerpoTexto = '') {
    // Primero intentar con PHPMailer
    $resultado = enviarCorreo($destinatario, $asunto, $cuerpoHTML, $cuerpoTexto);
    
    // Si falla, intentar con método de respaldo
    if (!$resultado) {
        $resultado = enviarCorreoRespaldo($destinatario, $asunto, $cuerpoHTML, $cuerpoTexto);
    }
    
    return $resultado;
}
?>