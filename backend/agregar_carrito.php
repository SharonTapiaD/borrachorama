<?php
session_start(); // ¡IMPORTANTE! Debe ser la primera línea
header("Content-Type: application/json");
require __DIR__ . "/config/conexion.php";

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(["status" => "error", "msg" => "Inicia sesión primero"]);
    exit;
}

$usuario_id  = $_SESSION['usuario_id']; 
$producto_id = intval($_POST['producto_id'] ?? 0);

if ($producto_id <= 0) {
    echo json_encode(["status" => "error", "msg" => "ID de producto no válido"]);
    exit;
}

// 1. Ver si ya existe
$sql = "SELECT id, cantidad FROM carrito WHERE usuario_id = ? AND producto_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $usuario_id, $producto_id);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_assoc()) {
    $nuevaCantidad = $row['cantidad'] + 1;
    $upd = $conn->prepare("UPDATE carrito SET cantidad = ? WHERE id = ?");
    $upd->bind_param("ii", $nuevaCantidad, $row['id']);
    $upd->execute();
} else {
    $ins = $conn->prepare("INSERT INTO carrito (usuario_id, producto_id, cantidad) VALUES (?, ?, 1)");
    $ins->bind_param("ii", $usuario_id, $producto_id);
    $ins->execute();
}

echo json_encode(["status" => "ok", "msg" => "Agregado correctamente"]);