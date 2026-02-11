<?php
session_start();
header("Content-Type: application/json");

require __DIR__ . "/config/conexion.php";

$sql = "
SELECT 
    id,
    nombre,
    correo,
    telefono,
    empresa,
    fecha_registro,
    estado,
    etapa_crm
FROM clientes
";

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
