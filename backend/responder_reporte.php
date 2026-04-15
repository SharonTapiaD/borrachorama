<?php
session_start();
header("Content-Type: application/json");
require __DIR__ . "/config/conexion.php";

if (!isset($_SESSION['rol']) || $_SESSION['rol'] === 'usuario') {
    echo json_encode(["status" => "error", "msg" => "No tienes permisos"]);
    exit;
}

$id = intval($_POST['id'] ?? 0);
$respuesta = trim($_POST['respuesta'] ?? '');
$nuevo_estado = $_POST['estado'] ?? 'Resuelto';

if ($id <= 0 || empty($respuesta)) {
    echo json_encode(["status" => "error", "msg" => "La respuesta no puede estar vacía"]);
    exit;
}

$sql = "UPDATE reportes SET respuesta = ?, estado = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssi", $respuesta, $nuevo_estado, $id);

if ($stmt->execute()) {
    echo json_encode(["status" => "ok"]);
} else {
    echo json_encode(["status" => "error", "msg" => "Error en la base de datos"]);
}