<?php
header("Content-Type: application/json");
require __DIR__ . "/config/conexion.php";

$filtro = $_GET['filtro'] ?? '';
$sql = "SELECT p.id, p.nombre, p.stock, 
        (SELECT SUM(c.cantidad) FROM carrito c JOIN pedidos ped ON c.id = ped.carrito_id WHERE c.producto_id = p.id) as total_vendido
        FROM productos p";

if ($filtro === 'mas_vendidos') {
    $sql .= " ORDER BY total_vendido DESC LIMIT 5";
} elseif ($filtro === 'menos_vendidos') {
    $sql .= " ORDER BY total_vendido ASC LIMIT 5";
} elseif ($filtro === 'baja_rotacion') {
    $sql .= " HAVING total_vendido IS NULL OR total_vendido < 5";
}

$res = $conn->query($sql);
$data = [];
while ($row = $res->fetch_assoc()) { $data[] = $row; }
echo json_encode($data);