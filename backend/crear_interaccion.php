<?php
session_start();
include __DIR__ . "/config/conexion.php";

if (!isset($_SESSION['usuario_id'])) { exit("No autorizado"); }

$usuario_id = $_SESSION['usuario_id'];
$tipo        = $_POST['tipo'];
$descripcion = $_POST['descripcion'];

// 1. Necesitamos el id del cliente asociado a este usuario
// En tu tabla 'clientes', el id es el que usamos.
$sqlCliente = "SELECT id FROM clientes WHERE correo = (SELECT correo FROM usuarios WHERE id = ?)";
$stmtC = $conn->prepare($sqlCliente);
$stmtC->bind_param("i", $usuario_id);
$stmtC->execute();
$resC = $stmtC->get_result();
$cliente = $resC->fetch_assoc();
$cliente_id = $cliente['id'];

// 2. Insertar la interacción
$sql = "INSERT INTO interacciones (cliente_id, usuario_id, tipo, descripcion) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiss", $cliente_id, $usuario_id, $tipo, $descripcion);

if ($stmt->execute()) {
    echo "Ok";
} else {
    echo "Error";
}