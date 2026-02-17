<?php
session_start();
header("Content-Type: application/json");

require __DIR__ . "/config/conexion.php";

$cliente_id = isset($_GET["cliente_id"]) ? (int)$_GET["cliente_id"] : 0;

$sql = "
SELECT 
    id,
    cliente_id,
    usuario_id,
    tipo,
    descripcion,
    fecha
FROM interacciones
WHERE cliente_id = $cliente_id
ORDER BY fecha DESC
";

$result = $conn->query($sql);

$interacciones = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $interacciones[] = [
            "id"          => (int)$row["id"],
            "cliente_id"  => (int)$row["cliente_id"],
            "usuario_id"  => (int)$row["usuario_id"],
            "tipo"        => $row["tipo"],
            "descripcion" => $row["descripcion"],
            "fecha"       => $row["fecha"]
        ];
    }
    echo json_encode($interacciones);
} else {
    echo json_encode([]);
}
