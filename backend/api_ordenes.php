<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'config/conexion.php';

$r = $conn->query("
    SELECT
        p.id,
        c.nombre AS cliente,
        p.total,
        p.estatus,
        DATE_FORMAT(p.fecha, '%d/%m/%y') AS fecha
    FROM pedidos p
    JOIN clientes c ON c.id = p.cliente_id
    ORDER BY p.fecha DESC
");

$pedidos = [];
while ($row = $r->fetch_assoc()) {
    $pedidos[] = $row;
}

$conn->close();

echo json_encode($pedidos, JSON_UNESCAPED_UNICODE);