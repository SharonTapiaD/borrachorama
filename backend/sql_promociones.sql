-- Agregar campos de promociones a la tabla productos
ALTER TABLE productos
ADD COLUMN descuento_porcentaje DECIMAL(5,2) DEFAULT 0.00,
ADD COLUMN descuento_fijo DECIMAL(10,2) DEFAULT 0.00,
ADD COLUMN fecha_inicio_promocion DATETIME NULL,
ADD COLUMN fecha_fin_promocion DATETIME NULL,
ADD COLUMN tipo_promocion ENUM('producto', 'categoria', 'pago') DEFAULT 'producto',
ADD COLUMN promocion_activa TINYINT(1) DEFAULT 0;

-- Crear tabla para promociones de categorías
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

-- Crear tabla para promociones de pago
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