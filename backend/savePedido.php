<?php
session_start();
header("Content-Type: application/json");
require __DIR__ . "/config/conexion.php";

$usuario_id = 1; 
$total      = isset($_POST["total"]) ? (float)$_POST["total"] : 0;
$estatus    = $_POST["estatus"] ?? "pendiente";
$cliente_id = isset($_POST["cliente_id"]) ? (int)$_POST["cliente_id"] : 0;
$carrito_id = isset($_POST["carrito_id"]) ? (int)$_POST["carrito_id"] : 0;


$sql = "INSERT INTO pedidos (usuario_id, total, estatus, fecha, cliente_id, carrito_id) VALUES (?, ?, ?, NOW(), ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("idsii", $usuario_id, $total, $estatus, $cliente_id, $carrito_id);

if ($stmt->execute()) {
   
    $sql_cart = "SELECT producto_id, cantidad FROM carrito WHERE usuario_id = ?";
    $stmt_cart = $conn->prepare($sql_cart);
    
    $sesion_actual = $_SESSION['usuario_id'] ?? 1; 
    $stmt_cart->bind_param("i", $sesion_actual);
    $stmt_cart->execute();
    $res_cart = $stmt_cart->get_result();

    // Restar el stock de cada producto vendido
    while ($item = $res_cart->fetch_assoc()) {
        $upd_stock = $conn->prepare("UPDATE productos SET stock = stock - ? WHERE id = ? AND stock >= ?");
        $upd_stock->bind_param("iii", $item['cantidad'], $item['producto_id'], $item['cantidad']);
        $upd_stock->execute();
    }

    echo json_encode(["status" => "ok", "msg" => "Pedido registrado y stock actualizado."]);
} else {
    echo json_encode(["status" => "error", "msg" => "Error al registrar pedido."]);
}
?>