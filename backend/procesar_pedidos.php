<?php
require __DIR__ . "/config/conexion.php";

function enviarCorreoProveedor($pedido_id, $conn) {
    // Simulación de envío de correo (PHPMailer se usaría aquí)
    $sql = "SELECT p.nombre as proveedor, p.correo, pr.nombre as producto, pp.total 
            FROM pedidos_proveedor pp 
            JOIN proveedores p ON pp.proveedor_id = p.id 
            JOIN productos pr ON pp.producto_id = pr.id 
            WHERE pp.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $pedido_id);
    $stmt->execute();
    $datos = $stmt->get_result()->fetch_assoc();
    
    // Log del envío
    $log = $conn->prepare("INSERT INTO logs_correos_proveedores (pedido_proveedor_id, destinatario, estatus) VALUES (?, ?, 'Enviado')");
    $log->bind_param("is", $pedido_id, $datos['correo']);
    $log->execute();
    
    return true;
}

if (isset($_GET['check_auto'])) {
    try {
        $sql = "SELECT id, nombre, stock, stock_minimo, precio, proveedor_id FROM productos WHERE stock <= stock_minimo";
        $res = $conn->query($sql);
        if ($res === false) throw new Exception('Query productos error: ' . $conn->error);

        $pedidos_generados = 0;

        while ($prod = $res->fetch_assoc()) {
            $prod_id = intval($prod['id']);
            $stock_min = isset($prod['stock_minimo']) ? intval($prod['stock_minimo']) : 0;

         
            $checkSql = "SELECT id FROM pedidos_proveedor WHERE producto_id = ? AND estado = 'pendiente'";
            $chk = $conn->prepare($checkSql);
            if (!$chk) throw new Exception('Prepare check pedido error: ' . $conn->error);
            $chk->bind_param('i', $prod_id);
            $chk->execute();
            $chkRes = $chk->get_result();
            if ($chkRes && $chkRes->num_rows == 0) {
                $cantidad_a_pedir = max(1, intval($stock_min * 2));
                $precio = isset($prod['precio']) ? floatval($prod['precio']) : 0.0;
                $total = $cantidad_a_pedir * ($precio * 0.7); 

                $prov_id = isset($prod['proveedor_id']) && intval($prod['proveedor_id'])>0 ? intval($prod['proveedor_id']) : 1;

                $insSql = "INSERT INTO pedidos_proveedor (proveedor_id, producto_id, fecha_pedido, estado, tipo, metodo_generacion, total) VALUES (?, ?, CURDATE(), 'pendiente', 'reposicion', 'automatico', ?)";
                $ins = $conn->prepare($insSql);
                if (!$ins) throw new Exception('Prepare insert pedido error: ' . $conn->error);

                $ins->bind_param("iid", $prov_id, $prod_id, $total);
                if ($ins->execute()) {
                    $newId = $ins->insert_id;
                    enviarCorreoProveedor($newId, $conn);
                    $pedidos_generados++;
                } else {
                    
                    error_log('Insert pedido failed: ' . $ins->error);
                }
            }
            $chk && $chk->close();
        }

        echo json_encode(["status" => "ok", "auto_generados" => $pedidos_generados]);
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["status"=>"error","msg"=>$e->getMessage()]);
        exit;
    }
}
?>