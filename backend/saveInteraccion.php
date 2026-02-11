<?php
session_start();
header("Content-Type: application/json");

require __DIR__ . "/config/conexion.php";

$cliente_id  = isset($_POST["cliente_id"]) ? (int)$_POST["cliente_id"] : 0;
$usuario_id  = 1; // fijo admin
$tipo        = $_POST["tipo"] ?? "";
$descripcion = $_POST["descripcion"] ?? "";

$sql = "INSERT INTO interacciones (cliente_id, usuario_id, tipo, descripcion, fecha)
        VALUES (?, ?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiss", $cliente_id, $usuario_id, $tipo, $descripcion);
$ok = $stmt->execute();

$msg = $ok ? "Interacción registrada correctamente" : "Error al registrar interacción";
echo json_encode(["status" => $ok ? "ok" : "error", "msg" => $msg]);
