<?php
header('Content-Type: application/json');
require __DIR__ . "/config/conexion.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$producto_id = $_POST['producto_id'] ?? '';
$tipo_descuento = $_POST['tipo_descuento'] ?? '';
$valor = $_POST['valor'] ?? '';
$activa = $_POST['activa'] ?? '1';
$fecha_inicio = $_POST['fecha_inicio'] ?? null;
$fecha_fin = $_POST['fecha_fin'] ?? null;

if (!$producto_id || !$tipo_descuento || $valor === '') {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

try {
    if ($tipo_descuento === 'porcentaje') {
        $sql = "UPDATE productos SET
                descuento_porcentaje = ?,
                descuento_fijo = 0,
                fecha_inicio_promocion = ?,
                fecha_fin_promocion = ?,
                promocion_activa = ?
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("dssii", $valor, $fecha_inicio, $fecha_fin, $activa, $producto_id);
    } else {
        $sql = "UPDATE productos SET
                descuento_porcentaje = 0,
                descuento_fijo = ?,
                fecha_inicio_promocion = ?,
                fecha_fin_promocion = ?,
                promocion_activa = ?
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("dssii", $valor, $fecha_inicio, $fecha_fin, $activa, $producto_id);
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Promoción guardada exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar la promoción']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>