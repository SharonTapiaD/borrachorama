<?php
session_start();
header("Content-Type: application/json");
require __DIR__ . "/config/conexion.php";

$filtro = $_GET['filtro'] ?? 'todos';


$sql = "SELECT p.id, p.nombre, p.stock, 
        COALESCE((SELECT SUM(cantidad) FROM carrito WHERE producto_id = p.id), 0) as total_vendido
        FROM productos p";


if ($filtro === 'mas_vendidos') {
   
    $sql .= " ORDER BY total_vendido DESC LIMIT 5";

} elseif ($filtro === 'menos_vendidos') {
    
    $sql .= " ORDER BY total_vendido ASC LIMIT 5";

} elseif ($filtro === 'baja_rotacion') {
    
    $sql .= " HAVING total_vendido < 3 AND p.stock > 0 ORDER BY total_vendido ASC";
}

$result = $conn->query($sql);
$data = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

echo json_encode($data);
?>