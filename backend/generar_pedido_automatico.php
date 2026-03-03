<?php
session_start();
header("Content-Type: application/json");
require __DIR__ . "/config/conexion.php";


$prov_res = $conn->query("SELECT id FROM proveedores WHERE estatus = 'activo'");
$proveedores_ids = [];
while($row = $prov_res->fetch_assoc()) {
    $proveedores_ids[] = $row['id'];
}

if(empty($proveedores_ids)) {
    $proveedores_ids = [1];
}


$sql = "SELECT id as producto_id, stock_minimo, precio FROM productos WHERE stock <= stock_minimo";
$res = $conn->query($sql);
$pedidos_generados = 0;


$estados_posibles = ['pendiente', 'enviado', 'recibido', 'cancelado'];
$tipos_posibles = ['reposicion', 'venta'];

while ($prod = $res->fetch_assoc()) {
    
    
    $prov_id = $proveedores_ids[array_rand($proveedores_ids)];
    
   
    $minimo = $prod['stock_minimo'] > 0 ? $prod['stock_minimo'] : 10;
    $cantidad_pedir = $minimo * rand(2, 6); 
    
    $porcentaje_costo = rand(45, 85) / 100; 
    $precio_unitario_proveedor = $prod['precio'] * $porcentaje_costo;
    $total = $cantidad_pedir * $precio_unitario_proveedor; 
    
    
    $dias_atras = rand(0, 7); // Negros de mierda
    $fecha_pedido = date('Y-m-d', strtotime("-$dias_atras days"));
    
    $dias_entrega = rand(2, 10); 
    $fecha_entrega_estimada = date('Y-m-d', strtotime("$fecha_pedido +$dias_entrega days"));

  
    $estado_random = $estados_posibles[array_rand($estados_posibles)];
    $tipo_random = $tipos_posibles[array_rand($tipos_posibles)];

   
    $sql_insert = "INSERT INTO pedidos_proveedor (proveedor_id, producto_id, fecha_pedido, fecha_entrega_estimada, estado, tipo, metodo_generacion, total) VALUES (?, ?, ?, ?, ?, ?, 'automatico', ?)";
    $ins = $conn->prepare($sql_insert);
    
  
    $ins->bind_param("iissssd", $prov_id, $prod['producto_id'], $fecha_pedido, $fecha_entrega_estimada, $estado_random, $tipo_random, $total);
    
    if($ins->execute()){
        $pedido_id = $ins->insert_id;

        
        $ins_det = $conn->prepare("INSERT INTO detalle_pedido_proveedor (pedido_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
        $ins_det->bind_param("iiid", $pedido_id, $prod['producto_id'], $cantidad_pedir, $precio_unitario_proveedor);
        $ins_det->execute();
        
       
        $upd_stock = $conn->prepare("UPDATE productos SET stock = stock + ? WHERE id = ?");
        $upd_stock->bind_param("ii", $cantidad_pedir, $prod['producto_id']);
        $upd_stock->execute();
        
        $pedidos_generados++;
    }
}


if ($pedidos_generados > 0) {
    echo json_encode(["status" => "ok", "msg" => "Se generaron $pedidos_generados pedidos (stock sumado y datos variados)."]);
} else {
    echo json_encode(["status" => "error", "msg" => "No hay productos con stock bajo."]);
}
?>