<?php
include 'config/conexion.php';
session_start();

// Leer los datos recibidos
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || empty($data['items'])) {
    echo json_encode(["status" => "error", "msg" => "El servidor no recibió productos. Revisa el orden del JS."]);
    exit;
}

$conn->begin_transaction();

try {
    foreach ($data['items'] as $item) {
        $id_prod = $item['producto_id'];
        $cantidad = $item['cantidad'];

        // IMPORTANTE: Tu tabla es 'productos', columna 'stock', llave 'id'
        $query = "UPDATE productos SET stock = stock - ? WHERE id = ? AND stock >= ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iii", $cantidad, $id_prod, $cantidad);
        $stmt->execute();

        if ($stmt->affected_rows === 0) {
            throw new Exception("Stock insuficiente o producto no encontrado (ID: $id_prod)");
        }
    }

    $conn->commit();
    echo json_encode(["status" => "ok"]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["status" => "error", "msg" => $e->getMessage()]);
}