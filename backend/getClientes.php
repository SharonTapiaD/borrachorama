<?php
session_start();
header("Content-Type: application/json");

require __DIR__ . "/config/conexion.php";

// 1. Capturamos los filtros que vienen de la URL
$busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';
$estado   = isset($_GET['estado'])   ? $_GET['estado']   : '';
$etapa    = isset($_GET['etapa'])    ? $_GET['etapa']    : '';

// 2. Empezamos la consulta con un "WHERE 1=1" (esto no hace nada, pero nos deja añadir "AND" después)
$sql = "SELECT id, nombre, correo, telefono, empresa, fecha_registro, estado, etapa_crm FROM clientes WHERE 1=1";

// 3. Si mandaron búsqueda, la añadimos a la consulta
if ($busqueda !== '') {
    $busquedaEscapada = $conn->real_escape_string($busqueda);
    $sql .= " AND (nombre LIKE '%$busquedaEscapada%' OR correo LIKE '%$busquedaEscapada%' OR empresa LIKE '%$busquedaEscapada%')";
}

// 4. Si mandaron estado, lo añadimos
if ($estado !== '') {
    $estadoEscapado = $conn->real_escape_string($estado);
    $sql .= " AND estado = '$estadoEscapado'";
}

// 5. Si mandaron etapa CRM (para la tablita de la derecha), la añadimos
if ($etapa !== '') {
    $etapaEscapada = $conn->real_escape_string($etapa);
    $sql .= " AND etapa_crm = '$etapaEscapada'";
}

// 6. Ejecutamos la consulta ya filtrada
$result = $conn->query($sql);

$clientes = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $clientes[] = [
            "id"             => (int)$row["id"],
            "nombre"         => $row["nombre"],
            "correo"         => $row["correo"],
            "telefono"       => $row["telefono"],
            "empresa"        => $row["empresa"],
            "fecha_registro" => $row["fecha_registro"],
            "estado"         => $row["estado"],
            "etapa_crm"      => $row["etapa_crm"]
        ];
    }
    echo json_encode($clientes);
} else {
    echo json_encode([]);
}