<?php
header('Content-Type: application/json');
require __DIR__ . "/config/conexion.php";

$out = ['status'=>'ok','products'=>[], 'triggers'=>[]];
try {
    $sql = "SELECT id, nombre, stock, IFNULL(stock_minimo,0) AS stock_minimo, IFNULL(proveedor_id,0) AS proveedor_id FROM productos";
    $res = $conn->query($sql);
    if ($res === false) throw new Exception('Query productos failed: '.$conn->error);

    while ($p = $res->fetch_assoc()) {
        $prod_id = (int)$p['id'];
        $pending = 0;
        $chk = $conn->prepare("SELECT COUNT(*) as cnt FROM pedidos_proveedor WHERE producto_id = ? AND estado = 'pendiente'");
        if ($chk) {
            $chk->bind_param('i', $prod_id);
            $chk->execute();
            $r = $chk->get_result()->fetch_assoc();
            $pending = isset($r['cnt']) ? (int)$r['cnt'] : 0;
            $chk->close();
        }

        $out['products'][] = [
            'id'=>$prod_id,
            'nombre'=>$p['nombre'],
            'stock'=>(int)$p['stock'],
            'stock_minimo'=>(int)$p['stock_minimo'],
            'proveedor_id'=>(int)$p['proveedor_id'],
            'pending_pedidos'=>$pending
        ];

        if ((int)$p['stock'] <= (int)$p['stock_minimo']) {
            $out['triggers'][] = $prod_id;
        }
    }

    $out['summary'] = [
        'total_products'=>count($out['products']),
        'would_trigger'=>count($out['triggers'])
    ];

    echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status'=>'error','msg'=>$e->getMessage()]);
    exit;
}

?>