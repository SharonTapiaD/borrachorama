<?php
session_start();
header("Content-Type: application/json");

require __DIR__ . "/config/conexion.php";

$id          = isset($_POST['id']) ? intval($_POST['id']) : 0;
$pedido_id   = isset($_POST['pedido_id']) ? intval($_POST['pedido_id']) : 0;
$producto_id = isset($_POST['producto_id']) ? intval($_POST['producto_id']) : 0;
$cantidad    = isset($_POST['cantidad']) ? intval($_POST['cantidad']) : 0;
$precio_uni  = isset($_POST['precio_unitario']) ? floatval($_POST['precio_unitario']) : 0;

if ($pedido_id === 0 || $producto_id === 0 || $cantidad <= 0) {
    echo json_encode(["status"=>"error","msg"=>"Faltan datos obligatorios"]);
    exit;
}

if ($id === 0) {
    $sql = "INSERT INTO detalle_pedido_proveedor (pedido_id, producto_id, cantidad, precio_unitario)
            VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiid", $pedido_id, $producto_id, $cantidad, $precio_uni);
    $ok = $stmt->execute();
    $msg = $ok ? "Detalle agregado" : "Error al agregar detalle";
} else {
    $sql = "UPDATE detalle_pedido_proveedor SET producto_id=?, cantidad=?, precio_unitario=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iidi", $producto_id, $cantidad, $precio_uni, $id);
    $ok = $stmt->execute();
    $msg = $ok ? "Detalle actualizado" : "Error al actualizar detalle";
}

echo json_encode(["status"=> $ok ? "ok" : "error", "msg"=> $msg]);
