<?php
require __DIR__ . "/config/conexion.php";

// Buscar el producto "Cerveza Artesanal"
$sql = "SELECT id, nombre FROM productos WHERE nombre LIKE '%Cerveza%' LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $producto = $result->fetch_assoc();

    // Aplicar promoción del 20%
    $updateSql = "UPDATE productos SET
                  descuento_porcentaje = 20,
                  descuento_fijo = 0,
                  promocion_activa = 1,
                  fecha_inicio_promocion = NOW(),
                  fecha_fin_promocion = DATE_ADD(NOW(), INTERVAL 30 DAY)
                  WHERE id = ?";

    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("i", $producto['id']);

    if ($stmt->execute()) {
        echo "<h2 style='color: green;'>✅ Promoción activada exitosamente</h2>";
        echo "<p>Producto: " . $producto['nombre'] . "</p>";
        echo "<p>Descuento: 20%</p>";
        echo "<p>Vigencia: 30 días</p>";
        echo "<p><a href='../src/html/cart.html'>Ver en el carrito</a></p>";
    } else {
        echo "<h2 style='color: red;'>❌ Error al activar promoción</h2>";
    }
} else {
    echo "<h2 style='color: red;'>❌ No se encontró el producto 'Cerveza Artesanal'</h2>";
    echo "<p>Productos disponibles:</p>";
    $allProducts = $conn->query("SELECT nombre FROM productos LIMIT 10");
    while ($p = $allProducts->fetch_assoc()) {
        echo "- " . $p['nombre'] . "<br>";
    }
}
?>