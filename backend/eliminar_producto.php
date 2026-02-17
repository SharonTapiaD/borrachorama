<?php
session_start();
header("Content-Type: application/json");
require __DIR__ . "/config/conexion.php";

$id = intval($_POST['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(["status"=>"error","msg"=>"ID inválido"]);
    exit;
}

$sql = "DELETE FROM productos WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(["status"=>"ok","msg"=>"Producto eliminado"]);
} else {
    echo json_encode(["status"=>"error","msg"=>"Error al eliminar: ".$conn->error]);
}
