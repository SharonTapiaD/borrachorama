<?php
session_start();
header("Content-Type: application/json");
require __DIR__ . "/config/conexion.php";

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(["status" => "error", "code" => "auth_required"]);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

$sql = "SELECT c.id as carrito_id, c.producto_id, c.cantidad, p.nombre, p.precio, p.imagen,
                p.descuento_porcentaje, p.descuento_fijo, p.fecha_inicio_promocion,
                p.fecha_fin_promocion, p.promocion_activa, cat.nombre as categoria
        FROM carrito c
        INNER JOIN productos p ON c.producto_id = p.id
        LEFT JOIN categorias cat ON p.categoria_id = cat.id
        WHERE c.usuario_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$res = $stmt->get_result();

$items = [];
while ($row = $res->fetch_assoc()) {
    // Verificar si la promoción está activa y en fecha válida
    $promocionActiva = false;
    if ($row['promocion_activa']) {
        $ahora = new DateTime();
        $fechaInicio = $row['fecha_inicio_promocion'] ? new DateTime($row['fecha_inicio_promocion']) : null;
        $fechaFin = $row['fecha_fin_promocion'] ? new DateTime($row['fecha_fin_promocion']) : null;

        if ((!$fechaInicio || $ahora >= $fechaInicio) && (!$fechaFin || $ahora <= $fechaFin)) {
            $promocionActiva = true;
        }
    }

    $items[] = [
        'carrito_id' => $row['carrito_id'],
        'producto_id' => $row['producto_id'],
        'cantidad' => $row['cantidad'],
        'nombre' => $row['nombre'],
        'precio' => $row['precio'],
        'imagen' => $row['imagen'],
        'descuento_porcentaje' => $row['descuento_porcentaje'] ?? 0,
        'descuento_fijo' => $row['descuento_fijo'] ?? 0,
        'promocion_activa' => $promocionActiva,
        'categoria' => $row['categoria'],
        // Debug info
        'debug_promocion_activa_db' => $row['promocion_activa'],
        'debug_fecha_inicio' => $row['fecha_inicio_promocion'],
        'debug_fecha_fin' => $row['fecha_fin_promocion'],
        'debug_ahora' => date('Y-m-d H:i:s')
    ];
}

echo json_encode($items);
?>