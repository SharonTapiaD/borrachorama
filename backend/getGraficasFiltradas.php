<?php
session_start();
header("Content-Type: application/json");

// Importante: Verifica que la ruta a tu conexión sea correcta
require __DIR__ . "/config/conexion.php"; 

$busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';
$estado   = isset($_GET['estado'])   ? $_GET['estado']   : '';

$where = " WHERE 1=1";
if ($busqueda !== '') {
    $b = $conn->real_escape_string($busqueda);
    $where .= " AND (nombre LIKE '%$b%' OR correo LIKE '%$b%' OR empresa LIKE '%$b%')";
}
if ($estado !== '') {
    $e = $conn->real_escape_string($estado);
    $where .= " AND estado = '$e'";
}

// 1. Etapas
$etapas = ["Prospecto" => 0, "Activo" => 0, "Frecuente" => 0, "Inactivo" => 0];
$sqlEtapas = "SELECT etapa_crm, COUNT(*) as total FROM clientes $where GROUP BY etapa_crm";
$resEtapas = $conn->query($sqlEtapas);
if($resEtapas){
    while($row = $resEtapas->fetch_assoc()){
        if(isset($etapas[$row['etapa_crm']])) $etapas[$row['etapa_crm']] = (int)$row['total'];
    }
}

// 2. Estados
$estados = ["activo" => 0, "inactivo" => 0];
$sqlEstado = "SELECT estado, COUNT(*) as total FROM clientes $where GROUP BY estado";
$resEstado = $conn->query($sqlEstado);
if($resEstado){
    while($row = $resEstado->fetch_assoc()){
        if(isset($estados[$row['estado']])) $estados[$row['estado']] = (int)$row['total'];
    }
}

echo json_encode([
    "etapas" => $etapas,
    "estados" => $estados
]);