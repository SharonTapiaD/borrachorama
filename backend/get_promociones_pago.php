<?php
require __DIR__ . "/config/conexion.php";

header('Content-Type: application/json');

try {
    $sql = "SELECT id, metodo_pago, descuento_porcentaje, descuento_fijo, fecha_inicio, fecha_fin, activa, minimo_compra
            FROM promociones_pago
            ORDER BY fecha_inicio DESC";

    $result = $conn->query($sql);

    if ($result) {
        $promociones = [];
        while ($row = $result->fetch_assoc()) {
            $promociones[] = $row;
        }
        echo json_encode($promociones);
    } else {
        echo json_encode(['error' => 'Error al obtener promociones']);
    }

} catch (Exception $e) {
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}

$conn->close();
?>