<?php
session_start();
header("Content-Type: application/json");
require __DIR__ . "/config/conexion.php";

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(["status" => "error", "code" => "auth_required"]);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

$sql = "SELECT c.id as carrito_id, c.producto_id, c.cantidad, p.nombre, p.precio, p.imagen
        FROM carrito c
        INNER JOIN productos p ON c.producto_id = p.id
        WHERE c.usuario_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$res = $stmt->get_result();

$items = [];
while ($row = $res->fetch_assoc()) {
    $items[] = $row;
}

echo json_encode($items);