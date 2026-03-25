<?php
session_start();
header("Content-Type: application/json");
require __DIR__ . "/config/conexion.php";

$proveedor_id    = isset($_POST['proveedor_id']) ? intval($_POST['proveedor_id']) : 0;
$producto_id     = isset($_POST['producto_id']) ? intval($_POST['producto_id']) : 0;
$fecha_pedido    = $_POST['fecha_pedido'] ?? date('Y-m-d');
$fecha_entrega   = $_POST['fecha_entrega_estimada'] ?? null;
$estado          = $_POST['estado'] ?? 'pendiente';
$tipo            = $_POST['tipo'] ?? 'reposicion';
$total           = isset($_POST['total']) ? floatval($_POST['total']) : 0;

if ($proveedor_id === 0 || $producto_id === 0) {
    echo json_encode(["status"=>"error","msg"=>"Proveedor y producto son obligatorios"]);
    exit;
}

$sql = "INSERT INTO pedidos_proveedor (proveedor_id, producto_id, fecha_pedido, fecha_entrega_estimada, estado, tipo, metodo_generacion, total)
        VALUES (?, ?, ?, ?, ?, ?, 'manual', ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iissssd", $proveedor_id, $producto_id, $fecha_pedido, $fecha_entrega, $estado, $tipo, $total);

if ($stmt->execute()) {
    
   
    $q_prov = $conn->query("SELECT nombre, correo, telefono FROM proveedores WHERE id = $proveedor_id");
    $d_prov = $q_prov->fetch_assoc();
    $q_prod = $conn->query("SELECT nombre FROM productos WHERE id = $producto_id");
    $d_prod = $q_prod->fetch_assoc();

    
    $wa_url = "";
    if(!empty($d_prov['telefono'])) {
        $tel_limpio = preg_replace('/[^0-9]/', '', $d_prov['telefono']);
        $msj_wa = "Borrachorama - Nuevo Pedido Manual*\n\nHola {$d_prov['nombre']}, te solicitamos {$d_prod['nombre']} por un total de $" . number_format($total, 2) . " MXN.\n\nFecha de entrega esperada: $fecha_entrega.\n\nPor favor, confirma de recibido.";
        $wa_url = "https://wa.me/52" . $tel_limpio . "?text=" . urlencode($msj_wa);
    }

    echo json_encode(["status"=>"ok", "msg"=>"Pedido guardado exitosamente.", "whatsapp_url" => $wa_url]);
} else {
    echo json_encode(["status"=>"error","msg"=>"Error al crear pedido"]);
}