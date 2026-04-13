<?php
require __DIR__ . "/config/conexion.php";

$sql = "SELECT id, nombre, precio, descuento_porcentaje, descuento_fijo, promocion_activa,
        fecha_inicio_promocion, fecha_fin_promocion
        FROM productos
        WHERE promocion_activa = 1";

$result = $conn->query($sql);

$productos = [];
while ($row = $result->fetch_assoc()) {
    $productos[] = $row;
}

echo "<h2>Productos con promociones activas:</h2>";
echo "<pre>";
print_r($productos);
echo "</pre>";

if (empty($productos)) {
    echo "<p style='color: red;'>No hay productos con promociones activas.</p>";
    echo "<p>Para probar, ejecuta esta consulta SQL:</p>";
    echo "<code>UPDATE productos SET descuento_porcentaje = 20, promocion_activa = 1 WHERE nombre LIKE '%Cerveza%' LIMIT 1;</code>";
}
?>