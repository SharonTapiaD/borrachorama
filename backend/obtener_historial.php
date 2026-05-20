<?php
session_start();
header("Content-Type: application/json");
require __DIR__ . "/config/conexion.php";

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(["status" => "error", "msg" => "Sesión no iniciada"]);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Consultamos los pedidos del usuario
$sql = "SELECT p.id, p.total, p.estatus, DATE_FORMAT(p.fecha, '%d/%m/%Y %H:%i') as fecha_formateada, 
        f.id as factura_id, f.numero_factura 
        FROM pedidos p 
        LEFT JOIN facturas f ON p.id = f.pedido_id 
        WHERE p.usuario_id = ? 
        ORDER BY p.id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$res = $stmt->get_result();

$pedidos = [];
while ($row = $res->fetch_assoc()) {
    $pedidos[] = $row;
}

echo json_encode($pedidos);