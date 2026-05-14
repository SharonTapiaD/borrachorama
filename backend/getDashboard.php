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
SELECT c.id as cliente_id, c.nombre, c.correo, c.fecha_registro
    FROM clientes c
    LEFT JOIN pedidos p ON c.id = p.cliente_id
    WHERE p.id IS NULL 
    ORDER BY c.fecha_registro DESC 
    LIMIT 10
";

$res = $conn->query($sql);

$riesgo = [];
while ($row = $res->fetch_assoc()) {
    $riesgo[] = [
        "cliente_id"       => (int)$row["cliente_id"],
        "nombre"           => $row["nombre"],
        "correo"           => $row["correo"],
        "pedido_id"        => null,
        "carrito_id"       => null,
        "total"            => 0,
        "ultima_interaccion"=> null
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
