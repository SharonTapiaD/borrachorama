<?php
header("Content-Type: application/json");
require __DIR__ . "/config/conexion.php";

$usuario_id = intval($_GET['usuario_id'] ?? 0);

$sql = "SELECT c.id, c.producto_id, c.cantidad, p.nombre, p.precio
        FROM carrito c
        JOIN productos p ON c.producto_id = p.id
        WHERE c.usuario_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$res = $stmt->get_result();

$items = [];
while ($row = $res->fetch_assoc()) {
    $items[] = $row;
}

echo json_encode($items);
