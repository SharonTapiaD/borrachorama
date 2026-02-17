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
    p.estatus,
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
            "status"      => (int)$row["estatus"]
        ];
    }

    echo json_encode($productos);
} else {
    echo json_encode([]);
}
