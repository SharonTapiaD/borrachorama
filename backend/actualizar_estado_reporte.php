<?php
session_start();
header("Content-Type: application/json");
require __DIR__ . "/config/conexion.php";

$id = $_POST['id'];
$nuevo_estado = $_POST['estado'];

$sql = "UPDATE reportes SET estado = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $nuevo_estado, $id);

if ($stmt->execute()) {
    echo json_encode(["status" => "ok"]);
} else {
    echo json_encode(["status" => "error"]);
}