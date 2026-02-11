<?php
session_start();
header("Content-Type: application/json");

require __DIR__ . "/config/conexion.php";

$cliente_id = isset($_GET["cliente_id"]) ? (int)$_GET["cliente_id"] : 0;
$pedido_id  = isset($_GET["pedido_id"]) ? (int)$_GET["pedido_id"] : 0;
$carrito_id = isset($_GET["carrito_id"]) ? (int)$_GET["carrito_id"] : 0;

$sql = "
SELECT 
    p.producto_id,
    pr.nombre AS producto,
    c.cantidad
FROM pedidos p
INNER JOIN productos pr ON p.producto_id = pr.id
INNER JOIN carrito c ON p.carrito_id = c.id
WHERE p.pedido_id = ? AND p.carrito_id = ? AND p.cliente_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $pedido_id, $carrito_id, $cliente_id);
$stmt->execute();
$result = $stmt->get_result();

$productos = [];
while ($row = $result->fetch_assoc()) {
    $productos[] = [
        "producto_id" => (int)$row["producto_id"],
        "producto"    => $row["producto"],
        "cantidad"    => (int)$row["cantidad"]
    ];
}

echo json_encode($productos);
