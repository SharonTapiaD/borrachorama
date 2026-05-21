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
$vende_Producto = intval($_POST['vende_Producto'] ?? 0); // NUEVO CAMPO
$producto_especifico_id = intval($_POST['producto_especifico_id'] ?? 0); // NUEVO
$estatus   = $_POST['estatus'] ?? 'activo';

if ($nombre === '') {
    echo json_encode(["status"=>"error","msg"=>"El nombre es obligatorio"]);
    exit;
}

if ($id === 0) {
    // nuevo registro
    $sql = "INSERT INTO proveedores (nombre, contacto, telefono, correo, direccion, vende_Producto, producto_especifico_id, estatus)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $ok = false;
        $msg = "Error en preparación SQL: " . $conn->error;
    } else {
        $stmt->bind_param("ssssssii", $nombre, $contacto, $telefono, $correo, $direccion, $vende_Producto, $producto_especifico_id, $estatus);
        $ok = $stmt->execute();
        $msg = $ok ? "Proveedor agregado" : ("Error al agregar proveedor: " . $stmt->error);
    }
} else {
    // actualización
    $sql = "UPDATE proveedores SET nombre=?, contacto=?, telefono=?, correo=?, direccion=?, vende_Producto=?, producto_especifico_id=?, estatus=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $ok = false;
        $msg = "Error en preparación SQL: " . $conn->error;
    } else {
        $stmt->bind_param("sssssssi", $nombre, $contacto, $telefono, $correo, $direccion, $vende_Producto, $producto_especifico_id, $estatus, $id);
        $ok = $stmt->execute();
        $msg = $ok ? "Proveedor actualizado" : ("Error al actualizar proveedor: " . $stmt->error);
    }
}

echo json_encode(["status"=> $ok ? "ok" : "error", "msg"=> $msg]);
