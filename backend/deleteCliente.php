<?php
session_start();
header("Content-Type: application/json");

require __DIR__ . "/config/conexion.php";

$id = isset($_POST["id"]) ? (int)$_POST["id"] : 0;

if ($id <= 0) {
    echo json_encode([
        "status" => "error",
        "msg" => "ID inválido"
    ]);
    exit;
}

// Primero eliminamos interacciones relacionadas (si quieres mantener integridad)
$conn->query("DELETE FROM interacciones WHERE cliente_id = $id");

// Luego eliminamos pedidos relacionados (opcional, según tu modelo)
$conn->query("DELETE FROM pedidos WHERE cliente_id = $id");

// Finalmente eliminamos el cliente
$sql = "DELETE FROM clientes WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "ok",
        "msg" => "Cliente eliminado correctamente"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "msg" => "Error al eliminar cliente"
    ]);
}
