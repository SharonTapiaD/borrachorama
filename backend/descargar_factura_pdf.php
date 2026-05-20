<?php
session_start();
header("Content-Type: application/json");
include __DIR__ . "/config/conexion.php";

// Verificar que el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode([
        "status" => "error",
        "msg" => "No autorizado"
    ]);
    exit;
}

$pedido_id = intval($_GET['pedido_id'] ?? 0);

if ($pedido_id == 0) {
    echo json_encode([
        "status" => "error",
        "msg" => "ID de pedido inválido"
    ]);
    exit;
}

// Obtener datos del pedido
$sql = "SELECT pe.*, u.nombre as cliente_nombre, u.correo, pf.telefono, f.numero_factura, f.rfc_cliente, f.razon_social, f.domicilio_fiscal, f.fecha_emision
        FROM pedidos pe
        JOIN usuarios u ON pe.usuario_id = u.id
        LEFT JOIN perfiles pf ON u.id = pf.usuario_id
        LEFT JOIN facturas f ON pe.id = f.pedido_id
        WHERE pe.id = ? AND pe.usuario_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode([
        "status" => "error",
        "msg" => "Error en preparación de consulta: " . $conn->error
    ]);
    exit;
}
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

// Si no hay factura, devolver error
if (!$pedido['numero_factura']) {
    echo json_encode([
        "status" => "error",
        "msg" => "No hay factura para este pedido"
    ]);
    exit;
}

// Obtener detalles de productos del pedido
$sql = "SELECT dp.cantidad, dp.precio_unitario, p.nombre FROM detalle_pedido dp JOIN productos p ON dp.producto_id = p.id WHERE dp.pedido_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode([
        "status" => "error",
        "msg" => "Error en preparación de consulta de productos: " . $conn->error
    ]);
    exit;
}
$stmt->bind_param("i", $pedido_id);
$stmt->execute();
$productos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Generar HTML para la factura
$html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Factura {$pedido['numero_factura']}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: white;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            border: 2px solid #333;
            padding: 30px;
            background: white;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #FF6F00;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #FF6F00;
            margin: 0;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .factura-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        .info-section {
            background: #fffbea;
            padding: 15px;
            border-left: 4px solid #FFD600;
        }
        .info-section h3 {
            margin: 0 0 10px 0;
            color: #FF6F00;
            font-size: 14px;
        }
        .info-section p {
            margin: 5px 0;
            font-size: 13px;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th {
            background: #FFD600;
            padding: 10px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #999;
        }
        td {
            padding: 10px;
            border: 1px solid #999;
        }
        .total-section {
            display: flex;
            justify-content: flex-end;
            margin: 20px 0;
        }
        .total-box {
            background: #fffbea;
            padding: 15px 30px;
            border: 2px solid #FFD600;
            text-align: right;
        }
        .total-box h3 {
            margin: 0;
            color: #FF6F00;
        }
        .total-box .total {
            font-size: 24px;
            font-weight: bold;
            color: #28a745;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #999;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🍺 BORRACHORAMA</h1>
            <p>Tienda de Cervezas - Aguascalientes</p>
            <p style="font-size: 16px; font-weight: bold;">FACTURA #{$pedido['numero_factura']}</p>
        </div>

        <div class="factura-info">
            <div class="info-section">
                <h3>👤 CLIENTE</h3>
                <p><strong>Nombre:</strong> {$pedido['razon_social']}</p>
                <p><strong>RFC:</strong> {$pedido['rfc_cliente']}</p>
                <p><strong>Email:</strong> {$pedido['correo']}</p>
                <p><strong>Teléfono:</strong> {$pedido['telefono']}</p>
            </div>

            <div class="info-section">
                <h3>📋 FACTURA</h3>
                <p><strong>Número:</strong> {$pedido['numero_factura']}</p>
                <p><strong>Fecha:</strong> {$pedido['fecha_emision']}</p>
                <p><strong>Pedido:</strong> #{$pedido['id']}</p>
                <p><strong>Domicilio Fiscal:</strong> {$pedido['domicilio_fiscal']}</p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th style="text-align: center;">Cantidad</th>
                    <th style="text-align: right;">Precio Unitario</th>
                    <th style="text-align: right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
HTML;

$subtotal = 0;
foreach ($productos as $prod) {
    $cantidad = intval($prod['cantidad']);
    $precio = floatval($prod['precio_unitario']);
    $subtotal_prod = $cantidad * $precio;
    $subtotal += $subtotal_prod;
    
    $html .= <<<HTML
                <tr>
                    <td>{$prod['nombre']}</td>
                    <td style="text-align: center;">{$cantidad}</td>
                    <td style="text-align: right;">\${$precio}</td>
                    <td style="text-align: right;">\${$subtotal_prod}</td>
                </tr>
HTML;
}

$iva = $subtotal * 0.16;
$envio = 80.00;
$total = $subtotal + $iva + $envio;

$html .= <<<HTML
            </tbody>
        </table>

        <div class="total-section">
            <div class="total-box">
                <p><strong>Subtotal:</strong> \${number_format($subtotal, 2)}</p>
                <p><strong>IVA (16%):</strong> \${number_format($iva, 2)}</p>
                <p><strong>Costo de Envío:</strong> \${number_format($envio, 2)}</p>
                <h3>Total: \${number_format($total, 2)} MXN</h3>
            </div>
        </div>

        <div class="footer">
            <p>Factura generada automáticamente por Borrachorama</p>
            <p>Válida para fines fiscales conforme a la legislación mexicana</p>
            <p>Emitida: {$pedido['fecha_emision']}</p>
        </div>
    </div>
</body>
</html>
HTML;

// Convertir HTML a PDF usando dompdf si está disponible, sino lo mostramos como HTML
// Para esta versión simplificada, vamos a generar como PDF descargable

// Usar la librería integrada de PHP o generar como descarga
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="Factura_' . $pedido['numero_factura'] . '.html"');
echo $html;
?>
