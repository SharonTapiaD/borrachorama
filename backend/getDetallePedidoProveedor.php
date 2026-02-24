<?php
session_start();
header("Content-Type: application/json");

require __DIR__ . "/config/conexion.php";

$pedido = isset($_GET['pedido_id']) ? intval($_GET['pedido_id']) : 0;

if ($pedido === 0) {
    echo json_encode([]);
    exit;
}

$sql = "
SELECT d.id,
       d.pedido_id,
       d.producto_id,
       pr.nombre AS producto_nombre,
       d.cantidad,
       d.precio_unitario
FROM detalle_pedido_proveedor d
LEFT JOIN productos pr ON d.producto_id = pr.id
WHERE d.pedido_id = $pedido
";

$result = $conn->query($sql);
$rows = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $rows[] = [
            "id" => (int)$row["id"],
            "pedido_id" => (int)$row["pedido_id"],
            "producto_id" => (int)$row["producto_id"],
            "producto_nombre" => $row["producto_nombre"],
            "cantidad" => (int)$row["cantidad"],
            "precio_unitario" => (float)$row["precio_unitario"]
        ];
    }
}

echo json_encode($rows);
