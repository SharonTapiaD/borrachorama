<?php
require __DIR__ . "/config/conexion.php";

echo "<h1>🔧 Creando Tablas de Promociones</h1>";

// SQL para crear las tablas de promociones
$sql = "
-- Agregar campos de promociones a la tabla productos (si no existen)
ALTER TABLE productos
ADD COLUMN IF NOT EXISTS descuento_porcentaje DECIMAL(5,2) DEFAULT 0.00,
ADD COLUMN IF NOT EXISTS descuento_fijo DECIMAL(10,2) DEFAULT 0.00,
ADD COLUMN IF NOT EXISTS fecha_inicio_promocion DATETIME NULL,
ADD COLUMN IF NOT EXISTS fecha_fin_promocion DATETIME NULL,
ADD COLUMN IF NOT EXISTS tipo_promocion ENUM('producto', 'categoria', 'pago') DEFAULT 'producto',
ADD COLUMN IF NOT EXISTS promocion_activa TINYINT(1) DEFAULT 0;

-- Crear tabla para promociones de categorías (si no existe)
CREATE TABLE IF NOT EXISTS promociones_categorias (
    id INT PRIMARY KEY AUTO_INCREMENT,
    categoria_id INT,
    descuento_porcentaje DECIMAL(5,2) DEFAULT 0.00,
    descuento_fijo DECIMAL(10,2) DEFAULT 0.00,
    fecha_inicio DATETIME,
    fecha_fin DATETIME,
    activa TINYINT(1) DEFAULT 1,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id)
);

-- Crear tabla para promociones de pago (si no existe)
CREATE TABLE IF NOT EXISTS promociones_pago (
    id INT PRIMARY KEY AUTO_INCREMENT,
    metodo_pago VARCHAR(50),
    descuento_porcentaje DECIMAL(5,2) DEFAULT 0.00,
    descuento_fijo DECIMAL(10,2) DEFAULT 0.00,
    fecha_inicio DATETIME,
    fecha_fin DATETIME,
    activa TINYINT(1) DEFAULT 1,
    minimo_compra DECIMAL(10,2) DEFAULT 0.00
);
";

try {
    // Ejecutar el SQL
    if ($conn->multi_query($sql)) {
        echo "<p style='color: green;'>✅ Tablas de promociones creadas/actualizadas exitosamente</p>";

        // Limpiar resultados pendientes
        while ($conn->more_results() && $conn->next_result()) {
            // Limpiar resultados
        }
    } else {
        echo "<p style='color: red;'>❌ Error al crear las tablas: " . $conn->error . "</p>";
    }

    // Verificar que la tabla promociones_pago existe
    $checkTable = $conn->query("SHOW TABLES LIKE 'promociones_pago'");
    if ($checkTable->num_rows > 0) {
        echo "<p style='color: green;'>✅ Tabla 'promociones_pago' verificada</p>";
    } else {
        echo "<p style='color: red;'>❌ Tabla 'promociones_pago' no encontrada</p>";
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='../admin_promociones.html' target='_blank'>Ir al panel de promociones</a></p>";

$conn->close();
?>