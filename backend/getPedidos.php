<?php
session_start();
header("Content-Type: application/json");

require __DIR__ . "/config/conexion.php";

$sql = "
SELECT 
    p.id,
    p.usuario_id,
    p.total,
    p.estatus,
    p.fecha,
    p.cliente_id,
    p.carrito_id,
    COALESCE(c.nombre, c2.nombre, 'Cliente desconocido') AS cliente_nombre
FROM pedidos p
LEFT JOIN clientes c ON p.cliente_id = c.id
LEFT JOIN clientes c2 ON p.cliente_id = c2.usuario_id AND c.id IS NULL
ORDER BY p.fecha DESC
";

$result = $conn->query($sql);

$pedidos = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pedidos[] = [
            "id"            => (int)$row["id"],
            "usuario_id"    => (int)$row["usuario_id"],
            "total"         => (float)$row["total"],
            "estatus"       => $row["estatus"],
            "fecha"         => $row["fecha"],
            "cliente_id"    => (int)$row["cliente_id"],
            "carrito_id"    => (int)$row["carrito_id"],
            "cliente_nombre"=> $row["cliente_nombre"]
        ];
    }
    echo json_encode($pedidos);
} else {
    echo json_encode([]);
}
