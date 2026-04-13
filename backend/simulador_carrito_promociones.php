<?php
require __DIR__ . "/config/conexion.php";

echo "<h1>🛒 Simulador de Carrito con Promociones</h1>";

// Obtener productos con promociones activas
$sql = "SELECT id, nombre, precio, descuento_porcentaje, descuento_fijo, promocion_activa
        FROM productos
        WHERE promocion_activa = 1
        LIMIT 3";

$result = $conn->query($sql);

if ($result->num_rows === 0) {
    echo "<p style='color: red;'>❌ No hay productos con promociones activas</p>";
    echo "<p><a href='setup_prueba_promociones_simple.php'>Crear promociones de prueba</a></p>";
    exit;
}

$productos = $result->fetch_all(MYSQLI_ASSOC);

echo "<h3>Productos con promociones activas:</h3>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Producto</th><th>Precio Original</th><th>Descuento</th><th>Precio Final</th></tr>";

$totalOriginal = 0;
$totalDescuento = 0;
$totalFinal = 0;

foreach ($productos as $producto) {
    $precioOriginal = floatval($producto['precio']);
    $descuentoPorcentaje = floatval($producto['descuento_porcentaje']);
    $descuentoFijo = floatval($producto['descuento_fijo']);

    $descuentoAplicado = 0;
    $precioFinal = $precioOriginal;

    if ($descuentoPorcentaje > 0) {
        $descuentoAplicado = $precioOriginal * ($descuentoPorcentaje / 100);
        $precioFinal = $precioOriginal - $descuentoAplicado;
        $descuentoStr = "{$descuentoPorcentaje}% OFF";
    } elseif ($descuentoFijo > 0) {
        $descuentoAplicado = min($descuentoFijo, $precioOriginal);
        $precioFinal = $precioOriginal - $descuentoAplicado;
        $descuentoStr = "$ {$descuentoFijo} OFF";
    } else {
        $descuentoStr = "Sin descuento";
    }

    $totalOriginal += $precioOriginal;
    $totalDescuento += $descuentoAplicado;
    $totalFinal += $precioFinal;

    echo "<tr>";
    echo "<td>{$producto['nombre']}</td>";
    echo "<td>$" . number_format($precioOriginal, 2) . "</td>";
    echo "<td>{$descuentoStr}</td>";
    echo "<td>$" . number_format($precioFinal, 2) . "</td>";
    echo "</tr>";
}

echo "</table>";

echo "<hr>";
echo "<h3>Resumen del Carrito:</h3>";
echo "<p><strong>Subtotal Original:</strong> $" . number_format($totalOriginal, 2) . "</p>";
echo "<p><strong>Total Descuentos:</strong> $" . number_format($totalDescuento, 2) . "</p>";
echo "<p><strong>Subtotal con Descuentos:</strong> $" . number_format($totalFinal, 2) . "</p>";

$iva = $totalFinal * 0.16;
$envio = 80.00;
$totalFinalConImpuestos = $totalFinal + $iva + $envio;

echo "<p><strong>IVA (16%):</strong> $" . number_format($iva, 2) . "</p>";
echo "<p><strong>Envío:</strong> $" . number_format($envio, 2) . "</p>";
echo "<p><strong><span style='color: green;'>Total Final:</span></strong> $" . number_format($totalFinalConImpuestos, 2) . "</p>";

echo "<hr>";
echo "<p style='color: blue;'>💡 <strong>Si los descuentos no aparecen en el carrito real:</strong></p>";
echo "<ul>";
echo "<li>Asegúrate de estar logueado</li>";
echo "<li>Agrega productos al carrito desde el catálogo</li>";
echo "<li>Verifica que los productos tengan promociones activas</li>";
echo "</ul>";

echo "<p><a href='../src/html/cart.html' target='_blank'>Ir al carrito real</a></p>";

$conn->close();
?>