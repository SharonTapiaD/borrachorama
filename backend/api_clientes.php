<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'config/conexion.php';

$r = $conn->query("
    SELECT
        id,
        nombre,
        correo,
        telefono,
        empresa,
        DATE_FORMAT(fecha_registro, '%d/%m/%y') AS fecha_registro,
        estado,
        etapa_crm
    FROM clientes
    ORDER BY id ASC
");

$clientes = [];
while ($row = $r->fetch_assoc()) {
    $clientes[] = $row;
}

$conn->close();

echo json_encode($clientes, JSON_UNESCAPED_UNICODE);