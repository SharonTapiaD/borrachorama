<?php
session_start();
require __DIR__ . "/config/conexion.php";
$u_id = $_SESSION['usuario_id'];
$p_id = $_POST['producto_id'];

$sql = "DELETE FROM carrito WHERE usuario_id = ? AND producto_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $u_id, $p_id);
$stmt->execute();
echo json_encode(["status" => "ok"]);