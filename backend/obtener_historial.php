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
$sql = "SELECT id, total, estatus, DATE_FORMAT(fecha, '%d/%m/%Y %H:%i') as fecha_formateada 
        FROM pedidos 
        WHERE usuario_id = ? 
        ORDER BY id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$res = $stmt->get_result();

$pedidos = [];
while ($row = $res->fetch_assoc()) {
    $pedidos[] = $row;
}

echo json_encode($pedidos);