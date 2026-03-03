<?php
session_start();
header("Content-Type: application/json");
require __DIR__ . "/config/conexion.php";

$proveedor_id    = isset($_POST['proveedor_id']) ? intval($_POST['proveedor_id']) : 0;
$producto_id     = isset($_POST['producto_id']) ? intval($_POST['producto_id']) : 0;
$fecha_pedido    = $_POST['fecha_pedido'] ?? date('Y-m-d');
$fecha_entrega   = $_POST['fecha_entrega_estimada'] ?? null;
$estado          = $_POST['estado'] ?? 'pendiente';
$tipo            = $_POST['tipo'] ?? 'reposicion';
$total           = isset($_POST['total']) ? floatval($_POST['total']) : 0;

if ($proveedor_id === 0 || $producto_id === 0) {
    echo json_encode(["status"=>"error","msg"=>"Proveedor y producto son obligatorios"]);
    exit;
}

$sql = "INSERT INTO pedidos_proveedor (proveedor_id, producto_id, fecha_pedido, fecha_entrega_estimada, estado, tipo, metodo_generacion, total)
        VALUES (?, ?, ?, ?, ?, ?, 'manual', ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iissssd", $proveedor_id, $producto_id, $fecha_pedido, $fecha_entrega, $estado, $tipo, $total);

if ($stmt->execute()) {
    
    $correo_simulado = "Se envió un correo al proveedor informando sobre este pedido manual.";
    echo json_encode(["status"=>"ok","msg"=>"Pedido guardado. " . $correo_simulado]);
} else {
    echo json_encode(["status"=>"error","msg"=>"Error al crear pedido"]);
}