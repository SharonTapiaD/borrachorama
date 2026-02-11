<?php
session_start();
header("Content-Type: application/json");

require __DIR__ . "/config/conexion.php";

$id        = $_POST["id"] ?? "";
$nombre    = $_POST["nombre"] ?? "";
$correo    = $_POST["correo"] ?? "";
$telefono  = $_POST["telefono"] ?? "";
$empresa   = $_POST["empresa"] ?? "";
$estado    = $_POST["estado"] ?? "activo";
$etapa_crm = $_POST["etapa_crm"] ?? "Prospecto";

if ($id === "") {
    // Alta
    $sql = "INSERT INTO clientes (nombre, correo, telefono, empresa, fecha_registro, estado, etapa_crm)
            VALUES (?, ?, ?, ?, NOW(), ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $nombre, $correo, $telefono, $empresa, $estado, $etapa_crm);
    $ok = $stmt->execute();
    $msg = $ok ? "Cliente agregado correctamente" : "Error al agregar cliente";
} else {
    // Edición
    $sql = "UPDATE clientes SET nombre=?, correo=?, telefono=?, empresa=?, estado=?, etapa_crm=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $nombre, $correo, $telefono, $empresa, $estado, $etapa_crm, $id);
    $ok = $stmt->execute();
    $msg = $ok ? "Cliente actualizado correctamente" : "Error al actualizar cliente";
}

echo json_encode(["status" => $ok ? "ok" : "error", "msg" => $msg]);
