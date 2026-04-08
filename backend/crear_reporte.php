<?php
session_start();
header("Content-Type: application/json");
require __DIR__ . "/config/conexion.php";

$usuario_id = $_SESSION['usuario_id'];
$tipo = $_POST['tipo'];
$mensaje = $_POST['mensaje'];

$sql = "INSERT INTO reportes (usuario_id, tipo, mensaje) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $usuario_id, $tipo, $mensaje);

if ($stmt->execute()) {
    echo json_encode(["status" => "ok"]);
} else {
    echo json_encode(["status" => "error"]);
}