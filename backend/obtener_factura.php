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

// Verificar que el pedido pertenece al usuario
$sql = "SELECT id FROM pedidos WHERE id = ? AND usuario_id = ?";
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

// Buscar si existe factura para este pedido
$sql = "SELECT id, numero_factura, fecha_emision, rfc_cliente, razon_social, domicilio_fiscal, monto_total FROM facturas WHERE pedido_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $pedido_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $factura = $result->fetch_assoc();
    echo json_encode([
        "status" => "ok",
        "factura" => $factura
    ]);
} else {
    echo json_encode([
        "status" => "ok",
        "factura" => null
    ]);
}
?>
