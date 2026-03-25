<?php
session_start();
header("Content-Type: application/json");

require __DIR__ . "/config/conexion.php";

// puede filtrarse por proveedor_id si se pasa GET/POST
$prov = isset($_GET['proveedor_id']) ? intval($_GET['proveedor_id']) : 0;

$sql = "
SELECT pp.id,
       pp.proveedor_id,
       p.nombre AS proveedor_nombre,
       pp.producto_id,
       pr.nombre AS producto_nombre,
       pp.fecha_pedido,
       pp.fecha_entrega_estimada,
       pp.estado,
       pp.tipo,
       pp.total
FROM pedidos_proveedor pp
LEFT JOIN proveedores p ON pp.proveedor_id = p.id
LEFT JOIN productos pr ON pp.producto_id = pr.id
";
if ($prov > 0) {
    $sql .= " WHERE pp.proveedor_id = $prov";
}
$sql .= " ORDER BY pp.fecha_pedido DESC";

$result = $conn->query($sql);
$rows = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $rows[] = [
            "id"              => (int)$row["id"],
            "proveedor_id"    => (int)$row["proveedor_id"],
            "proveedor_nombre"=> $row["proveedor_nombre"],
            "producto_id"     => (int)$row["producto_id"],
            "producto_nombre" => $row["producto_nombre"],
            "fecha_pedido"    => $row["fecha_pedido"],
            "fecha_entrega_estimada" => $row["fecha_entrega_estimada"],
            "estado"          => $row["estado"],
            "tipo"            => $row["tipo"],
            "total"           => (float)$row["total"]
        ];
    }
}

echo json_encode($rows);
