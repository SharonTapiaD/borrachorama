<?php
session_start();
header("Content-Type: application/json");

require __DIR__ . "/config/conexion.php";

// IMPORTANTE: Tu login.php usa 'usuario_id', no 'user_id'
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode([]);
    exit;
}

$id_logueado = $_SESSION['usuario_id']; // Para María esto será 2

/**
 * Buscamos el ID en la tabla de CLIENTES (CRM).
 * María en la tabla 'usuarios' es ID 2, pero en 'clientes' es ID 5.
 * Usamos la columna 'usuario_id' de la tabla clientes para hacer el puente.
 */
$sql_cliente = "SELECT id FROM clientes WHERE usuario_id = $id_logueado LIMIT 1";
$res_cliente = $conn->query($sql_cliente);

if ($res_cliente && $res_cliente->num_rows > 0) {
    $cliente_data = $res_cliente->fetch_assoc();
    $real_cliente_id = $cliente_data['id']; // Aquí obtendrá el 5

    // Ahora buscamos las interacciones que tengan el cliente_id = 5
    $sql_inter = "SELECT tipo, descripcion, fecha 
                  FROM interacciones 
                  WHERE cliente_id = $real_cliente_id 
                  ORDER BY fecha DESC";
    
    $result = $conn->query($sql_inter);
    $interacciones = [];

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $interacciones[] = $row;
        }
    }
    echo json_encode($interacciones);
} else {
    // Si María no está en la tabla clientes, no hay interacciones que mostrar
    echo json_encode([]);
}
?>