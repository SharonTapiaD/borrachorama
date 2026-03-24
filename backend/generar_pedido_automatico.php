<?php
session_start();
header("Content-Type: application/json");
require __DIR__ . "/config/conexion.php";

$prov_res = $conn->query("SELECT id, nombre, telefono FROM proveedores WHERE estatus = 'activo'");
$proveedores = [];
while($row = $prov_res->fetch_assoc()) { $proveedores[] = $row; }

if(empty($proveedores)) {
    echo json_encode(["status" => "error", "msg" => "No hay proveedores activos."]);
    exit;
}

$sql = "SELECT id as producto_id, nombre, stock_minimo, precio FROM productos WHERE stock <= stock_minimo";
$res = $conn->query($sql);
$pedidos_generados = 0;
$enlaces_wa = []; 

$estados_posibles = ['pendiente', 'enviado', 'recibido', 'cancelado'];
$tipos_posibles = ['reposicion', 'venta'];

while ($prod = $res->fetch_assoc()) {
    $prov_random = $proveedores[array_rand($proveedores)];
    $prov_id = $prov_random['id'];
    $prov_nombre = $prov_random['nombre'];
    
    $minimo = $prod['stock_minimo'] > 0 ? $prod['stock_minimo'] : 10;
    $cantidad_pedir = $minimo * rand(2, 6);
    $precio_uni = $prod['precio'] * (rand(45, 85) / 100);
    $total = $cantidad_pedir * $precio_uni; 
    
    $fecha_pedido = date('Y-m-d', strtotime("-".rand(0, 7)." days"));
    $fecha_entrega_estimada = date('Y-m-d', strtotime("$fecha_pedido +".rand(2, 10)." days"));

    $estado_random = $estados_posibles[array_rand($estados_posibles)];
    $tipo_random = $tipos_posibles[array_rand($tipos_posibles)];

    $ins = $conn->prepare("INSERT INTO pedidos_proveedor (proveedor_id, producto_id, fecha_pedido, fecha_entrega_estimada, estado, tipo, metodo_generacion, total) VALUES (?, ?, ?, ?, ?, ?, 'automatico', ?)");
    $ins->bind_param("iissssd", $prov_id, $prod['producto_id'], $fecha_pedido, $fecha_entrega_estimada, $estado_random, $tipo_random, $total);
    
    if($ins->execute()){
        $pedido_id = $ins->insert_id;

        $ins_det = $conn->prepare("INSERT INTO detalle_pedido_proveedor (pedido_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
        $ins_det->bind_param("iiid", $pedido_id, $prod['producto_id'], $cantidad_pedir, $precio_uni);
        $ins_det->execute();
        
        $upd_stock = $conn->prepare("UPDATE productos SET stock = stock + ? WHERE id = ?");
        $upd_stock->bind_param("ii", $cantidad_pedir, $prod['producto_id']);
        $upd_stock->execute();
        
        
        if(!empty($prov_random['telefono'])) {
            $tel_limpio = preg_replace('/[^0-9]/', '', $prov_random['telefono']);
            $msj_wa = "Borrachorama \n\nHola {$prov_nombre}, el sistema detectó stock bajo y generó esta orden:\n\nProducto: *{$prod['nombre']}*\nCantidad: {$cantidad_pedir} unidades\nTotal estimado: $" . number_format($total, 2) . "\n\nConfirmar disponibilidad.";
            $enlaces_wa[] = [
                "nombre_proveedor" => $prov_nombre,
                "url" => "https://wa.me/52" . $tel_limpio . "?text=" . urlencode($msj_wa)
            ];
        }
        
        $pedidos_generados++;
    }
}

if ($pedidos_generados > 0) {
    echo json_encode(["status" => "ok", "msg" => "Se generaron $pedidos_generados pedidos.", "whatsapp_links" => $enlaces_wa]);
} else {
    echo json_encode(["status" => "error", "msg" => "No hay productos con stock bajo."]);
}
?>