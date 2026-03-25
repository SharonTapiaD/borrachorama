<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');
require_once 'config/conexion.php';

$id     = (int) $_POST['id'];
$estado = $conn->real_escape_string($_POST['estatus']);

if (!$id || !$estado) {
    echo json_encode(['error' => 'No se recibieron datos', 'id' => $id, 'estado' => $estado]);
    exit;
}

$estados_validos = ['completada', 'procesada', 'cancelado'];
if (!in_array($estado, $estados_validos)) {
    echo json_encode(['error' => 'Estado inválido', 'estado' => $estado]);
    exit;
}

$conn->query("UPDATE pedidos SET estatus = '$estado' WHERE id = $id");
$filas = $conn->affected_rows;

$conn->close();
echo json_encode(['success' => true, 'filas' => $filas]);