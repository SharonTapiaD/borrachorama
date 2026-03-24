<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once 'config/conexion.php';

// ── 1. VENTAS TOTALES (pagado + completada)
$r = $conn->query("
    SELECT COALESCE(SUM(total), 0) AS ventas_totales
    FROM pedidos
    WHERE estatus IN ('pagado', 'completada')
");
$ventas = $r->fetch_assoc();

// ── 2. ÓRDENES
$r = $conn->query("
    SELECT
        COUNT(*) AS total_ordenes,
        SUM(CASE WHEN estatus = 'pendiente'   THEN 1 ELSE 0 END) AS pendientes,
        SUM(CASE WHEN estatus IN ('pagado','completada') THEN 1 ELSE 0 END) AS completadas,
        SUM(CASE WHEN estatus = 'cancelado'   THEN 1 ELSE 0 END) AS canceladas
    FROM pedidos
");
$ordenes = $r->fetch_assoc();

// ── 3. CLIENTES
$r = $conn->query("
    SELECT
        COUNT(*) AS total_clientes,
        SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) AS clientes_activos
    FROM clientes
");
$clientes = $r->fetch_assoc();

// ── 4. INVENTARIO
$r = $conn->query("
    SELECT
        COALESCE(SUM(stock), 0) AS total_unidades,
        SUM(CASE WHEN stock < stock_minimo THEN 1 ELSE 0 END) AS productos_stock_bajo
    FROM productos
    WHERE estatus = 'activo'
");
$inventario = $r->fetch_assoc();

// ── 5. VENTAS MENSUALES (pagado + completada, últimos 6 meses)
$r = $conn->query("
    SELECT
        DATE_FORMAT(fecha, '%b') AS mes,
        DATE_FORMAT(fecha, '%Y-%m') AS periodo,
        COALESCE(SUM(total), 0) AS total_ventas
    FROM pedidos
    WHERE estatus IN ('pagado', 'completada')
      AND fecha >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY YEAR(fecha), MONTH(fecha)
    ORDER BY YEAR(fecha), MONTH(fecha)
");
$ventas_mensuales = [];
while ($row = $r->fetch_assoc()) {
    $ventas_mensuales[] = $row;
}

// ── 6. VENTAS POR CATEGORÍA (pagado + completada)
$r = $conn->query("
    SELECT
        c.nombre AS categoria,
        COALESCE(SUM(p.total), 0) AS total_ventas
    FROM pedidos p
    JOIN detalle_pedido dp ON dp.pedido_id = p.id
    JOIN productos pr      ON pr.id = dp.producto_id
    JOIN categorias c      ON c.id  = pr.categoria_id
    WHERE p.estatus IN ('pagado', 'completada')
    GROUP BY c.id, c.nombre
    ORDER BY total_ventas DESC
");
$ventas_categoria = [];
while ($row = $r->fetch_assoc()) {
    $ventas_categoria[] = $row;
}

$conn->close();

// ── RESPUESTA
echo json_encode([
    'ventas_totales'   => (float) $ventas['ventas_totales'],
    'ordenes'          => [
        'total'       => (int) $ordenes['total_ordenes'],
        'pendientes'  => (int) $ordenes['pendientes'],
        'completadas' => (int) $ordenes['completadas'],
        'canceladas'  => (int) $ordenes['canceladas'],
    ],
    'clientes'         => [
        'total'   => (int) $clientes['total_clientes'],
        'activos' => (int) $clientes['clientes_activos'],
    ],
    'inventario'       => [
        'total_unidades'       => (int) $inventario['total_unidades'],
        'productos_stock_bajo' => (int) $inventario['productos_stock_bajo'],
    ],
    'ventas_mensuales' => $ventas_mensuales,
    'ventas_categoria' => $ventas_categoria,
], JSON_UNESCAPED_UNICODE);