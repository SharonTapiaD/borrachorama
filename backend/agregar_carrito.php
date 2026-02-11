<?php
header("Content-Type: application/json");
require __DIR__ . "/config/conexion.php";

$usuario_id  = intval($_POST['usuario_id'] ?? 0);
$producto_id = intval($_POST['producto_id'] ?? 0);

if ($usuario_id <= 0 || $producto_id <= 0) {
    echo json_encode(["status"=>"error","msg"=>"Datos inválidos"]);
    exit;
}

// ¿Ya existe ese producto en el carrito?
$sql = "SELECT id, cantidad FROM carrito WHERE usuario_id=? AND producto_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $usuario_id, $producto_id);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_assoc()) {
    $nuevaCantidad = $row['cantidad'] + 1;
    $upd = $conn->prepare("UPDATE carrito SET cantidad=? WHERE id=?");
    $upd->bind_param("ii", $nuevaCantidad, $row['id']);
    $upd->execute();
} else {
    $ins = $conn->prepare("INSERT INTO carrito (usuario_id, producto_id, cantidad) VALUES (?, ?, 1)");
    $ins->bind_param("ii", $usuario_id, $producto_id);
    $ins->execute();
}

echo json_encode(["status"=>"ok","msg"=>"Producto agregado al carrito"]);
