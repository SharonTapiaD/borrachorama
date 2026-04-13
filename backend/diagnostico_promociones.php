<?php
require __DIR__ . "/config/conexion.php";

echo "<h1>Diagnóstico del Sistema de Promociones</h1>";

// Verificar conexión a BD
if ($conn->connect_error) {
    die("<p style='color: red;'>Error de conexión: " . $conn->connect_error . "</p>");
}
echo "<p style='color: green;'>✅ Conexión a BD exitosa</p>";

// Verificar si existen las columnas de promociones
$result = $conn->query("DESCRIBE productos");
$columnas = [];
while ($row = $result->fetch_assoc()) {
    $columnas[] = $row['Field'];
}

$columnasRequeridas = ['descuento_porcentaje', 'descuento_fijo', 'fecha_inicio_promocion', 'fecha_fin_promocion', 'promocion_activa'];
$columnasFaltantes = [];

foreach ($columnasRequeridas as $col) {
    if (!in_array($col, $columnas)) {
        $columnasFaltantes[] = $col;
    }
}

if (!empty($columnasFaltantes)) {
    echo "<p style='color: red;'>❌ Faltan columnas en tabla productos: " . implode(', ', $columnasFaltantes) . "</p>";
    echo "<p>Ejecuta el script SQL nuevamente.</p>";
} else {
    echo "<p style='color: green;'>✅ Todas las columnas de promociones existen</p>";
}

// Verificar productos con promociones
$result = $conn->query("SELECT COUNT(*) as total FROM productos WHERE promocion_activa = 1");
$row = $result->fetch_assoc();
echo "<p>📊 Productos con promociones activas: " . $row['total'] . "</p>";

// Mostrar algunos productos con promociones
$result = $conn->query("SELECT nombre, precio, descuento_porcentaje, descuento_fijo, promocion_activa FROM productos WHERE promocion_activa = 1 LIMIT 5");
if ($result->num_rows > 0) {
    echo "<h3>Productos con promociones:</h3><ul>";
    while ($row = $result->fetch_assoc()) {
        echo "<li>{$row['nombre']} - \${$row['precio']} - ";
        if ($row['descuento_porcentaje'] > 0) {
            echo "{$row['descuento_porcentaje']}% OFF";
        } elseif ($row['descuento_fijo'] > 0) {
            echo "\${$row['descuento_fijo']} OFF";
        }
        echo "</li>";
    }
    echo "</ul>";
}

// Verificar si hay productos en el carrito
session_start();
if (isset($_SESSION['usuario_id'])) {
    $usuario_id = $_SESSION['usuario_id'];
    $result = $conn->query("SELECT COUNT(*) as total FROM carrito WHERE usuario_id = $usuario_id");
    $row = $result->fetch_assoc();
    echo "<p>🛒 Productos en carrito: " . $row['total'] . "</p>";

    if ($row['total'] > 0) {
        echo "<h3>Productos en carrito:</h3><ul>";
        $result = $conn->query("SELECT p.nombre, c.cantidad, p.precio, p.promocion_activa FROM carrito c JOIN productos p ON c.producto_id = p.id WHERE c.usuario_id = $usuario_id");
        while ($row = $result->fetch_assoc()) {
            echo "<li>{$row['nombre']} (x{$row['cantidad']}) - \${$row['precio']} - Promoción: " . ($row['promocion_activa'] ? 'Sí' : 'No') . "</li>";
        }
        echo "</ul>";
    }
} else {
    echo "<p style='color: orange;'>⚠️ No hay sesión activa</p>";
}

echo "<hr><p><a href='../src/html/cart.html'>Ir al carrito</a> | <a href='../admin_promociones.html'>Ir a promociones</a></p>";
?>