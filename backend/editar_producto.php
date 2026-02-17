<?php
header("Content-Type: application/json");
require __DIR__ . "/config/conexion.php";

$id = intval($_POST['id'] ?? 0);
$nombre = trim($_POST['nombre'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$categoria_id = intval($_POST['categoria_id'] ?? 0);
$precio = floatval($_POST['precio'] ?? 0);
$stock = intval($_POST['stock'] ?? 0);
$estatus = intval($_POST['estatus'] ?? 1);

if ($id <= 0) {
    echo json_encode(["status"=>"error","msg"=>"ID inválido"]);
    exit;
}

$sql = "UPDATE productos SET categoria_id=?, nombre=?, descripcion=?, precio=?, stock=?, estatus=? WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("issdiii", $categoria_id, $nombre, $descripcion, $precio, $stock, $estatus, $id);

if ($stmt->execute()) {
    echo json_encode(["status"=>"ok","msg"=>"Producto actualizado"]);
} else {
    echo json_encode(["status"=>"error","msg"=>"Error al actualizar: ".$conn->error]);
}
