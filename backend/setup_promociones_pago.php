<?php
require __DIR__ . "/config/conexion.php";

echo "<h1>🎯 Prueba de Promociones de Pago</h1>";

// Insertar algunas promociones de prueba
$promocionesPrueba = [
    [
        'metodo_pago' => 'tarjeta',
        'descuento_porcentaje' => 10.00,
        'descuento_fijo' => 0.00,
        'minimo_compra' => 500.00,
        'activa' => 1
    ],
    [
        'metodo_pago' => 'efectivo',
        'descuento_porcentaje' => 0.00,
        'descuento_fijo' => 50.00,
        'minimo_compra' => 300.00,
        'activa' => 1
    ],
    [
        'metodo_pago' => 'transferencia',
        'descuento_porcentaje' => 5.00,
        'descuento_fijo' => 0.00,
        'minimo_compra' => 1000.00,
        'activa' => 1
    ]
];

$insertadas = 0;
foreach ($promocionesPrueba as $promo) {
    $sql = "INSERT INTO promociones_pago (metodo_pago, descuento_porcentaje, descuento_fijo, fecha_inicio, fecha_fin, activa, minimo_compra)
            VALUES (?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sddd", $promo['metodo_pago'], $promo['descuento_porcentaje'], $promo['descuento_fijo'], $promo['activa'], $promo['minimo_compra']);

    if ($stmt->execute()) {
        $insertadas++;
        $metodoTraducido = [
            'tarjeta' => 'Tarjeta de Crédito',
            'efectivo' => 'Efectivo',
            'transferencia' => 'Transferencia'
        ][$promo['metodo_pago']] ?? $promo['metodo_pago'];

        $descuento = $promo['descuento_porcentaje'] > 0 ?
            "{$promo['descuento_porcentaje']}% OFF" :
            "$ {$promo['descuento_fijo']} OFF";

        echo "<p style='color: green;'>✅ Promoción creada: $metodoTraducido - $descuento (mínimo: $${$promo['minimo_compra']})</p>";
    }
}

if ($insertadas > 0) {
    echo "<p style='color: blue;'>🎉 Se insertaron $insertadas promociones de prueba</p>";
} else {
    echo "<p style='color: orange;'>⚠️ No se insertaron nuevas promociones (posiblemente ya existen)</p>";
}

echo "<hr>";
echo "<h3>Promociones de Pago Actuales:</h3>";

// Mostrar promociones existentes
$sql = "SELECT * FROM promociones_pago ORDER BY id DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Método</th><th>Descuento</th><th>Mínimo</th><th>Estado</th></tr>";

    while ($row = $result->fetch_assoc()) {
        $metodoTraducido = [
            'tarjeta' => 'Tarjeta',
            'efectivo' => 'Efectivo',
            'transferencia' => 'Transferencia',
            'cheque' => 'Cheque',
            'monedero' => 'Monedero'
        ][$row['metodo_pago']] ?? $row['metodo_pago'];

        $descuento = $row['descuento_porcentaje'] > 0 ?
            "{$row['descuento_porcentaje']}%" :
            "$ {$row['descuento_fijo']}";

        $estado = $row['activa'] ? 'Activa' : 'Inactiva';

        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>$metodoTraducido</td>";
        echo "<td>$descuento OFF</td>";
        echo "<td>$ {$row['minimo_compra']}</td>";
        echo "<td>$estado</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No hay promociones de pago configuradas.</p>";
}

echo "<hr>";
echo "<p><a href='../admin_promociones.html#pago' target='_blank'>Ir al panel de promociones de pago</a></p>";
echo "<p><a href='../backend/get_promociones_pago.php' target='_blank'>Ver API de promociones de pago</a></p>";

$conn->close();
?>