<?php
session_start();
header("Content-Type: application/json");
require __DIR__ . "/config/conexion.php";

$pedido_id = $_GET['id'] ?? 0;

// Traemos los productos de ese pedido
$sql = "SELECT dp.cantidad, dp.precio_unitario, p.nombre, p.imagen 
        FROM detalle_pedido dp 
        JOIN productos p ON dp.producto_id = p.id 
        WHERE dp.pedido_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $pedido_id);
$stmt->execute();
$res = $stmt->get_result();

$detalles = [];
while ($row = $res->fetch_assoc()) {
    $detalles[] = $row;
}

echo json_encode($detalles);