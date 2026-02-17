<?php
session_start();
header("Content-Type: application/json");

require __DIR__ . "/config/conexion.php";

$usuario_id = 1; // fijo admin
$total      = isset($_POST["total"]) ? (float)$_POST["total"] : 0;
$estatus    = $_POST["estatus"] ?? "pendiente";
$cliente_id = isset($_POST["cliente_id"]) ? (int)$_POST["cliente_id"] : 0;
$carrito_id = isset($_POST["carrito_id"]) ? (int)$_POST["carrito_id"] : 0;

$sql = "INSERT INTO pedidos (usuario_id, total, estatus, fecha, cliente_id, carrito_id)
        VALUES (?, ?, ?, NOW(), ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("idsii", $usuario_id, $total, $estatus, $cliente_id, $carrito_id);
$ok = $stmt->execute();

$msg = $ok ? "Pedido registrado correctamente" : "Error al registrar pedido";
echo json_encode(["status" => $ok ? "ok" : "error", "msg" => $msg]);
