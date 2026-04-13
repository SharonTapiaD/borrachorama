<?php
require __DIR__ . "/config/conexion.php";
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'No hay sesión activa']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    $metodo_pago = $_POST['metodo_pago'] ?? '';
    $descuento_porcentaje = floatval($_POST['descuento_porcentaje'] ?? 0);
    $descuento_fijo = floatval($_POST['descuento_fijo'] ?? 0);
    $fecha_inicio = $_POST['fecha_inicio'] ?? null;
    $fecha_fin = $_POST['fecha_fin'] ?? null;
    $minimo_compra = floatval($_POST['minimo_compra'] ?? 0);
    $activa = isset($_POST['activa']) ? intval($_POST['activa']) : 1;

    if (empty($metodo_pago)) {
        echo json_encode(['success' => false, 'message' => 'Método de pago es requerido']);
        exit;
    }

    if ($descuento_porcentaje <= 0 && $descuento_fijo <= 0) {
        echo json_encode(['success' => false, 'message' => 'Debe especificar un descuento']);
        exit;
    }

    if ($descuento_porcentaje > 0 && $descuento_porcentaje > 100) {
        echo json_encode(['success' => false, 'message' => 'El porcentaje no puede ser mayor a 100%']);
        exit;
    }

    $sql = "INSERT INTO promociones_pago (metodo_pago, descuento_porcentaje, descuento_fijo, fecha_inicio, fecha_fin, activa, minimo_compra)
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sddsssd", $metodo_pago, $descuento_porcentaje, $descuento_fijo, $fecha_inicio, $fecha_fin, $activa, $minimo_compra);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Promoción de pago guardada exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar la promoción']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();
?>