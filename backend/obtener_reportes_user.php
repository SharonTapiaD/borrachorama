<?php
session_start();
header("Content-Type: application/json");
require __DIR__ . "/config/conexion.php";

$usuario_id = $_SESSION['usuario_id'];

$sql = "SELECT id, tipo, mensaje, estado, DATE_FORMAT(fecha, '%d/%m/%Y') as fecha 
        FROM reportes WHERE usuario_id = ? ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$res = $stmt->get_result();

$reportes = [];
while($row = $res->fetch_assoc()){
    $reportes[] = $row;
}
echo json_encode($reportes);