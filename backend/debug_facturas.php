<?php
session_start();
header("Content-Type: application/json");
include __DIR__ . "/config/conexion.php";

// Verificar que el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode([
        "status" => "error",
        "msg" => "No autorizado"
    ]);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Primero crear la tabla si no existe
$sql_create = "CREATE TABLE IF NOT EXISTS facturas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pedido_id INT NOT NULL UNIQUE,
    numero_factura VARCHAR(50) NOT NULL UNIQUE,
    rfc_cliente VARCHAR(13) NOT NULL,
    razon_social VARCHAR(255) NOT NULL,
    domicilio_fiscal TEXT NOT NULL,
    monto_total DECIMAL(10, 2) NOT NULL,
    fecha_emision DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME ON UPDATE CURRENT_TIMESTAMP,
    estado VARCHAR(50) DEFAULT 'Emitida',
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    INDEX idx_pedido_id (pedido_id),
    INDEX idx_numero_factura (numero_factura)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$conn->query($sql_create);

// Obtener todos los pedidos del usuario con sus facturas
$sql = "SELECT p.id, p.total, p.fecha, p.estatus, 
        f.id as factura_id, f.numero_factura, f.rfc_cliente
        FROM pedidos p
        LEFT JOIN facturas f ON p.id = f.pedido_id
        WHERE p.usuario_id = ?
        ORDER BY p.fecha DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

$pedidos = [];
while ($row = $result->fetch_assoc()) {
    $pedidos[] = [
        "pedido_id" => $row['id'],
        "total" => $row['total'],
        "fecha" => $row['fecha'],
        "estatus" => $row['estatus'],
        "tiene_factura" => !is_null($row['factura_id']),
        "numero_factura" => $row['numero_factura'],
        "rfc" => $row['rfc_cliente']
    ];
}

echo json_encode([
    "status" => "ok",
    "pedidos" => $pedidos
]);
?>
