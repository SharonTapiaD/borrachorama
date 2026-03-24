<?php
session_start();
header("Content-Type: application/json");

require __DIR__ . "/config/conexion.php";

$sql = "
SELECT 
    id,
    nombre,
    contacto,
    telefono,
    correo,
    direccion,
    estatus
FROM proveedores
ORDER BY nombre
";

$result = $conn->query($sql);

$rows = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $rows[] = [
            "id"        => (int)$row["id"],
            "nombre"    => $row["nombre"],
            "contacto"  => $row["contacto"],
            "telefono"  => $row["telefono"],
            "correo"    => $row["correo"],
            "direccion" => $row["direccion"],
            "estatus"   => $row["estatus"]
        ];
    }
}

echo json_encode($rows);
