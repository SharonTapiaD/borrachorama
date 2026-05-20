<?php
session_start();
header("Content-Type: application/json");
include __DIR__ . "/config/conexion.php";

// Crear tabla si no existe
$sql_create = "CREATE TABLE IF NOT EXISTS facturas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pedido_id INT NOT NULL UNIQUE,
    numero_factura VARCHAR(50) NOT NULL UNIQUE,
    rfc_cliente VARCHAR(13) NOT NULL,
    razon_social VARCHAR(255) NOT NULL,
    domicilio_fiscal TEXT NOT NULL,
    monto_total DECIMAL(10, 2) NOT NULL,
    fecha_emision DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME ON UPDATE CURRENT_TIMESTAMP,
    estado VARCHAR(50) DEFAULT 'Emitida',
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    INDEX idx_pedido_id (pedido_id),
    INDEX idx_numero_factura (numero_factura)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
$conn->query($sql_create);

// Verificar que el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode([
        "status" => "error",
        "msg" => "No autorizado"
    ]);
    exit;
}

$pedido_id = intval($_POST['pedido_id'] ?? 0);

if ($pedido_id == 0) {
    echo json_encode([
        "status" => "error",
        "msg" => "ID de pedido inválido"
    ]);
    exit;
}

// Verificar que el pedido pertenece al usuario
$sql = "SELECT p.*, u.correo, u.nombre FROM pedidos p 
        JOIN usuarios u ON p.usuario_id = u.id
        WHERE p.id = ? AND p.usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $pedido_id, $_SESSION['usuario_id']);
$stmt->execute();
$pedido = $stmt->get_result()->fetch_assoc();

if (!$pedido) {
    echo json_encode([
        "status" => "error",
        "msg" => "Pedido no encontrado"
    ]);
    exit;
}

// Verificar que existe factura para este pedido
$sql = "SELECT * FROM facturas WHERE pedido_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $pedido_id);
$stmt->execute();
$factura = $stmt->get_result()->fetch_assoc();

if (!$factura) {
    echo json_encode([
        "status" => "error",
        "msg" => "No existe factura para este pedido"
    ]);
    exit;
}

// Preparar el contenido del correo
$asunto = "Tu Factura Fiscal - Borrachorama Pedido #" . $pedido_id;
$correo_destino = $pedido['correo'];
$nombre_cliente = $pedido['nombre'];

$cuerpo = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .container { max-width: 600px; margin: 0 auto; border: 2px solid #FF6F00; padding: 20px; }
        .header { text-align: center; background: #FFD600; padding: 20px; }
        .header h1 { margin: 0; color: #FF6F00; }
        .content { padding: 20px; }
        .factura-info { background: #fffbea; padding: 15px; margin: 20px 0; border-left: 4px solid #FFD600; }
        .button { background: #FF6F00; color: white; padding: 12px 20px; text-decoration: none; display: inline-block; border-radius: 5px; }
        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #999; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🍺 BORRACHORAMA</h1>
            <p>Tu Factura Fiscal</p>
        </div>

        <div class="content">
            <h3>Hola {$nombre_cliente},</h3>
            <p>Tu factura fiscal está lista para descargar. Los detalles son los siguientes:</p>

            <div class="factura-info">
                <p><strong>Número de Factura:</strong> {$factura['numero_factura']}</p>
                <p><strong>RFC:</strong> {$factura['rfc_cliente']}</p>
                <p><strong>Razón Social:</strong> {$factura['razon_social']}</p>
                <p><strong>Monto Total:</strong> \${$factura['monto_total']}</p>
                <p><strong>Fecha de Emisión:</strong> {$factura['fecha_emision']}</p>
                <p><strong>Pedido:</strong> #{$pedido_id}</p>
            </div>

            <p>Puedes acceder a tu factura ingresando a tu cuenta en Borrachorama y descargándola desde la sección de historial de compras.</p>

            <p>Si tienes dudas o necesitas ayuda, no dudes en contactarnos.</p>

            <div style="text-align: center; margin: 30px 0;">
                <a href="http://borrachorama.local/User.html" class="button">Ver en Mi Cuenta</a>
            </div>

            <div class="footer">
                <p>Este es un correo automático de Borrachorama. Por favor no respondas a este correo.</p>
                <p>&copy; 2026 Borrachorama. Todos los derechos reservados.</p>
            </div>
        </div>
    </div>
</body>
</html>
HTML;

// Headers para correo HTML
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
$headers .= "From: facturacion@borrachorama.com" . "\r\n";
$headers .= "Reply-To: soporte@borrachorama.com" . "\r\n";

// Enviar correo
if (mail($correo_destino, $asunto, $cuerpo, $headers)) {
    // Registrar el envío en la BD
    $sql = "UPDATE facturas SET fecha_actualizacion = NOW() WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $factura['id']);
    $stmt->execute();

    echo json_encode([
        "status" => "ok",
        "msg" => "Factura enviada a " . $correo_destino
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "msg" => "Error al enviar el correo. Intenta más tarde."
    ]);
}
?>
