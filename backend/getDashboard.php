<?php
session_start();
header("Content-Type: application/json");

require __DIR__ . "/config/conexion.php";

// Contadores generales
$total     = $conn->query("SELECT COUNT(*) AS c FROM clientes")->fetch_assoc()["c"];
$activos   = $conn->query("SELECT COUNT(*) AS c FROM clientes WHERE estado='activo'")->fetch_assoc()["c"];
$inactivos = $conn->query("SELECT COUNT(*) AS c FROM clientes WHERE estado='inactivo'")->fetch_assoc()["c"];

// Clientes con sus compras
$sql = "
SELECT 
    c.id AS cliente_id,
    c.nombre,
    c.correo,
    p.id AS pedido_id,
    p.carrito_id,
    p.total,
    (SELECT MAX(fecha) FROM interacciones i WHERE i.cliente_id=c.id) AS ultima_interaccion
FROM clientes c
LEFT JOIN pedidos p ON c.id = p.cliente_id
";

$res = $conn->query($sql);

$riesgo = [];
while ($row = $res->fetch_assoc()) {
    $riesgo[] = [
        "cliente_id"       => (int)$row["cliente_id"],
        "nombre"           => $row["nombre"],
        "correo"           => $row["correo"],
        "pedido_id"        => $row["pedido_id"] ? (int)$row["pedido_id"] : null,
        "carrito_id"       => $row["carrito_id"] ? (int)$row["carrito_id"] : null,
        "total"            => $row["total"] ? (float)$row["total"] : 0,
        "ultima_interaccion"=> $row["ultima_interaccion"]
    ];
}

// Ordenar por total ascendente (los que compraron menos primero)
usort($riesgo, function($a, $b) {
    return $a["total"] <=> $b["total"];
});

// Etapas CRM
$etapas = [];
foreach (["Prospecto","Activo","Frecuente","Inactivo"] as $e) {
    $etapas[$e] = $conn->query("SELECT COUNT(*) AS c FROM clientes WHERE etapa_crm='$e'")->fetch_assoc()["c"];
}

echo json_encode([
    "total"     => (int)$total,
    "activos"   => (int)$activos,
    "inactivos" => (int)$inactivos,
    "etapas"    => $etapas,
    "riesgo"    => $riesgo
]);
