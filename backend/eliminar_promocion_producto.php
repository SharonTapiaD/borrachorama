<?php
header('Content-Type: application/json');
require __DIR__ . "/config/conexion.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$producto_id = $_POST['producto_id'] ?? '';

if (!$producto_id) {
    echo json_encode(['success' => false, 'message' => 'ID de producto requerido']);
    exit;
}

try {
    $sql = "UPDATE productos SET
            descuento_porcentaje = 0,
            descuento_fijo = 0,
            fecha_inicio_promocion = NULL,
            fecha_fin_promocion = NULL,
            promocion_activa = 0
            WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $producto_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Promoción eliminada exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar la promoción']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>