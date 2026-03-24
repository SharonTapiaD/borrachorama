<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'config/conexion.php';

$metodo = $_SERVER['REQUEST_METHOD'];

// ── GET: traer clientes y productos para los dropdowns
if ($metodo === 'GET') {
    $clientes = [];
    $r = $conn->query("SELECT id, nombre FROM clientes WHERE estado = 'activo' ORDER BY nombre");
    while ($row = $r->fetch_assoc()) $clientes[] = $row;

    $productos = [];
    $r = $conn->query("SELECT id, nombre, precio, stock FROM productos WHERE estatus = 'activo' ORDER BY nombre");
    while ($row = $r->fetch_assoc()) $productos[] = $row;

    echo json_encode(['clientes' => $clientes, 'productos' => $productos], JSON_UNESCAPED_UNICODE);
    exit;
}

// ── POST: insertar nueva orden
if ($metodo === 'POST') {
    $body       = json_decode(file_get_contents('php://input'), true);
    $cliente_id = (int) $body['cliente_id'];
    $producto_id= (int) $body['producto_id'];
    $cantidad   = (int) $body['cantidad'];
    $total      = (float) $body['total'];

    if (!$cliente_id || !$producto_id || !$cantidad || !$total) {
        echo json_encode(['error' => 'Faltan datos']); exit;
    }

    // Verificar stock suficiente
    $r = $conn->query("SELECT stock FROM productos WHERE id = $producto_id");
    $prod = $r->fetch_assoc();
    if ($prod['stock'] < $cantidad) {
        echo json_encode(['error' => 'Stock insuficiente']); exit;
    }

    // Insertar carrito
    $conn->query("INSERT INTO carrito (usuario_id, producto_id, cantidad) VALUES (1, $producto_id, $cantidad)");
    $carrito_id = $conn->insert_id;

    // Insertar pedido
    $conn->query("INSERT INTO pedidos (usuario_id, total, estatus, cliente_id, carrito_id)
                  VALUES (1, $total, 'pendiente', $cliente_id, $carrito_id)");
    $pedido_id = $conn->insert_id;

    // Insertar detalle
    $conn->query("INSERT INTO detalle_pedido (pedido_id, producto_id, cantidad, carrito_id, cliente_id)
                  VALUES ($pedido_id, $producto_id, $cantidad, $carrito_id, $cliente_id)");

    // Descontar stock
    $conn->query("UPDATE productos SET stock = stock - $cantidad WHERE id = $producto_id");

    $conn->close();
    echo json_encode(['success' => true, 'pedido_id' => $pedido_id]);
    exit;
}