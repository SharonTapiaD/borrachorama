<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'config/conexion.php';

$r = $conn->query("
    SELECT
        id,
        nombre,
        descripcion,
        precio,
        stock,
        stock_minimo,
        estatus,
        categoria_id
    FROM productos
    WHERE estatus = 'activo'
    ORDER BY id ASC
");

$productos = [];
while ($row = $r->fetch_assoc()) {
    $productos[] = $row;
}

$conn->close();

echo json_encode($productos, JSON_UNESCAPED_UNICODE);