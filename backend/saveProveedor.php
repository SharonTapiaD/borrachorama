<?php
session_start();
header("Content-Type: application/json");

require __DIR__ . "/config/conexion.php";

$id        = isset($_POST['id']) ? intval($_POST['id']) : 0;
$nombre    = trim($_POST['nombre'] ?? '');
$contacto  = trim($_POST['contacto'] ?? '');
$telefono  = trim($_POST['telefono'] ?? '');
$correo    = trim($_POST['correo'] ?? '');
$direccion = trim($_POST['direccion'] ?? '');
$estatus   = $_POST['estatus'] ?? 'activo';

if ($nombre === '') {
    echo json_encode(["status"=>"error","msg"=>"El nombre es obligatorio"]);
    exit;
}

if ($id === 0) {
    // nuevo registro
    $sql = "INSERT INTO proveedores (nombre, contacto, telefono, correo, direccion, estatus)
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $ok = false;
        $msg = "Error en preparación SQL: " . $conn->error;
    } else {
        $stmt->bind_param("ssssss", $nombre, $contacto, $telefono, $correo, $direccion, $estatus);
        $ok = $stmt->execute();
        $msg = $ok ? "Proveedor agregado" : ("Error al agregar proveedor: " . $stmt->error);
    }
} else {
    // actualización
    $sql = "UPDATE proveedores SET nombre=?, contacto=?, telefono=?, correo=?, direccion=?, estatus=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $ok = false;
        $msg = "Error en preparación SQL: " . $conn->error;
    } else {
        $stmt->bind_param("ssssssi", $nombre, $contacto, $telefono, $correo, $direccion, $estatus, $id);
        $ok = $stmt->execute();
        $msg = $ok ? "Proveedor actualizado" : ("Error al actualizar proveedor: " . $stmt->error);
    }
}

echo json_encode(["status"=> $ok ? "ok" : "error", "msg"=> $msg]);
