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

$pedido_id = intval($_POST['pedido_id'] ?? 0);
$rfc = trim($_POST['rfc'] ?? '');
$razon_social = trim($_POST['razon_social'] ?? '');
$domicilio = trim($_POST['domicilio'] ?? '');

// Validar datos
if ($pedido_id == 0 || $rfc == '' || $razon_social == '' || $domicilio == '') {
    echo json_encode([
        "status" => "error",
        "msg" => "Faltan datos requeridos"
    ]);
    exit;
}

// Validar RFC (13 caracteres)
if (strlen($rfc) != 13) {
    echo json_encode([
        "status" => "error",
        "msg" => "El RFC debe tener 13 caracteres"
    ]);
    exit;
}

// Verificar que el pedido pertenece al usuario
$sql = "SELECT id, total FROM pedidos WHERE id = ? AND usuario_id = ?";
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

// Verificar que no exista ya una factura para este pedido
$sql = "SELECT id FROM facturas WHERE pedido_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $pedido_id);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    echo json_encode([
        "status" => "error",
        "msg" => "Este pedido ya tiene una factura"
    ]);
    exit;
}

// Generar número de factura único
$numero_factura = "FAC-" . date('Ymd') . "-" . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

// Insertar la factura
$sql = "INSERT INTO facturas (pedido_id, numero_factura, rfc_cliente, razon_social, domicilio_fiscal, fecha_emision, monto_total) 
        VALUES (?, ?, ?, ?, ?, NOW(), ?)";
$stmt = $conn->prepare($sql);
$total = floatval($pedido['total']);
$stmt->bind_param("isssssd", $pedido_id, $numero_factura, $rfc, $razon_social, $domicilio, $total);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "ok",
        "msg" => "Factura creada exitosamente",
        "numero_factura" => $numero_factura
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "msg" => "Error al guardar la factura: " . $stmt->error
    ]);
}
?>
