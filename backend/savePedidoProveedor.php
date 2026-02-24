<?php
session_start();
header("Content-Type: application/json");

require __DIR__ . "/config/conexion.php";

$id              = isset($_POST['id']) ? intval($_POST['id']) : 0;
$proveedor_id    = isset($_POST['proveedor_id']) ? intval($_POST['proveedor_id']) : 0;
$producto_id     = isset($_POST['producto_id']) ? intval($_POST['producto_id']) : 0;
$fecha_pedido    = $_POST['fecha_pedido'] ?? date('Y-m-d');
$fecha_entrega   = $_POST['fecha_entrega_estimada'] ?? null;
$estado          = $_POST['estado'] ?? 'pendiente';
$tipo            = $_POST['tipo'] ?? 'reposicion';
total            = isset($_POST['total']) ? floatval($_POST['total']) : 0;

if ($proveedor_id === 0 || $producto_id === 0) {
    echo json_encode(["status"=>"error","msg"=>"Proveedor y producto son obligatorios"]);
    exit;
}

if ($id === 0) {
    $sql = "INSERT INTO pedidos_proveedor (proveedor_id, producto_id, fecha_pedido, fecha_entrega_estimada, estado, tipo, total)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisssdd", $proveedor_id, $producto_id, $fecha_pedido, $fecha_entrega, $estado, $tipo, $total);
    $ok = $stmt->execute();
    $msg = $ok ? "Pedido creado" : "Error al crear pedido";
} else {
    $sql = "UPDATE pedidos_proveedor SET proveedor_id=?, producto_id=?, fecha_pedido=?, fecha_entrega_estimada=?, estado=?, tipo=?, total=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisssddi", $proveedor_id, $producto_id, $fecha_pedido, $fecha_entrega, $estado, $tipo, $total, $id);
    $ok = $stmt->execute();
    $msg = $ok ? "Pedido actualizado" : "Error al actualizar pedido";
}

echo json_encode(["status"=> $ok ? "ok" : "error", "msg"=> $msg]);
