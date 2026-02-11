<?php
header("Content-Type: application/json");
require __DIR__ . "/config/conexion.php";

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(["status"=>"error","msg"=>"ID inválido"]);
    exit;
}

$sql = "SELECT * FROM productos WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode($row);
} else {
    echo json_encode(["status"=>"error","msg"=>"Producto no encontrado"]);
}
