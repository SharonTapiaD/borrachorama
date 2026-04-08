<?php
session_start();
header("Content-Type: application/json");
require __DIR__ . "/config/conexion.php";

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(["status" => "error", "msg" => "Sesión expirada"]);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// 1. OBTENEMOS LOS PRODUCTOS DEL CARRITO
$sql_carrito = "SELECT c.id AS carrito_id, c.producto_id, c.cantidad, p.precio 
                FROM carrito c 
                JOIN productos p ON c.producto_id = p.id 
                WHERE c.usuario_id = ?";
$stmt_cart = $conn->prepare($sql_carrito);
$stmt_cart->bind_param("i", $usuario_id);
$stmt_cart->execute();
$res_cart = $stmt_cart->get_result();

$productos_pedido = [];
$subtotal = 0; // <--- Empezamos el subtotal en 0
$primer_carrito_id = 0;

while ($row = $res_cart->fetch_assoc()) {
    if ($primer_carrito_id == 0) $primer_carrito_id = $row['carrito_id'];
    $productos_pedido[] = $row;
    
    // SUMAMOS AL SUBTOTAL (Precio * Cantidad)
    $subtotal += ($row['precio'] * $row['cantidad']);
}

if (empty($productos_pedido)) {
    echo json_encode(["status" => "error", "msg" => "El carrito está vacío"]);
    exit;
}

// ==========================================================
// 2. AQUÍ VAN LOS CÁLCULOS (IVA Y ENVÍO)
// ==========================================================
$iva = $subtotal * 0.16;           // Calculamos el 16% de IVA
$envio = 80.00;                    // Costo de envío fijo
$total_final = $subtotal + $iva + $envio; // Suma de todo
// ==========================================================

$conn->begin_transaction();

try {
    // 3. INSERTAR EN 'pedidos' (Usamos el $total_final que calculamos arriba)
    $sql_pedido = "INSERT INTO pedidos (usuario_id, total, estatus, fecha, cliente_id, carrito_id) 
                   VALUES (?, ?, 'pendiente', NOW(), ?, ?)";
    $stmt_p = $conn->prepare($sql_pedido);
    
    // El 'd' es para Double (decimales), pasamos $total_final
    $stmt_p->bind_param("idii", $usuario_id, $total_final, $usuario_id, $primer_carrito_id);
    $stmt_p->execute();
    
    $pedido_id = $conn->insert_id;

    // 4. INSERTAR DETALLES
    $sql_detalle = "INSERT INTO detalle_pedido (pedido_id, producto_id, cantidad, precio_unitario, carrito_id, cliente_id) 
                    VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_d = $conn->prepare($sql_detalle);

    foreach ($productos_pedido as $prod) {
        $stmt_d->bind_param("iiidii", 
            $pedido_id, 
            $prod['producto_id'], 
            $prod['cantidad'], 
            $prod['precio'],
            $prod['carrito_id'],
            $usuario_id
        );
        $stmt_d->execute();
    }

    // 5. TRUCO PARA VACIAR EL CARRITO
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");
    $sql_delete = "DELETE FROM carrito WHERE usuario_id = ?";
    $stmt_del = $conn->prepare($sql_delete);
    $stmt_del->bind_param("i", $usuario_id);
    $stmt_del->execute();
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");

    $conn->commit();
    echo json_encode(["status" => "ok", "msg" => "¡Compra exitosa! Total con IVA y Envío: $" . number_format($total_final, 2)]);

} catch (Exception $e) {
    $conn->rollback();
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");
    echo json_encode(["status" => "error", "msg" => "Error: " . $e->getMessage()]);
}