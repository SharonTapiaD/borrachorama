<?php
session_start();
header("Content-Type: application/json");
require __DIR__ . "/config/conexion.php";

// Solo Admin o roles autorizados
if (!isset($_SESSION['rol']) || $_SESSION['rol'] === 'usuario') {
    echo json_encode(["status" => "error", "msg" => "No autorizado"]);
    exit;
}

$id_pedido = $_POST['id_pedido'];
$nuevo_estado = $_POST['estado'];

// Actualizar el estado en la tabla pedidos
$sql = "UPDATE pedidos SET estatus = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $nuevo_estado, $id_pedido);

if ($stmt->execute()) {
    echo json_encode(["status" => "ok", "msg" => "Estado del pedido #$id_pedido actualizado"]);
} else {
    echo json_encode(["status" => "error", "msg" => $conn->error]);
}