<?php
session_start();
header("Content-Type: application/json");

require __DIR__ . "/config/conexion.php";

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($id <= 0) {
    echo json_encode(["status"=>"error","msg"=>"ID inválido"]);
    exit;
}

// eliminar detalles de pedidos y pedidos relacionados (opcional) para mantener integridad
$conn->query("DELETE FROM detalle_pedido_proveedor WHERE pedido_id IN (SELECT id FROM pedidos_proveedor WHERE proveedor_id = $id)");
$conn->query("DELETE FROM pedidos_proveedor WHERE proveedor_id = $id");

$sql = "DELETE FROM proveedores WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(["status"=>"ok","msg"=>"Proveedor eliminado"]);
} else {
    echo json_encode(["status"=>"error","msg"=>"Error al eliminar proveedor"]);
}
