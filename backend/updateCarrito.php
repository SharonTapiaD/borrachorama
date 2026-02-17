<?php
header("Content-Type: application/json");
require __DIR__ . "/config/conexion.php";

$id = intval($_POST['id'] ?? 0);
$cantidad = intval($_POST['cantidad'] ?? 0);

if ($id <= 0 || $cantidad < 0) {
    echo json_encode(["status"=>"error","msg"=>"Datos inválidos"]);
    exit;
}

if ($cantidad == 0) {
    $del = $conn->prepare("DELETE FROM carrito WHERE id=?");
    $del->bind_param("i", $id);
    $del->execute();
    echo json_encode(["status"=>"ok","msg"=>"Producto eliminado"]);
} else {
    $upd = $conn->prepare("UPDATE carrito SET cantidad=? WHERE id=?");
    $upd->bind_param("ii", $cantidad, $id);
    $upd->execute();
    echo json_encode(["status"=>"ok","msg"=>"Cantidad actualizada"]);
}
