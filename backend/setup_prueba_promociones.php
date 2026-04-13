<?php
require __DIR__ . "/config/conexion.php";
session_start();

echo "<h1>🔧 Configuración de Prueba para Promociones</h1>";

// Verificar si hay usuario logueado
if (!isset($_SESSION['usuario_id'])) {
    echo "<p style='color: red;'>❌ No hay usuario logueado</p>";
    echo "<p><a href='../loginERP.html'>Ir al login</a></p>";
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
echo "<p style='color: green;'>✅ Usuario logueado: ID $usuario_id</p>";

// Buscar un producto para activar promoción
$sql = "SELECT id, nombre, precio FROM productos LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    echo "<p style='color: red;'>❌ No hay productos en la base de datos</p>";
    exit;
}

$producto = $result->fetch_assoc();
echo "<p>📦 Producto encontrado: {$producto['nombre']} (ID: {$producto['id']})</p>";

// Activar promoción del 25% en este producto
$updateSql = "UPDATE productos SET
              descuento_porcentaje = 25,
              descuento_fijo = 0,
              promocion_activa = 1,
              fecha_inicio_promocion = NOW(),
              fecha_fin_promocion = DATE_ADD(NOW(), INTERVAL 7 DAY)
              WHERE id = ?";

$stmt = $conn->prepare($updateSql);
$stmt->bind_param("i", $producto['id']);

if ($stmt->execute()) {
    echo "<p style='color: green;'>✅ Promoción activada: 25% OFF en {$producto['nombre']}</p>";
} else {
    echo "<p style='color: red;'>❌ Error al activar promoción</p>";
}

// Agregar producto al carrito si no está
$checkCart = "SELECT id FROM carrito WHERE usuario_id = ? AND producto_id = ?";
$stmt = $conn->prepare($checkCart);
$stmt->bind_param("ii", $usuario_id, $producto['id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Agregar al carrito
    $insertSql = "INSERT INTO carrito (usuario_id, producto_id, cantidad) VALUES (?, ?, 1)";
    $stmt = $conn->prepare($insertSql);
    $stmt->bind_param("ii", $usuario_id, $producto['id']);

    if ($stmt->execute()) {
        echo "<p style='color: green;'>✅ Producto agregado al carrito</p>";
    } else {
        echo "<p style='color: red;'>❌ Error al agregar producto al carrito</p>";
    }
} else {
    echo "<p style='color: blue;'>ℹ️ El producto ya está en el carrito</p>";
}

echo "<hr>";
echo "<p><strong>Resumen:</strong></p>";
echo "<ul>";
echo "<li>Producto: {$producto['nombre']}</li>";
echo "<li>Precio original: \${$producto['precio']}</li>";
echo "<li>Precio con descuento: \$" . ($producto['precio'] * 0.75) . "</li>";
echo "<li>Descuento: 25%</li>";
echo "</ul>";

echo "<p><a href='../src/html/cart.html' target='_blank'>🛒 Ver carrito</a> | ";
echo "<a href='../simulador_carrito.html' target='_blank'>🧪 Ver simulador</a> | ";
echo "<a href='../test_promociones.html' target='_blank'>🧪 Ver pruebas</a></p>";
?>