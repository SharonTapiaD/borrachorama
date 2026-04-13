<?php
session_start();
header("Content-Type: application/json");

require __DIR__ . "/config/conexion.php";

// Consulta con INNER JOIN para traer el nombre de la categoría
$sql = "
SELECT 
    p.id,
    p.nombre,
    p.descripcion,
    p.imagen,
    p.precio,
    p.stock,
    p.stock_minimo,
    p.estatus,
    p.descuento_porcentaje,
    p.descuento_fijo,
    p.fecha_inicio_promocion,
    p.fecha_fin_promocion,
    p.tipo_promocion,
    p.promocion_activa,
    c.nombre AS categoria_nombre
FROM productos p
INNER JOIN categorias c ON p.categoria_id = c.id
";

$result = $conn->query($sql);

$productos = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $productos[] = [
            "id"          => (int)$row["id"],
            "name"        => $row["nombre"],
            "category"    => $row["categoria_nombre"], // ahora devuelve el nombre
            "description" => $row["descripcion"],
            "image"       => $row["imagen"],
            "price"       => (float)$row["precio"],
            "stock"       => (int)$row["stock"],
            "min_stock"   => isset($row["stock_minimo"]) ? (int)$row["stock_minimo"] : 0,
            "status"      => (int)$row["estatus"],
            "discount_percentage" => (float)($row["descuento_porcentaje"] ?? 0),
            "discount_fixed" => (float)($row["descuento_fijo"] ?? 0),
            "promotion_start" => $row["fecha_inicio_promocion"],
            "promotion_end" => $row["fecha_fin_promocion"],
            "promotion_type" => $row["tipo_promocion"] ?? 'producto',
            "promotion_active" => (int)($row["promocion_activa"] ?? 0)
        ];
    }

    echo json_encode($productos);
} else {
    echo json_encode([]);
}
