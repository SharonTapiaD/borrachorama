<?php
session_start();
header("Content-Type: application/json");
require __DIR__ . "/config/conexion.php";

// Capturamos los datos, ya sea que JS los envíe como JSON (fetch) o como FormData
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);
if (!is_array($input)) $input = []; // evitar warnings al acceder

$proveedor_id    = isset($input['proveedor_id']) ? $input['proveedor_id'] : ($_POST['proveedor_id'] ?? 0);
$producto_id     = isset($input['producto_id']) ? $input['producto_id'] : ($_POST['producto_id'] ?? 0);
$cantidad        = isset($input['cantidad']) ? $input['cantidad'] : ($_POST['cantidad'] ?? 0); // La cantidad a pedir
$fecha_pedido    = isset($input['fecha_pedido']) ? $input['fecha_pedido'] : ($_POST['fecha_pedido'] ?? date('Y-m-d'));
$fecha_entrega   = isset($input['fecha_entrega_estimada']) ? $input['fecha_entrega_estimada'] : ($_POST['fecha_entrega_estimada'] ?? null);
$estado          = isset($input['estado']) ? $input['estado'] : ($_POST['estado'] ?? 'pendiente');
$tipo            = isset($input['tipo']) ? $input['tipo'] : ($_POST['tipo'] ?? 'reposicion');
$total           = isset($input['total']) ? $input['total'] : ($_POST['total'] ?? 0);

$proveedor_id = intval($proveedor_id);
$producto_id  = intval($producto_id);
$cantidad     = intval($cantidad);
$total        = floatval($total);

if ($proveedor_id === 0 || $producto_id === 0) {
    echo json_encode(["status"=>"error","msg"=>"Proveedor y producto son obligatorios"]);
    exit;
}

// Iniciamos transacción para que, si algo falla, no guarde datos a medias
$conn->begin_transaction();

try {
    // 1. Insertar el pedido general al proveedor
    $sql = "INSERT INTO pedidos_proveedor (proveedor_id, producto_id, fecha_pedido, fecha_entrega_estimada, estado, tipo, metodo_generacion, total)
            VALUES (?, ?, ?, ?, ?, ?, 'manual', ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception("Error preparando insert pedido: " . $conn->error);
    $stmt->bind_param("iissssd", $proveedor_id, $producto_id, $fecha_pedido, $fecha_entrega, $estado, $tipo, $total);
    
    if (!$stmt->execute()) {
        throw new Exception("Error insertando pedido: " . $stmt->error);
    }
    $pedido_id = $conn->insert_id;

    // 2. Insertar el detalle del pedido (necesario para mantener la relación de la cantidad comprada)
    $precio_uni = $cantidad > 0 ? ($total / $cantidad) : 0;
    $sql_det = "INSERT INTO detalle_pedido_proveedor (pedido_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)";
        $stmt_det = $conn->prepare($sql_det);
        if (!$stmt_det) throw new Exception("Error preparando detalle pedido: " . $conn->error);
        $stmt_det->bind_param("iiid", $pedido_id, $producto_id, $cantidad, $precio_uni);
        if (!$stmt_det->execute()) {
            throw new Exception("Error insertando detalle: " . $stmt_det->error);
        }

    // 3. AUMENTAR EL STOCK (Rellenar inventario)
    if ($cantidad > 0) {
           $upd_stock = $conn->prepare("UPDATE productos SET stock = stock + ? WHERE id = ?");
           if (!$upd_stock) throw new Exception("Error preparando update stock: " . $conn->error);
           $upd_stock->bind_param("ii", $cantidad, $producto_id);
           if (!$upd_stock->execute()) {
               throw new Exception("Error actualizando stock: " . $upd_stock->error);
           }
    }

    $conn->commit();

    // 4. (Opcional) Configurar el mensaje de WhatsApp al proveedor
    $q_prov = $conn->query("SELECT nombre, correo, telefono FROM proveedores WHERE id = $proveedor_id");
    $d_prov = $q_prov->fetch_assoc();
    $q_prod = $conn->query("SELECT nombre FROM productos WHERE id = $producto_id");
    $d_prod = $q_prod->fetch_assoc();

    $wa_url = "";
    if (!empty($d_prov['telefono'])) {
        $tel_limpio = preg_replace('/[^0-9]/', '', $d_prov['telefono']);
        $msj_wa = "Borrachorama - Nuevo Pedido Manual*\n\nHola {$d_prov['nombre']}, te solicitamos {$cantidad} unidades de {$d_prod['nombre']} por un total de $" . number_format($total, 2) . " MXN.\n\nFecha de entrega esperada: $fecha_entrega.\n\nPor favor, confirma de recibido.";
        $wa_url = "https://wa.me/52" . $tel_limpio . "?text=" . urlencode($msj_wa);
    }

    echo json_encode(["status"=>"ok", "msg"=>"Pedido registrado y stock aumentado correctamente.", "whatsapp_url" => $wa_url]);

} catch (Exception $e) {
    $conn->rollback(); // Revertir si hubo error
    echo json_encode(["status"=>"error","msg"=>$e->getMessage()]);
}