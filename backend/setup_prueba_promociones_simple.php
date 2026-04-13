<?php
require __DIR__ . "/config/conexion.php";

echo "<h1>🔧 Configuración de Prueba para Promociones (Sin Sesión)</h1>";

// Buscar productos para activar promociones
$sql = "SELECT id, nombre, precio FROM productos LIMIT 3";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    echo "<p style='color: red;'>❌ No hay productos en la base de datos</p>";
    exit;
}

$productos = $result->fetch_all(MYSQLI_ASSOC);
echo "<p>📦 Productos encontrados: " . count($productos) . "</p>";

// Activar promociones en los productos
$promociones = [
    ['porcentaje' => 25, 'fijo' => 0],
    ['porcentaje' => 0, 'fijo' => 50],
    ['porcentaje' => 15, 'fijo' => 0]
];

foreach ($productos as $index => $producto) {
    $promo = $promociones[$index] ?? $promociones[0];

    $updateSql = "UPDATE productos SET
                  descuento_porcentaje = ?,
                  descuento_fijo = ?,
                  promocion_activa = 1,
                  fecha_inicio_promocion = NOW(),
                  fecha_fin_promocion = DATE_ADD(NOW(), INTERVAL 7 DAY)
                  WHERE id = ?";

    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("ddi", $promo['porcentaje'], $promo['fijo'], $producto['id']);

    if ($stmt->execute()) {
        $tipo = $promo['porcentaje'] > 0 ? "{$promo['porcentaje']}% OFF" : "$ {$promo['fijo']} OFF";
        echo "<p style='color: green;'>✅ Promoción activada: $tipo en {$producto['nombre']}</p>";
    } else {
        echo "<p style='color: red;'>❌ Error al activar promoción en {$producto['nombre']}</p>";
    }
}

echo "<hr>";
echo "<p style='color: blue;'>🎯 <strong>Prueba completada!</strong></p>";
echo "<p>Ahora puedes:</p>";
echo "<ul>";
echo "<li><a href='../src/html/cart.html' target='_blank'>Ver el carrito</a> (debes estar logueado)</li>";
echo "<li><a href='../backend/diagnostico_promociones.php' target='_blank'>Ver diagnóstico</a></li>";
echo "<li><a href='../src/html/test_promociones.html' target='_blank'>Probar APIs</a></li>";
echo "</ul>";

$conn->close();
?>