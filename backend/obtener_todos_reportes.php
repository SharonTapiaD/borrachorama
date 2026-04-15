<?php
session_start();
header("Content-Type: application/json");
require __DIR__ . "/config/conexion.php";


if (!isset($_SESSION['rol']) || $_SESSION['rol'] === 'usuario') {
    echo json_encode(["status" => "error", "msg" => "No tienes permisos"]);
    exit;
}

$sql = "SELECT r.id, u.nombre as usuario, r.tipo, r.mensaje, r.respuesta, r.estado, DATE_FORMAT(r.fecha, '%d/%m/%Y %H:%i') as fecha 
        FROM reportes r 
        JOIN usuarios u ON r.usuario_id = u.id 
        ORDER BY r.id DESC";

$res = $conn->query($sql);

if (!$res) {
    echo json_encode(["status" => "error", "msg" => $conn->error]);
    exit;
}

$reportes = [];
while($row = $res->fetch_assoc()){
    $reportes[] = $row;
}

echo json_encode($reportes);