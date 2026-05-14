<?php
session_start();
header("Content-Type: application/json");
include __DIR__ . "/config/conexion.php";

// Este script crea la tabla de facturas si no existe
$sql = "CREATE TABLE IF NOT EXISTS facturas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pedido_id INT NOT NULL,
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

if ($conn->query($sql) === TRUE) {
    echo json_encode([
        "status" => "ok",
        "msg" => "Tabla de facturas creada o ya existe"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "msg" => "Error al crear tabla: " . $conn->error
    ]);
}
?>
